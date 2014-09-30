<?php

abstract class AnalyticsDataSource extends CComponent
{

	public $defaultZoom = 'day';
	public $type = 'spline';

	public function getGraphData($fromDate, $toDate, &$zoom)
	{
		$dates = $this->getDates($fromDate, $toDate, $zoom);
		$graph = $this->getSeriesData($dates, $zoom);
		if (isset($graph['series'])) {
			foreach ($graph['series'] as $serieId => $serie) {
				$graph['series'][$serieId] = CMap::mergeArray(array('name' => $this->getName($serieId), 'type' => $this->type), $graph['series'][$serieId]);
			}
			$graph['series'] = array_values($graph['series']);
		}
		$graph = CMap::mergeArray(array(
			'xAxis' => $this->getXAxisData($zoom),
			'yAxis' => $this->getYAxisData(),
		), $graph);
		return $graph;
	}

	public function getXAxisData($zoom)
	{
		$zoom = $this->getZoom($zoom);
		switch ($zoom) {
			case 'minute':
				$title = "Минута";
				break;
			case 'hour':
				$title = "Час";
				break;
			case 'day':
				$title = "День";
				break;
			case 'month':
				$title = "Месяц";
				break;
			default:
				return false;
		}
		return array(
			'title' => array(
				'text' => $title,
			),
			'minRange' => $this->getZoomTick($zoom),
		);
	}

	public function getYAxisData()
	{
		return array(
			'title' => array(
				'text' => '',
			),
		);
	}

	public function getName($seriesId)
	{
		return $this->name;
	}

	abstract public function getSeriesData($dates, &$zoom);

	public function getZoom(&$zoom)
	{
		if ($zoom === null)
			return $this->defaultZoom;
		return $zoom;
	}

	public function getDates($fromStr, $toStr, $zoom)
	{
		if ($fromStr === null || $toStr === null)
			return array(null, null);
		$from = strtotime($fromStr);
		$to = strtotime($toStr);

		// Если диапазон меньше максимальной детализации поля выбора - устанавливаем в 1 день
		if ($to - $from < 24 * 3600)
			$to = $from + $this->getZoomTick('day') - 1;
		$fromStr = date($this->getZoomPattern($zoom), $from);
		$toStr = date($this->getZoomPattern($zoom, true), $to);
		return array($fromStr, $toStr);
	}

	public function getZoomTick($zoom)
	{
		switch ($zoom) {
			case 'minute':
				return 60;
			case 'hour':
				return 3600;
			case 'day':
				return 3600 * 24;
			case 'month':
				return 3600 * 24 * 30;
		}
		return 0;
	}

	public function getZoomPattern($zoom, $max = false)
	{
		switch ($zoom) {
			case 'minute':
				return $max ? "Y-m-d H:i:59" : "Y-m-d H:i:00";
			case 'hour':
				return $max ? "Y-m-d H:59:59" : "Y-m-d H:00:00";
			case 'day':
				return $max ? "Y-m-d 23:59:59" : "Y-m-d 00:00:00";
			case 'month':
				return $max ? "Y-m-31 23:59:59" : "Y-m-1 0:00:00";
		}
		return "Y-m-d H:i:s";
	}

	public function getZoomMysqlPattern($zoom)
	{
		return str_replace(array("Y-", "m-", "d ", "H:", "i:", "s"), array("%Y-", "%m-", "%d ", "%H:", "%i:", "%s"), $this->getZoomPattern($zoom));
	}

} 