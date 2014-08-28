<?php

class AnalyticsCounterWidget extends CWidget {

	public $jsOptions = array();
	public $sendingData = array();

	public function run() {
		$this->jsOptions['debug'] = YII_DEBUG;
		$this->jsOptions['requestUrl'] = CHtml::normalizeUrl(array('/analytics/counter/index'));
		$this->jsOptions['data'] = $this->sendingData;
		$this->registerScripts();
	}

	public function registerScripts() {
		$assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets', -1, false, YII_DEBUG);
		Yii::app()->clientScript->registerCoreScript('jquery.js');
		Yii::app()->clientScript->registerScriptFile($assetsUrl . '/analytics.js', CClientScript::POS_END);
		$jsOptions = CJavaScript::encode($this->jsOptions);
		Yii::app()->clientScript->registerScript(__CLASS__, "$.fn.analytics({$jsOptions});", CClientScript::POS_READY);
	}

}
