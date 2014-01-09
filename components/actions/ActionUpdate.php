<?php

Yii::import('ext.components.actions.base.BaseActionUpdate');

class ActionUpdate extends BaseActionUpdate
{
	public $redirectUrl = 'array("view","id"=>$model->id)';
	public $processOutput = true;

	public function run()
	{

		$this->render();
	}
	
	public function onAfterModelSave($event)
	{
		parent::onAfterModelSave($event);

		$this->redirect();
	}
}