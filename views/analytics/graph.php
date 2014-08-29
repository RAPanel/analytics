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
