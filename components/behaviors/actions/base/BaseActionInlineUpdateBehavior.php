<?php /**
* 
*/
Yii::import('ext.components.behaviors.actions.BaseModelActionBehavior');

class BaseActionInlineUpdateBehavior extends BaseModelActionBehavior
{
	
	public function events()
	{
		return array_merge(parent::events(),array(
			'onSetInlineModel'=>'setInlineModel',
			'onAfterInlineModelSave'=>'afterInlineModelSave',
			'onBeforeInlineModelSave'=>'beforeInlineModelSave',
		));
	}

	public function setInlineModel($event){}
	public function afterInlineModelSave($event){}
	public function beforeInlineModelSave($event){}

} ?>