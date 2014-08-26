<?php

YiiBase::setPathOfAlias('analytics', dirname(__FILE__));

class AnalyticsModule extends CWebModule {

	public $defaultController = 'counter';

	public $import = array(
		'analytics.components.*',
		'analytics.controllers.*',
	);

	public function install() {
		$sqlFile = YiiBase::getPathOfAlias('analytics.data') . '/schema.sql';
		Yii::app()->db->createCommand(file_get_contents($sqlFile))->execute();
	}

}
