<?php 

Yii::import('ext.components.behaviors.actions.base.BaseModelActionBehavior');
 class ActionLoginBehavior extends BaseModelActionBehavior
 {
 	
 	public function events()
 	{
 		return array_merge(parent::events(),array(
			'onAfterLogin'=>'afterLogin',
		));
 	}

	public function afterLogin($event){}
	
 } 
?>