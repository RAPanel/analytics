<?php

require_once(__DIR__ . '/ViewsDataSource.php');

class VisitorsDataSource extends ViewsDataSource {

	public function getSeriesData($fromDate, $toDate, $zoom)
	{
		$data = $this->getData($fromDate, $toDate, $zoom);
		$graphs = array(
			'views' => array(
				'series' => array(
					'visitors' => array('type' => 'spline', 'data' => array()),
				),
			),
		);
		foreach ($data as $row) {
			$graphs['views']['series']['visitors']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['visitors']);
		}
		return $graphs;
	}


} 