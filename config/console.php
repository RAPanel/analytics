<?php
return CMap::mergeArray(require(__DIR__ . '/main.php'), array(
	'commandMap' => array(
		'analytics' => array(
			'class' => 'analytics.components.AnalyticsCommand',
		),
	),
));
