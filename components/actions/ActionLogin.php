<?php

Yii::import('ext.components.actions.base.BaseModelAction');

class ActionLogin extends BaseModelAction
{
	public $formId;
	public $processOutput = true;
	public $modelName = 'LoginForm';

	public function run()
	{
		$controller =  $this->controller;
		
		$model=new $this->modelName;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']===$this->formId)
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST[$this->modelName]))
		{
			$model->attributes=$_POST[$this->modelName];

			if($model->validate() && $model->login() )
			{
				$this->setViewParams(array(
					'success'=>true,
				));

				$this->onAfterLogin(new CEvent($this));

				if (Yii::app()->request->isAjaxRequest)
				{
					$this->render();
				}
				else
				{
					$this->redirect();
				}
			}
			else
			{
				$this->setViewParams(array(
					'success'=>false,
				));

				if (Yii::app()->request->isAjaxRequest)
				{
					$this->render();
				}	
			}
		}
		$this->setModel($model);
		
		$this->setViewParams(array(
			'model'=>$model,
		));
		
		$this->render();
	}

	public function onAfterLogin($event)
	{
		$this->raiseEvent('onAfterLogin', $event);
	}
}