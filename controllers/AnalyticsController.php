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

	/**
	 * @param string $range Диапазон дат
	 * @param string $zoom Размер одного деления - month|day|hour|minute
	 * @return array
	 */
	public function normalizeDates($range, $zoom) {
		if(!in_array($zoom, array('month', 'day', 'hour', 'minute')))
			$zoom = null;
		list($fromDate, $toDate) = explode(' - ', $range);
		if(!strtotime($fromDate))
			$fromDate = null;
		if(!strtotime($toDate))
			$toDate = null;
		return array($fromDate, $toDate, $zoom);
	}

	public function actionGraph($dataSources = null, $range = null, $zoom = null) {
		$dataSources = explode(',', $dataSources);
		list($fromDate, $toDate, $zoom) = $this->normalizeDates($range, $zoom);
		$graphData = $this->getData($dataSources, $fromDate, $toDate, $zoom);
		$this->render($this->action->id, compact('graphData'));
	}

	public function getData($dataSources, $fromDate, $toDate, $zoom) {
		$graphsData = array();
		foreach($dataSources as $dataSource) {
			if($dataSource)
				$graphsData = CMap::mergeArray($graphsData, $this->getGraphDataFromSource($dataSource, $fromDate, $toDate, $zoom));
		}
		return $graphsData;
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
		} else {
			throw new CException("Data source '{$dataSource}' not found");
		}
	}

}
