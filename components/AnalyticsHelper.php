<?php
YiiBase::setPathOfAlias('analytics', dirname(dirname(__FILE__)));
YiiBase::import('analytics.components.UserAgentParser');
/**
 * Class AnalyticsHelper
 * Хелпер для построения выборок и обработки данных
 */
class AnalyticsHelper
{
	/**
	 * @return CDbConnection
	 */
	public static function getDb() {
		return Yii::app()->db;
	}

	/**
	 * @param string|null $query
	 * @return CDbCommand
	 */
	public static function createCommand($query = null) {
		return self::getDb()->createCommand($query);
	}

	public static function incrementLog($data)
	{
		// Проверяем, что кто-то посетил
		if (empty($data['session']))
			return;
		if (!$sessionIdEncoded = self::getSessionCode($data['session']))
			return;
		// Ищем живой визит с таймоутом 30 минут
		$visitId = self::createCommand()->from('log_visit')
			->select('id')
			->where("BINARY visitor_id = :session", compact('session'))
//            ->andWhere('last_action_time > FROM_UNIXTIME(:time)', array( 'time' => $data['created'] - 60 * 30))
			->order('last_action_time DESC')
			->queryScalar();

		// Обновляем инфу визита или создаем новый
		if ($visitId) {
			self::createCommand()->update('log_visit', array(
				'last_action_time' => new CDbExpression('FROM_UNIXTIME(' . $data['created'] . ')'),
				'total_time' => new CDbExpression(':time - UNIX_TIMESTAMP(`first_action_time`)', array('time' => $data['created'])),
				'total_actions' => new CDbExpression('`total_actions` + 1'),
			), 'id=:visitId', compact('visitId'));
		} else {
			$userAgentData = self::parseUserAgentData($data['userAgent']);
			if (self::isBot($userAgentData))
				return;

			self::getDb()->autoCommit = true;
			self::createCommand()->insert('log_visit', CMap::mergeArray($userAgentData, array(
					'visitor_id' => $sessionIdEncoded,
					'last_action_time' => new CDbExpression('FROM_UNIXTIME(' . $data['created'] . ')'),
					'first_action_time' => new CDbExpression('FROM_UNIXTIME(' . $data['created'] . ')'),
					'location_ip' => self::p2n($data['ip']),
					'total_time' => 0,
					'total_actions' => 1,
					'action_id_ref' => self::getActionId($data['referrer'], 2),
				))
			);

			$visitId = self::getDb()->getLastInsertID();
			self::getDb()->autoCommit = false;
		}

		if (!$actionId = self::getActionId($data['url']))
			return;

		self::getDb()->createCommand()->insert('log_hit', array(
			'visitor_id' => $sessionIdEncoded,
			'visit_id' => $visitId,
			'action_id_name' => $actionId,
			'action_id_url' => $actionId,
			'action_id_event' => 0,
			'time_cpu' => $data['time_cpu'],
			'time_exec' => $data['time_exec'],
			'ram' => $data['ram'],
			'created' => new CDbExpression('FROM_UNIXTIME(' . $data['created'] . ')'),
		));
	}

	/**
	 * @param string $userAgent
	 * @return array
	 */
	public static function parseUserAgentData($userAgent)
	{
		YiiBase::import('analytics.vendors.Spyc');
		YiiBase::setPathOfAlias('DeviceDetector', YiiBase::getPathOfAlias('analytics.vendor.device-detector'));
		$userAgentInfo = \DeviceDetector\DeviceDetector::getInfoFromUserAgent($userAgent);
		if(isset($userAgentInfo['bot'])) {
			return array(
				'os' => 'Bot',
				'browser' => '',
				'browser_version' => '',
			);
		}
		return array(
			'os' => $userAgentInfo['os']['short_name'],
			'browser' => $userAgentInfo['browser']['short_name'],
			'browser_version' => array_shift(explode('.', $userAgentInfo['browser']['version'])),
		);
	}

	/**
	 * @param $userAgentData
	 * @return bool
	 */
	public static function isBot($userAgentData) {
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

	public static function getActionId($url, $type = 1)
	{
		$url = RStatPage::normalizeUrl($url, $type == 1);
		if (!$url || self::excludeUrl($url))
			return false;
		$hash = $url;

		// Ищем страницу в действиях
		$id = self::createCommand()->from('log_action')
			->select('id')
			->where('hash = CRC32(:hash)', compact('hash'))
			->queryScalar();

		// Создаем новую при отсутствии
		if ($id === false) {
			self::getDb()->autoCommit = true;
			self::getDb()->createCommand()->insert('log_action', array(
				'name' => $url,
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
	public static function getModule() {
		return Yii::app()->getModule('analytics');
	}

}
