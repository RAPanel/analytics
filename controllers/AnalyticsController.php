<?php
YiiBase::import('application.modules.rapanel.components.RAdminController');
/**
 * Class AnalyticsController
 * Контроллер для отображения данных, требуется авторизация
 */
class AnalyticsController extends RAdminController {

	public $parentLayout;

	public function render($view, $data = null, $return = false, $ignoreAjax = false)
	{
		$this->parentLayout = $this->layout;
		if (isset($_GET['iframe']))
			$this->layout = "iframe";
		else
			$this->layout = 'layout';
		return parent::render($view, $data, $return, $ignoreAjax);
	}

	public function getViewPath()
	{
		return dirname(dirname(__FILE__)) . '/views/analytics';
	}

	public function getLayoutFile($layoutName)
	{
		if ($layoutName == 'layout')
			return dirname(dirname(__FILE__)) . '/views/analytics/layout.php';
		else {
			return parent::getLayoutFile($layoutName);
		}
	}

	/**
	 * @param string $range Диапазон дат
	 * @param string $zoom Размер одного деления - month|day|hour|minute
	 * @return array
	 */
	public function normalizeParams($range = null, $zoom = null) {
		if(!in_array($zoom, array('month', 'day', 'hour', 'minute')))
			$zoom = null;
		list($fromDate, $toDate) = explode(' - ', $range);
		if(!strtotime($fromDate))
			$fromDate = null;
		if(!strtotime($toDate))
			$toDate = null;
		return array($fromDate, $toDate, $zoom);
	}

	public function actionGraph($dataSources = null, $range = null, $zoom = null, $graphZoom = null) {
		$dataSources = explode(',', $dataSources);
		list($fromDate, $toDate, $zoom) = $this->normalizeParams($range, $zoom);
		$graphData = $this->getData($dataSources, $fromDate, $toDate, $zoom, $graphZoom);
		if(Yii::app()->request->isAjaxRequest)
			$this->render('ajaxGraph', compact('graphData', 'dataSources', 'zoom', 'graphZoom'));
		else
			$this->render($this->action->id, compact('graphData', 'dataSources', 'zoom', 'graphZoom'));
	}

	public function getData($dataSources, $fromDate, $toDate, $zoom, $graphZoom) {
		$graphData = array();
		foreach($dataSources as $dataSource) {
			if($dataSource)
				$graphData = CMap::mergeArray($graphData, $this->getGraphDataFromSource($dataSource, $fromDate, $toDate, $zoom, $graphZoom));
		}
		$graphData['rangeSelector'] = $this->getGraphZoomConfig($zoom, $graphZoom);
		$i = 0;
		$axisMap = array();
		foreach($graphData['yAxis'] as $axisId => $axis) {
			if(!is_numeric($axisId)) {
				$axisMap[$axisId] = $i;
			}
			$i++;
		}
		$graphData['yAxis'] = array_values($graphData['yAxis']);
		foreach($graphData['series'] as $serieId => $serie) {
			if(isset($serie['yAxis']) && isset($axisMap[$serie['yAxis']])) {
				$graphData['series'][$serieId]['yAxis'] = $axisMap[$serie['yAxis']];
			}
		}
		return $graphData;
	}

	public function getGraphDataFromSource($dataSource, $fromDate, $toDate, $zoom) {
		$className = ucfirst($dataSource) . 'DataSource';
		$sourceFile = YiiBase::getPathOfAlias("analytics.dataSources.{$className}") . '.php';
		if(file_exists($sourceFile)) {
			require_once($sourceFile);
			if(class_exists($className, false)) {
				/** @var AnalyticsDataSource $source */
				$source = new $className;
				return $source->getGraphData($fromDate, $toDate, $zoom);
			}
			return array();
		} else {
			throw new CException("Data source '{$dataSource}' not found");
		}
	}

	public function getGraphZoomConfig($dataZoom, $graphZoom) {
		$config = array(
			'selected' => 0,
			'buttons' => array(
				array(
					'type' => 'hour',
					'count' => 1,
					'text' => '1h',
				),
				array(
					'type' => 'day',
					'count' => 1,
					'text' => '1d',
				),
				array(
					'type' => 'day',
					'count' => 7,
					'text' => '7d',
				),
				array(
					'type' => 'month',
					'count' => 1,
					'text' => '1m',
				),
				array(
					'type' => 'month',
					'count' => 3,
					'text' => '3m',
				),
				array(
					'type' => 'month',
					'count' => 6,
					'text' => '6m',
				),
				array(
					'type' => 'year',
					'count' => 1,
					'text' => '1y',
				),
				array(
					'type' => 'all',
					'text' => 'all',
				),
			),
		);
		switch($dataZoom) {
			case 'minute':
				$minGraphZoom = 0;
				break;
			case 'hour':
				$minGraphZoom = 1;
				break;
			case 'day':
				$minGraphZoom = 2;
				break;
			case 'month':
				$minGraphZoom = 3;
				break;
		}
		$buttons = array();
		$selected = 0;
		foreach($config['buttons'] as $id => $button) {
			if($id >= $minGraphZoom) {
				$buttons[] = $button;
				if($button['text'] == $graphZoom)
					$selected = count($buttons) - 1;
			}
		}
		$config['buttons'] = $buttons;
		$config['selected'] = $selected;
		return $config;
	}

	public function actionPages($range = null) {
		/** @var CDbCommand $command */
		Yii::app()->db->createCommand("SET SQL_BIG_SELECTS = 1")->execute();
		$command = AnalyticsHelper::statsPages(array('hits', 'enters', 'visits', 'visitors', 'exits'));
		list($fromDate, $toDate) = $this->normalizeParams($range);
		if($fromDate === null || $toDate === null) {
			$rangeCondition = '';
		} else {
			$rangeCondition = 'AND created BETWEEN \'' . date('Y-m-d', strtotime($fromDate)) . '\' AND \'' . date('Y-m-d', strtotime($toDate)) . '\'';
		}
		$command->join = str_replace("{rangeCondition}", $rangeCondition, $command->join);
		$countCommand = "SELECT COUNT(a.id) FROM (".$command->getText().") a";
		$totalItemCount = Yii::app()->db->createCommand($countCommand)->queryScalar($command->params);
		$dataProvider = new CSqlDataProvider($command, array(
			'totalItemCount' => $totalItemCount,
			'pagination' => array(
				'pageSize' => 50,
			)
		));
		$this->render('pages', compact('range', 'dataProvider'));
	}

	public function actionPage($id, $range = null, $zoom = null) {
		$command = AnalyticsHelper::statsPage($id, $range, $zoom);
		list($fromDate, $toDate) = $this->normalizeParams($range);
		if($fromDate === null || $toDate === null) {
			$rangeCondition = '';
		} else {
			$rangeCondition = 'AND created BETWEEN \'' . date('Y-m-d', strtotime($fromDate)) . '\' AND \'' . date('Y-m-d', strtotime($toDate)) . '\'';
		}
		$command->join = str_replace("{rangeCondition}", $rangeCondition, $command->join);
		$pageData = $command->queryRow();
		$this->render('page', array('data' => $pageData));
	}
}
