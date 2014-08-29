<div class="row date-range-picker">
	<?=CHtml::beginForm(array('/' . $this->module->id . '/analytics/graph', 'dataSources' => implode(',', $dataSources), 'zoom' => $zoom), 'get'); ?>
	<label for="dateRange">Выберите период: </label>
	<?php $this->widget('ext.RDateRangePicker.RDateRangePicker', array(
		'name' => 'range',
		'value' => $_GET['range'],
	)); ?>
	<?=CHtml::endForm() ?>
</div>
<?php
foreach($graphData as $graph) {
	$type = $graph['series'][0]['type'];
	$typeConfig = __DIR__ . '/graphs/' . $type . '.php';
	if(!file_exists($typeConfig)) {
		echo "Config for {$type} doesn't exist";
		break;
	}
	$config = CMap::mergeArray(require($typeConfig), $graph);
	$this->widget('ext.highcharts.HighstockWidget', array(
		'options' => $config,
	));
}
