<?php
Yii::import('ext.components.actions.base.BaseActionView');

class ActionModelRedirect extends BaseActionView
{
	public function run()
	{
		$this->redirect();
	}
}