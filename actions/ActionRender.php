<?php
Yii::import('ext.components.actions.base.BaseAction');

class ActionRender extends BaseAction
{
	public $processOutput = true;

	public function run()
	{
		$this->render();
	}
}