<?php
require_once(__DIR__ . '/PerformanceDataSource.php');

class ExecTimeDataSource extends PerformanceDataSource
{

	public function getSeriesData($fromDate, $toDate, $zoom)
	{
		$data = $this->getData($fromDate, $toDate, $zoom);
		$graphs = array(
			'cpu' => array(
				'series' => array(
					'time' => array('type' => 'spline', 'data' => array()),
				),
			),
		);
		foreach ($data as $row) {
			$graphs['cpu']['series']['time']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['time'] / 1000);
		}
		return $graphs;
	}

	public function getYAxisData()
	{
		$data = parent::getYAxisData();
		$data['title']['text'] = "Время (с)";
		return $data;
	}
} 