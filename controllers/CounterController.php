<?php

/**
 * Class CounterController
 * Контроллер, на который приходит запрос с сайта от JS скрипта
 */
class CounterController extends RController
{

	public function actionIndex($id, $name = null, $referrer = null)
	{
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(404);
		$data = Yii::app()->{Yii::app()->analytics->cacheId}->get('analytics:' . $id);
		$result = AnalyticsHelper::incrementLog(array(
			'url' => Yii::app()->request->urlReferrer,
			'name' => $name,
			'referrer' => $referrer,
			'userAgent' => Yii::app()->request->userAgent,
			'ip' => Yii::app()->request->userHostAddress,
			'session' => Yii::app()->session->getSessionID(),
			'time_cpu' => $data['cpu'] * 1000,
			'time_exec' => $data['time'] * 1000,
			'ram' => $data['ram'] * 1000,
			'created' => time(),
		));
		$this->response(array('success' => $result));
	}

	public function response($data)
	{
		echo json_encode($data);
	}

}
