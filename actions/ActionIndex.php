<?php
Yii::import('ext.components.actions.base.BaseActionIndex');

class ActionIndex extends BaseActionIndex
{
	public $processOutput = true;
	
	public function run()
	{
		$this->render();
	}
}