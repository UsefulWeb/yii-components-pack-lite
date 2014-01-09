<?php
Yii::import('ext.components.actions.base.BaseActionAdmin');

class ActionAdmin extends BaseActionAdmin
{
	public $processOutput = true;

	public function run()
	{
		$this->render();
	}
}