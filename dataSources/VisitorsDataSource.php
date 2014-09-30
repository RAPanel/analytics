<?php

require_once(__DIR__ . '/ViewsDataSource.php');

class VisitorsDataSource extends ViewsDataSource {

	public function getSeriesData($dates, &$zoom)
	{
		$data = $this->getData($dates, $zoom);
		$graph = array(
			'series' => array(
				'visitors' => array(
					'yAxis' => 'views',
					'type' => 'spline',
					'data' => array()
				),
			),
		);
		foreach ($data as $row) {
			$graph['series']['visitors']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['visitors']);
		}
		return $graph;
	}


} 