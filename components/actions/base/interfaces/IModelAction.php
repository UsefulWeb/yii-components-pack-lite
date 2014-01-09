<?php /**
* 
*/
interface IModelAction
{
	public function getModel();
	public function setModel($model);
	public function onAfterModelHandle($model);
	public function onBeforeModelHandle($model);
} 
?>