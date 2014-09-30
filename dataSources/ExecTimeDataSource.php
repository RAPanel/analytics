<?php
require_once(__DIR__ . '/PerformanceDataSource.php');

class ExecTimeDataSource extends PerformanceDataSource
{

	public function getSeriesData($dates, &$zoom)
	{
		$data = $this->getData($dates, $zoom);
		$graph = array(
			'series' => array(
				'time' => array(
					'yAxis' => 'time',
					'type' => 'spline',
					'data' => array()
				),
			),
		);
		foreach ($data as $row) {
			$graph['series']['time']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['time'] / 1000);
		}
		return $graph;
	}

	public function getYAxisData() {
		return array(
			'time' => array(
				'opposite' => false,
				'labels' => array(
					'format' => "{value} с.",
				),
				'title' => array(
					'text' => 'Время',
				)
			),
		);
	}
} 