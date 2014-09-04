<?php
require_once(__DIR__ . '/PerformanceDataSource.php');

class CpuDataSource extends PerformanceDataSource
{

	public function getSeriesData($dates, &$zoom)
	{
		$data = $this->getData($dates, $zoom);
		$graphs = array(
			'cpu' => array(
				'series' => array(
					'cpu' => array('type' => 'spline', 'data' => array()),
				),
			),
		);
		foreach ($data as $row) {
			$graphs['cpu']['series']['cpu']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['cpu'] / 1000);
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