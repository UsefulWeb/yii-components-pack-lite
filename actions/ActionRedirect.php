<?php
Yii::import('ext.components.actions.base.BaseAction');

class ActionRedirect extends BaseAction
{
	public function run()
	{
		$this->redirect();
	}
}