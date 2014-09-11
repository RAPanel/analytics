<div class="row statistic-page-info">
	<div class="url"><?= CHtml::link($data['name'], $data['name'], array('target' => '_blank')) ?></div>
	<div class="type"><?=$data['type']; ?></div>
	<div class="stats">
		<span class="hits">Показы: <?= $data['hits'] ?></span>
		<span class="visits">Визиты: <?= $data['visits'] ?></span>
		<span class="visitors">Посетители: <?= $data['visitors'] ?></span>
		<span class="enters">Входы: <?= $data['enters'] ?></span>
		<span class="exits">Выходы: <?= $data['exits'] ?></span>
	</div>
</div>