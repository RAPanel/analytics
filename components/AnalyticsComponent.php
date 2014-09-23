<?php

YiiBase::setPathOfAlias('analytics', dirname(dirname(__FILE__)));
YiiBase::import('analytics.components.AnalyticsHelper');

class AnalyticsComponent extends CApplicationComponent
{

	public $cacheId = 'cacheFast';

	public $enabled = true;

	public static $requestId;

	private static $_onStartCpuTime;

	public function init()
	{
		if ($this->enabled) {
			$this->addController();
			self::$_onStartCpuTime = self::getCpuTime();
			self::$requestId = uniqid();
		}
		parent::init();
	}

	private function addController()
	{
		$modules = Yii::app()->getModules();
		if (isset($modules['rapanel'])) {
			$controllerMap = isset($modules['rapanel']['controllerMap']) ? $modules['rapanel']['controllerMap'] : array();
			Yii::app()->setModules(array(
				'rapanel' => array(
					'controllerMap' => CMap::mergeArray($controllerMap, array(
						'analytics' => array(
							'class' => 'analytics.controllers.AnalyticsController',
						)
					)),
				)
			));
		}
	}

	public function reachGoal($goalName) {

	}

	public static function onApplicationEnd($event)
	{
		$app = $event->sender;
		$analytics = $app->getComponent('analytics');
		if ($app instanceof CWebApplication && $analytics->enabled && !$app->request->isAjaxRequest) {
			$logger = new CLogger;
			$ramUsage = round($logger->getMemoryUsage() / 1048576, 2);
			$executionTime = round($logger->getExecutionTime(), 3);
			$cpuUsage = self::$_onStartCpuTime ? self::getCpuTime() - self::$_onStartCpuTime : 0;
			$cpuTime = round($cpuUsage / 1000000, 3);

			Yii::trace("Memory used: " . $ramUsage . " MB");
			Yii::trace("Execution time: " . $executionTime . " sec");
			Yii::trace("CPU used: " . $cpuTime . " sec");

			Yii::app()->{$analytics->cacheId}->set('analytics:' . self::$requestId, array(
				'ram' => $ramUsage,
				'time' => $executionTime,
				'cpu' => $cpuTime,
				'url' => Yii::app()->request->hostInfo . Yii::app()->request->requestUri,
				'referrer' => Yii::app()->request->urlReferrer,
			), 5 * 60);
		}
	}

	private static function getCpuTime()
	{
		$resourceUsage = getrusage();
		return $resourceUsage["ru_utime.tv_sec"] * 1e6 + $resourceUsage["ru_utime.tv_usec"];
	}

} 