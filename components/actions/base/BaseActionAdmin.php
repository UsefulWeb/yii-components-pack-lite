<?php
Yii::import('ext.components.actions.base.BaseModelAction');

class BaseActionAdmin extends BaseModelAction
{
	public $loadParams = array('unsetAttributes'=>true,'scenario'=>'search');

	public function runAction($params){
		
		parent::runAction($params);

		$id = Yii::app()->request->getQuery('id',null);

		$controller = $this->controller;

		$model = $controller->loadModel($id,$this->modelName,$this->loadParams);
		
		if(isset($_GET[$this->modelName]))
			$model->attributes=$_GET[$this->modelName];

		$this->setModel($model);
		
		$this->setViewParams(array(
			'model'=>$model,
		));
	}
}