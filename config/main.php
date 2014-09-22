<?php
return array(
	'components' => array(
		'analytics' => array(
			'class' => 'analytics.components.AnalyticsComponent',
			'enabled' => true,
		),
		'urlManager' => array(
			'rules' => array(
				'<m_:(analytics)>' => array('<m_>//'),
				'<m_:(analytics)>/<c_>' => array('<m_>/<c_>/index'),
				'<m_:(analytics)>/<c_>/<a_>' => array('<m_>/<c_>/<a_>'),
			),
		),
	),
);
