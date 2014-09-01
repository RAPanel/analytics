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
            return;
        if (!$sessionIdEncoded = self::getSessionCode($data['session']))
            return;
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
                return;

            self::getDb()->autoCommit = true;
            self::createCommand()->insert('log_visit', CMap::mergeArray($userAgentData, array(
                    'visitor_id' => $sessionIdEncoded,
                    'lastmod' => new CDbExpression('FROM_UNIXTIME(' . $data['created'] . ')'),
                    'created' => new CDbExpression('FROM_UNIXTIME(' . $data['created'] . ')'),
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
            'action_id_url' => self::getActionId($data['url']),
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
	    if(!strlen($name))
		    return 0;
	    $isUrl = in_array($type, array(self::TYPE_INTERNAL_URL));
		if($isUrl) {
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

}
