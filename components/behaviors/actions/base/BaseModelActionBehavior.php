<?php /**
* 
*/
Yii::import('ext.components.behaviors.actions.base.BaseActionBehavior');

class BaseModelActionBehavior extends BaseActionBehavior
{
	
	public function events()
	{
		return array_merge(parent::events(),array(
			'onAfterModelHandle'=>'afterModelHandle',
			'onBeforeModelHandle'=>'beforeModelHandle',
		));
	}

	public function afterModelHandle($event){}
	public function beforeModelHandle($event){}

} ?>