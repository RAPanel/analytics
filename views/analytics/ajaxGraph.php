<?php
$outputData = array();
foreach ($graphData as $graphId => $graph) {
	foreach($graph['series'] as $serieId => $series) {
		$outputData[$graphId][$serieId] = $series['data'];
	}
}
echo json_encode($outputData);