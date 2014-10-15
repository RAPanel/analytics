<?php
return CMap::mergeArray(require(__DIR__ . '/main.php'), array(
	'preload' => array(
		'analytics' => 'web',
	),
	'controllerMap' => array(
		'analytics' => array(
			'class' => 'analytics.controllers.CounterController',
		),
	),
	'components' => array(
		'loader' => array(
			'onEnd' => array(
				'AnalyticsComponent::onApplicationEnd'
			),
		),
		'moduleMapper' => array(
			'menuExtra' => array(
				'Статистика' => array(
					'Просмотры' => array('analytics/graph', 'dataSources' => 'hits,visits,visitors', 'graphZoom' => '7d'),
					'Производительность' => array('analytics/graph', 'dataSources' => 'ram,cpu,execTime', 'graphZoom' => '7d'),
					'Всё' => array('analytics/graph', 'dataSources' => 'hits,visits,visitors,ram,cpu,execTime', 'graphZoom' => '7d'),
				),
			),
		),
	),
));
