<div class="row date-range-picker">
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
	<?= CHtml::label("Detalization:", "zoomSelect") ?>
	<?=
	CHtml::dropDownList('zoom', $zoom, array(
		'minute' => 'Minute',
		'hour' => 'Hour',
		'day' => 'Day',
		'month' => 'Month',
	)); ?>
	<?= CHtml::submitButton() ?>
	<?= CHtml::endForm() ?>
</div>
<?php
foreach ($graphData as $graph) {
	$type = $graph['series'][0]['type'];
	$typeConfig = __DIR__ . '/graphs/' . $type . '.php';
	if (!file_exists($typeConfig)) {
		echo "Config for {$type} doesn't exist";
		break;
	}
	$config = CMap::mergeArray(require($typeConfig), $graph);
	$this->widget('ext.highcharts.HighstockWidget', array(
		'options' => $config,
		'setupOptions' => array(
			'global' => array(
				'useUTC' => false,
			),
		)
	));
}
