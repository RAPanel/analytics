<?php

abstract class AnalyticsDataSource extends CComponent
{

	public $defaultZoom = 'day';
	public $type = 'spline';

	public function getGraphData($fromDate, $toDate, $zoom)
	{
		$graphs = $this->getSeriesData($fromDate, $toDate, $zoom);
		foreach ($graphs as $graphId => $graph) {
			if(isset($graph['series'])) {
				foreach ($graph['series'] as $serieId => $serie) {
					$graphs[$graphId]['series'][$serieId] = CMap::mergeArray(array('name' => $this->getName($serieId), 'type' => $this->type), $graphs[$graphId]['series'][$serieId]);
				}
				$graphs[$graphId]['series'] = array_values($graphs[$graphId]['series']);
			}
			$graphs[$graphId] = CMap::mergeArray(array(
				'xAxis' => $this->getXAxisData($zoom),
				'yAxis' => $this->getYAxisData(),
			), $graphs[$graphId]);
		}
		return $graphs;
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

	abstract public function getSeriesData($fromDate, $toDate, $zoom);

	public function getZoom($zoom)
	{
		if ($zoom === null)
			return $this->defaultZoom;
		return $zoom;
	}

	public function getDates($fromStr, $toStr, $zoom)
	{
		if ($fromStr === null)
			$fromStr = date("Y-m-d H:i:s", time());
		if ($toStr === null)
			$toStr = date("Y-m-d H:i:s", time() + $this->getZoomTick($zoom));
		$from = strtotime($fromStr);
		$to = strtotime($toStr);
		$zoomPattern = $this->getZoomPattern($zoom);
		$fromStr = date($zoomPattern, $from);
		$toStr = date($zoomPattern, $to);
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
				return "Y-m-d H:i:s";
			case 'hour':
				return $max ? "Y-m-d H:m:59" : "Y-m-d H:m:00";
			case 'day':
				return $max ? "Y-m-d H:59:59" : "Y-m-d H:00:00";
			case 'month':
				return $max ? "Y-m-d 23:59:59" : "Y-m-d 0:00:00";
		}
		return "Y-m-d H:i:s";
	}

	public function getZoomMysqlPattern($zoom)
	{
		return str_replace(array("Y-", "m-", "d ", "H:", "i:", "s"), array("%Y-", "%m-", "%d ", "%H:", "%i:", "%s"), $this->getZoomPattern($zoom));
	}
} 