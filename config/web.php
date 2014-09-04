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
			'onEnd' => 'AnalyticsComponent::onApplicationEnd',
		),
	),
));
