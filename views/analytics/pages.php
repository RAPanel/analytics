<div class="row date-range-picker" id="graphForm">
	<?= CHtml::beginForm(array('/' . $this->module->id . '/analytics/pages'), 'get'); ?>
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
	<?= CHtml::submitButton() ?>
	<?= CHtml::endForm() ?>
</div>
<div id="pages">
	<?php $this->widget('zii.widgets.grid.CGridView', array(
		'id' => 'pages-grid',
		'dataProvider' => $dataProvider,
		'columns' => array(
			'id' => array(
				'header' => 'ID',
				'name' => 'id',
				'htmlOptions' => array(
					'class' => 'id'
				),
			),
			'details' => array(
				'type' => 'raw',
				'header' => 'asdasd',
				'value' => function($data) use ($range) {
						return CHtml::link('', array('analytics/page', 'id' => $data['id'], 'range' => $range), array('class' => 'details-link', 'onclick' => 'modalIFrame(this);return false;'));
					},
				'htmlOptions' => array(
					'class' => 'details'
				),
			),
			'url' => array(
				'header' => 'URL',
				'name' => 'url',
				'type' => 'raw',
				'value' => function ($data) {
						$text = $data['url'];
						if(mb_strlen($text) > 300)
							$text = substr($text, 0, 297) . '...';
						return CHtml::link($text, $data['url'], array('class' => 'followLink', 'target' => '_blank'));
					},
				'htmlOptions' => array(
					'class' => 'url'
				),
			),
			'hits' => array(
				'header' => 'Просмотры',
				'name' => 'hits',
				'htmlOptions' => array(
					'class' => 'number'
				),
			),
			'visits' => array(
				'header' => 'Визиты',
				'name' => 'visits',
				'htmlOptions' => array(
					'class' => 'number'
				),
			),
			'visitors' => array(
				'header' => 'Посетители',
				'name' => 'visitors',
				'htmlOptions' => array(
					'class' => 'number'
				),
			),
			'enters' => array(
				'header' => 'Входы',
				'name' => 'enters',
				'htmlOptions' => array(
					'class' => 'number'
				),
			),
			'exits' => array(
				'header' => 'Выходы',
				'name' => 'exits',
				'htmlOptions' => array(
					'class' => 'number'
				),
			),
		),
	)); ?>
</div>