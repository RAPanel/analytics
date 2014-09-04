<div class="row date-range-picker" id="graphForm">
	<?= CHtml::beginForm(array('/' . $this->module->id . '/analytics/graph', 'dataSources' => implode(',', $dataSources)), 'get'); ?>
	<label for="dateRange">Выберите период: </label>
	<?= CHtml::button('Today', array('onclick' => "$('#range').val('" . date('d.m.Y') . " - " . date('d.m.Y') . "');")) ?>
	<?= CHtml::button('Last Day', array('onclick' => "$('#range').val('" . date('d.m.Y', time() - 3600 * 24) . " - " . date('d.m.Y', time() - 3600 * 24) . "');")) ?>
	<?= CHtml::button('Last Week', array('onclick' => "$('#range').val('" . date('d.m.Y', time() - 3600 * 24 * 7) . " - " . date('d.m.Y') . "');")) ?>
	<?= CHtml::button('Last Month', array('onclick' => "$('#range').val('" . date('d.m.Y', time() - 3600 * 24 * 30) . " - " . date('d.m.Y') . "');")) ?>
	<?= CHtml::button('Last Year', array('onclick' => "$('#range').val('" . date('d.m.Y', time() - 3600 * 24 * 365) . " - " . date('d.m.Y') . "');")) ?>
	<?php $this->widget('ext.RDateRangePicker.RDateRangePicker', array(
		'name' => 'range',
		'value' => $_GET['range'],
	)); ?>
	<?= CHtml::label("Детализация:", "zoomSelect") ?>
	<?=
	CHtml::dropDownList('zoom', $zoom, array(
		'minute' => 'Минута',
		'hour' => 'Час',
		'day' => 'День',
		'month' => 'Месяц',
	)); ?>
	<?= CHtml::hiddenField('graphZoom', $graphZoom); ?>
	<?= CHtml::submitButton() ?>
	<?= CHtml::endForm() ?>
</div>
<div id="graphs">
<?php
foreach ($graphData as $graphId => $graph) {
	$type = $graph['series'][0]['type'];
	$typeConfig = __DIR__ . '/graphs/' . $type . '.php';
	if (!file_exists($typeConfig)) {
		echo "Config for {$type} doesn't exist";
		break;
	}
	$config = CMap::mergeArray(require($typeConfig), $graph);
	$this->widget('ext.highcharts.HighstockWidget', array(
		'id' => $graphId,
		'options' => $config,
		'setupOptions' => array(
			'global' => array(
				'useUTC' => false,
			),
		)
	));
}
?>
</div>