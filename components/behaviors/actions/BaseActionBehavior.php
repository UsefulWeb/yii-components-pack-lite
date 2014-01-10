<?php

class BaseActionBehavior extends CBehavior
{
	
	public function events()
	{
		return array_merge(parent::events(),array(
			'onAfterInit'=>'afterInit',
		));
	}

	public function afterInit($event){}

} ?>