<?php

class BaseActionBehavior extends CBehavior
{
	
	public function events()
	{
		return array_merge(parent::events(),array(
			'onAfterInit'=>'afterInit',
			'onBeforeRender'=>'beforeRender',
			'onAfterRender'=>'afterRender',
		));
	}

	public function afterInit($event){}
	public function beforeRender($event){}
	public function afterRender($event){}

} ?>