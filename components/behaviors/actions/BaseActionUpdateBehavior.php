<?php /**
* 
*/
Yii::import('ext.components.behaviors.actions.BaseActionBehavior');

class BaseActionUpdateBehavior extends BaseActionBehavior
{
	
	public function events()
	{
		return array_merge(parent::events(),array(
			'onAfterModelSave'=>'afterModelSave',
			'onBeforeModelSave'=>'beforeModelSave',
		));
	}

	public function afterModelSave($event){}
	public function beforeModelSave($event){}

} ?>