<?php

require_once(__DIR__ . '/ViewsDataSource.php');

class HitsDataSource extends ViewsDataSource
{

	public function getSeriesData($dates, &$zoom)
	{
		$data = $this->getData($dates, $zoom);
		$graph = array(
			'series' => array(
				'hits' => array(
					'yAxis' => 'views',
					'type' => 'spline',
					'data' => array()
				),
			),
		);
		foreach ($data as $row) {
			$graph['series']['hits']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['hits']);
		}
		return $graph;
	}


} 