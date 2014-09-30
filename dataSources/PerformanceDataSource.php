<?php

require_once(__DIR__ . '/AnalyticsDataSource.php');

abstract class PerformanceDataSource extends AnalyticsDataSource
{

	public $defaultZoom = 'hour';

	private static $_cache = array();

	public function getData($dates, $zoom) {
		$zoom = $this->getZoom($zoom);
		$cacheString = $dates[0] . ':' . $dates[1] . ':' . $zoom;
		if(!isset(self::$_cache[$cacheString])) {
			$dateFormat = $this->getZoomMysqlPattern($zoom);
			$command = Yii::app()->db->createCommand();
			$command->select("MAX(ram) ram, AVG(ram) avgRam, AVG(time_cpu) cpu, AVG(time_exec) time, DATE_FORMAT(created, :dateFormat) date")
				->from("log_hit")
				->group("DATE_FORMAT(created, :dateFormat)");
			$params = array(
				':dateFormat' => $dateFormat,
			);
			if($dates[0] !== null) {
				$params[':dateFrom'] = $dates[0];
				$params[':dateTo'] = $dates[1];
				$command->where("created BETWEEN :dateFrom AND :dateTo");
			}
			self::$_cache[$cacheString] = $command->queryAll(true, $params);
		}
		return self::$_cache[$cacheString];
	}

	public function getName($seriesId)
	{
		switch ($seriesId) {
			case 'ram':
				return "Max Ram";
			case 'avgRam':
				return "Average Ram";
			case 'cpu':
				return "Average CPU";
			case 'time':
				return "Average execution time";
		}
		return "";
	}

} 