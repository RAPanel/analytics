<?php

/**
 * Class CounterController
 * Контроллер, на который приходит запрос с сайта от JS скрипта
 */
class CounterController extends RController
{

	public $domains = '*';

	public function actionIndex($id, $name = null, $referrer = null)
	{
		// Не проверять ajax - кроссдоменные запросы не отсылают нужный заголовок
		if(is_string($this->domains)) {
			$originAccess = $this->domains;
		} elseif(is_array($this->domains)) {
			$domains = array();
			foreach($this->domains as $domain) {
				$domains[] = "http://" . $domain;
			}
			$originAccess = implode(" ", $domains);
		}
		if(isset($originAccess))
			header("Access-Control-Allow-Origin: {$originAccess}");
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
