<?php

Yii::import('ext.components.actions.base.BaseActionView');

class ActionView extends BaseActionView
{
	public $processOutput = true;

	public function run()
	{
		$this->render();
		
	}

}