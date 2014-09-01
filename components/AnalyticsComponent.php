<?php

YiiBase::setPathOfAlias('analytics', dirname(dirname(__FILE__)));
YiiBase::import('analytics.components.AnalyticsHelper');

class AnalyticsComponent extends CApplicationComponent
{

	public $enabled = true;

	public static $requestId;

	public $actionName = null;

	private static $_onStartCpuTime;

	public function init()
	{
		if($this->enabled) {
			Yii::app()->getModule('rapanel')->controllerMap['analytics'] = array(
				'class' => 'analytics.controllers.AnalyticsController',
			);
			self::$_onStartCpuTime = self::getCpuTime();
			self::$requestId = uniqid();
		}
		parent::init();
	}

	public static function onApplicationEnd()
	{
		if (Yii::app() instanceof CWebApplication && Yii::app()->getComponent('analytics')->enabled) {
			$logger = new CLogger;
			$ramUsage = round($logger->getMemoryUsage() / 1048576, 2);
			$executionTime = round($logger->getExecutionTime(), 3);
			$cpuUsage = self::$_onStartCpuTime ? self::getCpuTime() - self::$_onStartCpuTime : 0;
			$cpuTime = round($cpuUsage / 1000000, 3);

			Yii::trace("Memory used: " . $ramUsage . " MB");
			Yii::trace("Execution time: " . $executionTime . " sec");
			Yii::trace("CPU used: " . $cpuTime . " sec");

			Yii::app()->user->setState(md5('analytics:' . self::$requestId), array(
				'ram' => $ramUsage,
				'time' => $executionTime,
				'cpu' => $cpuTime,
				'url' => Yii::app()->request->hostInfo . Yii::app()->request->requestUri,
				'referrer' => Yii::app()->request->urlReferrer,
			));
		}
	}

	private static function getCpuTime()
	{
		$resourceUsage = getrusage();
		return $resourceUsage["ru_utime.tv_sec"] * 1e6 + $resourceUsage["ru_utime.tv_usec"];
	}

} 