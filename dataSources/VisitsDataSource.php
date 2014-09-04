<?php

require_once(__DIR__ . '/ViewsDataSource.php');

class VisitsDataSource extends ViewsDataSource {

	public function getSeriesData($dates, &$zoom)
	{
		$data = $this->getData($dates, $zoom);
		$graphs = array(
			'views' => array(
				'series' => array(
					'visits' => array('type' => 'spline', 'data' => array()),
				),
			),
		);
		foreach ($data as $row) {
			$graphs['views']['series']['visits']['data'][] = array(strtotime($row['date']) * 1000, (int)$row['visits']);
		}
		return $graphs;
	}


} 