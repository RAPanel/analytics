<?php

require_once(__DIR__ . '/ViewsDataSource.php');

class VisitsDataSource extends ViewsDataSource {

	public function getSeriesData($dates, &$zoom)
	{
		$data = $this->getData($dates, $zoom);
		$graph = array(
			'series' => array(
				'visits' => array(
					'yAxis' => 'views',
					'type' => 'spline',
					'data' => array()
				),
			),
		);
		foreach ($data as $row) {
			$graph['series']['visits']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['visits']);
		}
		return $graph;
	}


} 