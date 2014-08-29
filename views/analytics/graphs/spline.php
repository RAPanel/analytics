<?php
return array(
	'chart' => array(
		'height' => 500,
	),
	'rangeSelector' => array(
		'selected' => 1,
		'buttons' => array(
			array(
				'type' => 'day',
				'count' => 1,
				'text' => '1d',
			),
			array(
				'type' => 'day',
				'count' => 7,
				'text' => '7d',
			),
			array(
				'type' => 'month',
				'count' => 1,
				'text' => '1m',
			),
			array(
				'type' => 'month',
				'count' => 3,
				'text' => '3m',
			),
			array(
				'type' => 'month',
				'count' => 6,
				'text' => '6m',
			),
			array(
				'type' => 'year',
				'count' => 1,
				'text' => '1y',
			),
			array(
				'type' => 'all',
				'text' => 'all',
			),
		),
	),
	'legend' => array(
		'enabled' => true,
		'verticalAlign' => 'top',
	),
	'xAxis' => array(
		'title' => array(
			'text' => 'Час',
		),
		'minRange' => 3600000,
	),
);