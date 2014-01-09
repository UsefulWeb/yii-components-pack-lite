<?php

Yii::import('ext.components.actions.base.BaseModelAction');

class BaseActionView extends BaseModelAction
{
	public $criteria;

	public function runAction($params){

		parent::runAction($params);

		$id = Yii::app()->request->getQuery('id',null);

		$controller = $this->controller;

		$this->onBeforeModelHandle(new CEvent($this));

		if (is_null($this->criteria))
		{
			$model = $controller->loadModel($id,$this->modelName);
		}
		else
		{
			$model = new $this->modelName;
			$model = $model->find($this->criteria);
			if (is_null($model))
			{
				throw new CHttpException(404, Yii::t('giix', 'The requested page does not exist.'));
			}
		}

		$this->setModel($model);
		
		$this->onAfterModelHandle(new CEvent($this));

		$viewParams = array('model'=>$model);

		foreach ($this->related as $key=>$relation) 
		{
			$relatedObject = $model->{$relation};
		
			$viewParams[$relation] = $relatedObject;
		}

		$this->setViewParams($viewParams);
	}

}