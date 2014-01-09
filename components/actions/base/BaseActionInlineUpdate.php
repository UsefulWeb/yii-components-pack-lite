<?php 

Yii::import('ext.components.actions.base.BaseModelAction');

class BaseActionInlineUpdate extends BaseModelAction
{
	public $allowedInlineModels;
	public $massUpdate = false;
	public $allowInlineModelCreate = false;
	
	private $_inlineModel;

	public function runAction($params)
	{
		parent::runAction($params);
		
		if($this->saveInlineData() !== null)
		{
			$this->render();
			Yii::app()->end();
		}
	}

	protected function saveInlineData($defaultModel=null)
	{
		if (!isset($_POST['primaryKey'])&&!$this->massUpdate)
		{
			return;
		}
		if (is_null($this->allowedInlineModels))
		{
			$models = array($this->modelName);
		}
		else
		{
			$models = $this->allowedInlineModels;
		}

		foreach ($models as $modelName) 
		{
			if (isset($_POST[$modelName]))
			{
				if ($this->massUpdate) 
				{
					return $this->saveMultipleInlineData($modelName,$_POST[$modelName],$defaultModel);
				}
				else
				{
					return $this->saveSingleInlineData($modelName,$_POST[$modelName],$defaultModel);
				}
			}
		}
	}
	protected function saveMultipleInlineData($modelName,$data,$defaultModel = null)
	{
		$params = $this->getViewParams();

		$inlineSaveresult = false;

		foreach ($data as $key => $item) 
		{
			$result = $this->saveSingleInlineData($modelName,$item,$defaultModel);
			$model = $this->getInlineModel();
			if ($result) 
			{
				$inlineSaveresult = true;

				if (!is_null($model))
				{
					$params[$modelName][$key] = $model->primaryKey;
				}
				else
				{
					$params[$modelName][$key] = true;
				}
			}
			else
			{
				if (!is_null($model))
				{
					$params[$key] = $model->primaryKey;
				}
				else
				{
					$params[$modelName][$key] = false;
				}
			}
		}

		$this->setViewParams($params);

		return $inlineSaveresult;
	}
	protected function saveSingleInlineData($modelName,$data,$defaultModel = null)
	{
		if ($modelName === $this->modelName&&!is_null($defaultModel))
		{
			$model = $defaultModel;
		}
		else if (isset($data['id']))
		{
			$model = new $modelName;
			$model = $model->findByPk($data['id']);
		}
		else if (isset($_POST['primaryKey']))
		{
			$model = new $modelName;
			$model = $model->findByPk($_POST['primaryKey']);
		}

		if ((!isset($model)||isset($data[$model->primaryKey()]) )&&!$this->allowInlineModelCreate)
		{
			return;
		}
		else if (!isset($model)&&$this->allowInlineModelCreate)
		{
			$model = new $modelName;
		}


		$related = array();

		foreach ($this->related as $relationName) 
		{
			if (isset($data[$relationName]))
			{
				$related[$relationName] = $data[$relationName];
			}
		}

		$model->attributes = $data;

		$this->setInlineModel($model);
		
		$this->onBeforeInlineModelSave(new CEvent($this));
		
		$result = $model->saveWithRelated($related);

		if ($result)
		{
			$this->onAfterInlineModelSave(new CEvent($this));
		}
		return $result;
	}
	protected function setInlineModel($model)
	{
		$this->_inlineModel = $model;

		$this->onSetInlineModel(new CEvent($this));
	} 

	public function getInlineModel()
	{
		return $this->_inlineModel;
	} 

	public function onSetInlineModel($event)
	{
		$this->raiseEvent('onSetInlineModel', $event);
	}

	public function onAfterInlineModelSave($event)
	{
		$this->raiseEvent('onAfterInlineModelSave', $event);
	}

	public function onBeforeInlineModelSave($event)
	{
		$this->raiseEvent('onBeforeInlineModelSave', $event);
	} 

} 
?>