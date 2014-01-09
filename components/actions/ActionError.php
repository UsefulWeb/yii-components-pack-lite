<?php

Yii::import('ext.components.actions.base.BaseAction');

class ActionError extends BaseAction
{
	public $processOutput = true;
	public function run()
	{
		$controller = $this->controller;
		
		if($error=Yii::app()->errorHandler->error)
		{
			$this->setViewParams($error);

			$this->render();
		}
	}
}