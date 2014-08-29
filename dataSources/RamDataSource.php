<?php
require_once(__DIR__ . '/PerformanceDataSource.php');

class RamDataSource extends PerformanceDataSource
{

	public function getSeriesData($fromDate, $toDate, $zoom)
	{
		$data = $this->getData($fromDate, $toDate, $zoom);
		$graphs = array(
			'ram' => array(
				'series' => array(
					'ram' => array('type' => 'area', 'data' => array()),
				),
			),
		);
		foreach ($data as $row) {
			$graphs['ram']['series']['ram']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['ram'] / 1000);
		}
		return $graphs;
	}

	public function getYAxisData()
	{
		$data = parent::getYAxisData();
		$data['title']['text'] = "Объём (МБ)";
		return $data;
	}
} 