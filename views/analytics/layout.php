<?php
/**
 * @var $this AnalyticsController
 * @var $content string
 */
$this->beginContent($this->parentLayout);
$assetsUrl = Yii::app()->assetManager->publish(YiiBase::getPathOfAlias('analytics.assets'), false, -1, YII_DEBUG);
Yii::app()->clientScript->registerScriptFile($assetsUrl . '/analytics.js');
?>

<?php
echo $content;
?>
<?php
$this->endContent();