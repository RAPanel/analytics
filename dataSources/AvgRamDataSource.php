<?php
require_once(__DIR__ . '/PerformanceDataSource.php');

class AvgRamDataSource extends PerformanceDataSource
{

	public function getSeriesData($dates, &$zoom)
	{
		$data = $this->getData($dates, $zoom);
		$graph = array(
			'series' => array(
				'avgRam' => array(
					'yAxis' => 'ram',
					'type' => 'area',
					'data' => array()
				),
			),
		);
		foreach ($data as $row) {
			$graph['series']['avgRam']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['avgRam'] / 1000);
		}
		return $graph;
	}

	public function getYAxisData() {
		return array(
			'ram' => array(
				'opposite' => false,
				'labels' => array(
					'format' => "{value} MB",
				),
				'title' => array(
					'text' => 'Объём памяти',
				)
			),
		);
	}
} 