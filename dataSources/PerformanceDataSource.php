<?php

require_once(__DIR__ . '/AnalyticsDataSource.php');

abstract class PerformanceDataSource extends AnalyticsDataSource
{

	public $defaultZoom = 'minute';

	private static $_cache = array();

	public function getData($fromDate, $toDate, $zoom) {
		$zoom = $this->getZoom($zoom);
		$dates = $this->getDates($fromDate, $toDate, $zoom);
		$cacheString = $dates[0] . ':' . $dates[1] . ':' . $zoom;
		if(!isset(self::$_cache[$cacheString])) {
			$dateFormat = $this->getZoomMysqlPattern($zoom);
			$command = Yii::app()->db->createCommand("SELECT MAX(ram) ram, AVG(time_cpu) cpu, AVG(time_exec) time, DATE_FORMAT(created, :dateFormat) date FROM log_hit WHERE created BETWEEN :dateFrom AND :dateTo GROUP BY DATE_FORMAT(created, :dateFormat);");
			self::$_cache[$cacheString] = $command->queryAll(true, array(
				':dateFrom' => $dates[0],
				':dateTo' => $dates[1],
				':dateFormat' => $dateFormat,
			));
		}
		return self::$_cache[$cacheString];
	}


	public function getName($seriesId)
	{
		switch ($seriesId) {
			case 'ram':
				return "Max Ram";
			case 'cpu':
				return "Average CPU";
			case 'time':
				return "Average execution time";
		}
		return "";
	}

} 