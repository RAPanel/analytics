<?php

YiiBase::setPathOfAlias('analytics', dirname(dirname(__FILE__)));

class AnalyticsComponent extends CApplicationComponent
{

	public $enabled = true;

	public function init()
	{
		if($this->enabled) {
			Yii::app()->getModule('rapanel')->controllerMap['analytics'] = array(
				'class' => 'analytics.controllers.AnalyticsController',
			);
		}
		parent::init();
	}

} 