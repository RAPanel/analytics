<?php

/**
 * Class CounterController
 * Контроллер, на который приходит запрос с сайта от JS скрипта
 */
class CounterController extends RController
{

	public function actionIndex($u, $r)
	{
		if(!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		$performance = Yii::app()->user->getState('performance');
		AnalyticsHelper::incrementLog(array(
			'url' => $u,
			'referrer' => $r,
			'userAgent' => Yii::app()->request->userAgent,
			'ip' => Yii::app()->request->userHostAddress,
			'session' => Yii::app()->session->id,
			'time_cpu' => $performance['cpu_time'] * 1000,
			'time_exec' => $performance['exec_time'] * 1000,
			'ram' => $performance['ram'] * 1000,
			'created' => time(),
		));
		$this->response(array('success' => true));
	}

	public function response($data)
	{
		echo json_encode($data);
	}

}
