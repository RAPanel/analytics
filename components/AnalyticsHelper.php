<?php
YiiBase::setPathOfAlias('analytics', dirname(dirname(__FILE__)));

/**
 * Class AnalyticsHelper
 * Хелпер для построения выборок и обработки данных
 */
class AnalyticsHelper
{

	const TYPE_INTERNAL_URL = 1;
	const TYPE_EXTERNAL_URL = 2;
	const TYPE_ACTION_NAME = 3;

	/**
	 * @return CDbConnection
	 */
	public static function getDb()
	{
		return Yii::app()->db;
	}

	/**
	 * @param string|null $query
	 * @return CDbCommand
	 */
	public static function createCommand($query = null)
	{
		return self::getDb()->createCommand($query);
	}

	public static function incrementLog($data)
	{
		// Проверяем, что кто-то посетил
		if (empty($data['session']))
			return false;
		if (!$sessionIdEncoded = self::getSessionCode($data['session']))
			return false;

		// Проверяем, пишем ли это в статистику
		if (!$actionId = self::getActionId($data['url'])) return false;

		// Ищем живой визит с таймоутом 30 минут
		$visitId = self::createCommand()->from('log_visit')
			->select('id')
			->where("BINARY visitor_id = :session", array('session' => $sessionIdEncoded))
			->andWhere('lastmod > FROM_UNIXTIME(:time)', array('time' => $data['created'] - 60 * 30))
			->order('lastmod DESC')
			->queryScalar();

		// Обновляем инфу визита или создаем новый
		if ($visitId) {
			self::createCommand()->update('log_visit', array(
				'lastmod' => new CDbExpression('FROM_UNIXTIME(:time)', array('time' => $data['created'])),
				'total_time' => new CDbExpression(':time - UNIX_TIMESTAMP(`created`)', array('time' => $data['created'])),
				'total_actions' => new CDbExpression('`total_actions` + 1'),
			), 'id=:visitId', compact('visitId'));
		} else {
			$userAgentData = self::parseUserAgentData($data['userAgent']);
			if (self::isBot($userAgentData))
				return false;

			self::getDb()->autoCommit = true;
			self::createCommand()->insert('log_visit', CMap::mergeArray($userAgentData, array(
					'visitor_id' => $sessionIdEncoded,
					'lastmod' => new CDbExpression('FROM_UNIXTIME(:time)', array('time' => $data['created'])),
					'created' => new CDbExpression('FROM_UNIXTIME(:time)', array('time' => $data['created'])),
					'location_ip' => self::p2n($data['ip']),
					'total_time' => 0,
					'total_actions' => 1,
					'action_id_ref' => self::getActionId($data['referrer'], 2),
				))
			);

			$visitId = self::getDb()->getLastInsertID();
			self::getDb()->autoCommit = false;
		}
		self::getDb()->createCommand()->insert('log_hit', array(
			'visitor_id' => $sessionIdEncoded,
			'visit_id' => $visitId,
			'action_id_name' => self::getActionId($data['name'], self::TYPE_ACTION_NAME),
			'action_id_url' => $actionId,
			'action_id_event' => 0,
			'time_cpu' => $data['time_cpu'],
			'time_exec' => $data['time_exec'],
			'ram' => $data['ram'],
			'created' => new CDbExpression('FROM_UNIXTIME(:time)', array('time' => $data['created'])),
		));
		return true;
	}

	/**
	 * @param string $userAgent
	 * @return array
	 */
	public static function parseUserAgentData($userAgent)
	{
		if (!YiiBase::getPathOfAlias('DeviceDetector')) {
			YiiBase::import('analytics.vendor.Spyc');
			YiiBase::setPathOfAlias('DeviceDetector', YiiBase::getPathOfAlias('analytics.vendor.device-detector'));
		}
		$userAgentInfo = \DeviceDetector\DeviceDetector::getInfoFromUserAgent($userAgent);
		if (isset($userAgentInfo['bot']) || (isset($userAgentInfo['os']['client']) && $userAgentInfo['client']['short_name'] == "UNK")) {
			return array(
				'os' => 'Bot',
				'browser' => '',
				'browser_version' => '',
			);
		}

		return array(
			'os' => $userAgentInfo['os']['short_name'],
			'browser' => $userAgentInfo['client']['short_name'],
			'browser_version' => current(explode('.', $userAgentInfo['client']['version'])),
		);
	}

	/**
	 * @param $userAgentData
	 * @return bool
	 */
	public static function isBot($userAgentData)
	{
		return isset($userAgentData['os']) ? $userAgentData['os'] == 'Bot' : false;
	}

	private static function getSessionCode($sessionId)
	{
		return substr(md5($sessionId, true), 0, 8);
	}

	public static function p2n($ipString)
	{
		$ip = @inet_pton($ipString);
		return $ip === false ? "\x00\x00\x00\x00" : $ip;
	}

	public static function n2p($ipString)
	{
		return @inet_ntop($ipString);
	}

	public static function getActionId($name, $type = self::TYPE_INTERNAL_URL)
	{
		$name = trim($name);
		if (!strlen($name))
			return 0;
		$isUrl = in_array($type, array(self::TYPE_INTERNAL_URL));
		if ($isUrl) {
			$name = self::normalizeUrl($name, $type == self::TYPE_INTERNAL_URL);
			if (!$name || self::excludeUrl($name))
				return 0;
		}
		$hash = $name;

		// Ищем страницу в действиях
		$id = self::createCommand()->from('log_action')
			->select('id')
			->where('hash = CRC32(:hash)', compact('hash'))
			->andWhere('type = :type', compact('type'))
			->queryScalar();

		// Создаем новую при отсутствии
		if ($id === false) {
			self::getDb()->autoCommit = true;
			self::getDb()->createCommand()->insert('log_action', array(
				'name' => $name,
				'hash' => new CDbExpression('CRC32(:hash)', compact('hash')),
				'type' => $type,
			));

			$id = self::getDb()->getLastInsertID();
			self::getDb()->autoCommit = false;
		}
		return $id;
	}

	public static function excludeUrl($url)
	{
		$noHtml = preg_match('#\.(h$|ht$|[^h](t$|[^t](m$|[^m](l$|[^l])?)?)?)?$#i', $url);
		$noStat = preg_match(self::getModule()->excludeUrlRegexp, $url);
		return $noHtml || $noStat;
	}

	/**
	 * @return AnalyticsModule
	 */
	public static function getModule()
	{
		// @todo !!!!!!!!!!!!! переписать Yii::app()->statisticManager
		// return Yii::app()->getModule('analytics');
		return Yii::app()->statisticManager;
	}

	public static function normalizeUrl($url, $withoutGet = true)
	{
		if ($withoutGet) {
			list($url) = explode('?', $url);
			$url = str_replace(Yii::app()->createAbsoluteUrl('/'), '/', $url);
		}
		$url = str_replace(array(
			'http://www.',
			'https://www.',
			'http://',
			'https://',
		), '', $url);
		if ($url == '')
			return false;
		// @todo !!!!!!!!!!!!! переписать Yii::app()->statisticManager->getUrlCutRegexp()
		if (preg_match(self::getModule()->getUrlCutRegexp(), $url, $matches))
			$url = $matches[1];
		if ($url == '')
			return '/';
		return $url;
	}

	/**
	 * @param array $joins
	 * @param int $type
	 * @return CDbCommand
	 */
	public static function statsPages($joins = array(), $type = self::TYPE_INTERNAL_URL)
	{
		$command = self::createCommand()
			->select("a.id AS id, a.name AS url")
			->from("log_action a")
			->where('type = :pageType', array(
				':pageType' => $type
			))->group("a.id");
		foreach ($joins as $join)
			self::statsPagesJoin($join, $command);
		return $command;
	}

	/**
	 * TODO: exits
	 * @param string $join (hits|enters|exits|visits)
	 * @param int $actionId
	 * @param CDbCommand $command
	 */
	public static function statsPagesJoin($join, $command, $actionId = null)
	{
		$select = str_replace('`', '', $command->select);
		if ($actionId !== null) {
			$actionCondition = "AND action_id_url = :actionId";
			$command->params[':actionId'] = $actionId;
		} else
			$actionCondition = '';
		switch ($join) {
			case 'hits':
				$command->select = $select . ", hits.count AS hits";
				$command->join("(SELECT COUNT(id) count, action_id_url FROM log_hit WHERE 1=1 {rangeCondition} {$actionCondition} GROUP BY action_id_url) hits", "hits.action_id_url = a.id");
				break;
			case 'visits':
				$command->select = $select . ", visits.count AS visits";
				$command->join("(SELECT COUNT(DISTINCT visit_id) count, action_id_url FROM log_hit WHERE 1=1 {rangeCondition} {$actionCondition} GROUP BY action_id_url) visits", "visits.action_id_url = a.id");
				break;
			case 'visitors':
				$command->select = $select . ", visitors.count AS visitors";
				$command->join("(SELECT COUNT(DISTINCT visitor_id) count, action_id_url FROM log_hit WHERE 1=1 {rangeCondition} {$actionCondition} GROUP BY action_id_url) visitors", "visitors.action_id_url = a.id");
				break;
			case 'enters':
				$command->select = $select . ", enters.count AS enters";
				$command->join("(SELECT COUNT(h.id) `count`, action_id_url FROM log_hit h JOIN (SELECT visit_id, MIN(created) AS minCreated FROM log_hit GROUP BY visit_id) `min` ON (min.visit_id = h.visit_id AND min.minCreated = h.created) WHERE 1 = 1 {rangeCondition} {$actionCondition} GROUP BY h.action_id_url) enters", "enters.action_id_url = a.id");
				break;
			case 'exits':
				$command->select = $select . ", exits.count AS exits";
				$command->join("(SELECT COUNT(h.id) `count`, action_id_url FROM log_hit h JOIN (SELECT visit_id, MAX(created) AS maxCreated FROM log_hit GROUP BY visit_id) `max` ON (max.visit_id = h.visit_id AND max.maxCreated = h.created) WHERE 1 = 1 {rangeCondition} {$actionCondition} GROUP BY h.action_id_url) exits", "exits.action_id_url = a.id");
				break;
		}
	}

	/**
	 * @param int $actionId
	 * @return CdbCommand
	 */
	public static function statsPage($actionId)
	{
		$command = self::createCommand()
			->select("a.id, a.name, a.type")
			->from("log_action a")
			->where("a.id = :actionId");
		self::statsPagesJoin('hits', $command, $actionId);
		self::statsPagesJoin('visits', $command, $actionId);
		self::statsPagesJoin('visitors', $command, $actionId);
		self::statsPagesJoin('enters', $command, $actionId);
		self::statsPagesJoin('exits', $command, $actionId);
		return $command;
	}

}
