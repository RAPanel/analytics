<?php

require_once(__DIR__ . '/AnalyticsDataSource.php');

abstract class ViewsDataSource extends AnalyticsDataSource
{
	public $defaultZoom = 'day';

	private static $_cache = array();

	public function getData($fromDate, $toDate, $zoom) {
		$zoom = $this->getZoom($zoom);
		$dates = $this->getDates($fromDate, $toDate, $zoom);
		$cacheString = $dates[0] . ':' . $dates[1] . ':' . $zoom;
		if(!isset(self::$_cache[$cacheString])) {
			$dateFormat = $this->getZoomMysqlPattern($zoom);
			$command = Yii::app()->db->createCommand("SELECT COUNT(id) hits, COUNT(DISTINCT visitor_id) visitors, COUNT(DISTINCT visit_id) visits, DATE_FORMAT(created, :dateFormat) date FROM log_hit WHERE created BETWEEN :dateFrom AND :dateTo GROUP BY DATE_FORMAT(created, :dateFormat);");
			self::$_cache[$cacheString] = $command->queryAll(true, array(
				':dateFrom' => $dates[0],
				':dateTo' => $dates[1],
				':dateFormat' => $dateFormat,
			));
		}
		return self::$_cache[$cacheString];
	}

	public function getYAxisData()
	{
		$data = parent::getYAxisData();
		$data['title']['text'] = "Количество";
		return $data;
	}

	public function getName($seriesId)
	{
		switch ($seriesId) {
			case 'hits':
				return "Просмотры";
			case 'visitors':
				return "Посетители";
			case 'visits':
				return "Визиты";
		}
		return "";
	}

} 