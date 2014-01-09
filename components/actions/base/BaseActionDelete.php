<?php
Yii::import('ext.components.actions.base.BaseModelAction');

class BaseActionDelete extends BaseModelAction
{
	public function runAction($params){

		parent::runAction($params);

		$id = Yii::app()->request->getQuery('id',null);

		$controller = $this->controller;
		
		$model = $controller->loadModel($id,$this->modelName);

		$this->setModel($model);
		
		if ($this->dataType == 'json')
		{
			$this->params = array(
				'model' => $this->prepareJSONAttributes($model)
			);
		}
		
		$model->delete();
	}
}