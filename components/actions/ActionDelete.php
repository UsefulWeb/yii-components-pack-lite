<?php
Yii::import('ext.components.actions.base.BaseActionDelete');

class ActionDelete extends BaseActionDelete
{
	public $redirectUrl = array('index');
	public $processOutput = true;

	public function run(){
		
		if (Yii::app()->request->isAjaxRequest || Yii::app()->request->requestType == 'DELETE')
		{
			$this->render();
		}
		
		if(!isset($_GET['ajax']))
			$this->redirect();
	}
}