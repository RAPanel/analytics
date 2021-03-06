<?php

class AnalyticsCounterWidget extends CWidget {

	public $jsOptions = array();

	public function run() {
		$this->jsOptions['debug'] = YII_DEBUG;
		$this->jsOptions['requestUrl'] = CHtml::normalizeUrl(array('/analytics/index'));
		$this->jsOptions['data']['id'] = AnalyticsComponent::$requestId;
		$this->registerScripts();
	}

	public function registerScripts() {
		$assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets', -1, false, YII_DEBUG);
		Yii::app()->clientScript->registerScriptFile($assetsUrl . '/analytics.js', CClientScript::POS_END);
		$jsOptions = CJavaScript::encode($this->jsOptions);
		Yii::app()->clientScript->registerScript(__CLASS__, "$.fn.analytics({$jsOptions});", CClientScript::POS_READY);
	}

}
