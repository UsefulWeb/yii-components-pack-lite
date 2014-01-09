<?php 

Yii::import('ext.components.actions.base.BaseActionInlineUpdate');

class BaseActionUpdate extends BaseActionInlineUpdate
{
	public $checkRelated = array();
	public $decodeAttributes = array();
	public $enableUrlAttributes = false;
	public $loadParams = array();
	public $inputMethod = 'POST';
	public $createFromGlobals = false;

	private $_relatedData = array();
	private $_data = array();

	public function runAction($params)
	{
		parent::runAction($params);

		$id = Yii::app()->request->getQuery('id',null);
		
		$controller = $this->controller;
		
		$this->hanndleInput();
		
		$this->onBeforeModelHandle(new CEvent($this));

		$model = $controller->loadModel($id,$this->modelName,$this->loadParams);

		$this->setModel($model);

		$this->onAfterModelHandle(new CEvent($this));
	}

	protected function hanndleInput()
	{
		switch ($this->inputMethod)
		{
			case 'POST': 
				$this->_data = $_POST;
			break;
			case 'GET': 
				$this->_data = $_GET;
			break;
			case 'input':
				$this->_data = CJSON::decode(file_get_contents("php://input"));
			break;
		}
	}

	public function onAfterModelHandle($event)
	{
		parent::onAfterModelHandle($event);

		$this->processUrlAttributes();
		$this->prepareAttributes();
		$this->saveModel();
		$this->prepareViewParams();
	}

	protected function processUrlAttributes()
	{
		if ($this->enableUrlAttributes)
		{
			$model = $this->getModel();
			foreach ($model->attributeNames() as $attribute) 
			{
				if (isset($_GET[$attribute]))
				{
					$model->$attribute = $_GET[$attribute];
				}
			}
			$this->setModel($model);
		}
		
	}
	protected function prepareAttributes()
	{
		$params = array();
		$relatedData = array();
		$model = $this->model;

		$relations = $model->relations();
		
		foreach ($this->related as $key=>$relation) 
		{
			$relatedObject = $model->{$relation};
			$relationModelName = $relations[$relation][1];
			if (is_null($relatedObject))
			{
				$relatedObject = new $relationModelName;
			}

			if (isset($this->_data[$relation]))
			{
				
				if ($relations[$relation][0] == CActiveRecord::HAS_ONE)
				{
					$relatedObject->attributes = $this->_data[$relation];
					$relatedData[$relation] = $relatedObject;
				}
				else 
				{
					$relatedData[$relation] = $this->_data[$relation];
				}

				$params[$relation] = $relatedObject;
			}
			else if(isset($this->_data[$relationModelName]))
			{
				$relatedObject->attributes = $this->_data[$relationModelName];

				$relatedData[$relation] = $this->_data[$relationModelName];
				$relatedData[$relation] = $relations[$relation][0] == CActiveRecord::HAS_ONE ? $relatedObject : $this->_data[$relationModelName];
				$params[$relation] = $relatedObject;
			}
			else
			{
				$params[$relation] = $relatedObject;
			}
		}

		$this->setViewParams($params);
		$this->_relatedData = $relatedData;
	}
	protected function saveModel()
	{
		$controller = $this->controller;
		$model = $this->getModel();
		$relatedData = $this->_relatedData;

		if(isset($this->_data[$this->modelName]) || count($this->_data)&&$this->createFromGlobals) 
		{

			$model->attributes = $this->createFromGlobals ? $this->_data : $this->_data[$this->modelName];
			
			if (count($this->decodeAttributes))
			{
				foreach ($this->decodeAttributes as $attribute) 
				{
					$model->$attribute = urldecode($model->$attribute);
				}
			}
			$checkRelated = true;
			if (count($this->checkRelated))
			{
				foreach ($this->checkRelated as $related) 
				{
					if (!$relatedData[$related]->validate())
					{
						$checkRelated = false;
						break;
					}
				}
			}

			if ($checkRelated)
			{
				$this->onBeforeModelSave(new CEvent($this));

				if($model->saveWithRelated($relatedData))
				{
					$this->onAfterModelSave(new CEvent($this));
				}
				else
				{
					Yii::log(print_r($model->errors,true),'trace');
					$this->setViewParams(array_merge($this->getViewParams(),array(
						'errors' => $model->errors,
					)));
				}
			}
				
		}
		$this->setModel($model);
	}
	protected function prepareViewParams()
	{
		$model = $this->model;
		
		$params = $this->getViewParams();
		$params['model'] = $model;

		$this->setViewParams($params);
	}
	public function onAfterModelSave($event)
	{
		$this->raiseEvent('onAfterModelSave', $event);
	}

	public function onBeforeModelSave($event)
	{
		$this->raiseEvent('onBeforeModelSave', $event);
	} 
} 
?>