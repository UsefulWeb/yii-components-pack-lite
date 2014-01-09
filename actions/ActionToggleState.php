<?php /**
* 
*/
class ActionToggleState extends CAction
{
	
	function run($state)
	{
		Yii::app()->user->setState($state,!Yii::app()->user->getState($state));
		$url = Yii::app()->user->getRefererUrl();
		
		$this->controller->redirect($url);
	}
	

} ?>