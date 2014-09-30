<?php

require_once(__DIR__ . '/AnalyticsDataSource.php');

abstract class ViewsDataSource extends AnalyticsDataSource
{
	public $defaultZoom = 'day';

	private static $_cache = array();

	public function getData($dates, &$zoom) {
		$zoom = $this->getZoom($zoom);
		$cacheString = $dates[0] . ':' . $dates[1] . ':' . $zoom;
		if(!isset(self::$_cache[$cacheString])) {
			$dateFormat = $this->getZoomMysqlPattern($zoom);
			/** @var CdbCommand $command */
			$command = Yii::app()->db->createCommand();
			$command->select("COUNT(id) hits, COUNT(DISTINCT visitor_id) visitors, COUNT(DISTINCT visit_id) visits, DATE_FORMAT(created, :dateFormat) date")
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

	public function getYAxisData() {
		return array(
			'views' => array(
				'opposite' => true,
				'labels' => array(
					'format' => "{value}",
				),
				'title' => array(
					'text' => 'Количество',
				)
			),
		);
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