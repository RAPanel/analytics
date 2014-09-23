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
        $data = Yii::app()->cacheFast->get('A:' . $id);
        $result = AnalyticsHelper::incrementLog(array(
            'url' => $data['url'] ? $data['url'] : Yii::app()->request->requestUri,
            'name' => $data['name'] ? $data['name'] : $name,
            'referrer' => $data['referrer']?$data['referrer']:$referrer,
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
