<?php

class ActionLogout extends CAction
{

	public function run()
	{
		Yii::app()->user->logout();
		$this->controller->redirect(Yii::app()->homeUrl);
	}
}