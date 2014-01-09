<?php /**
* 
*/
Yii::import('ext.components.actions.base.BaseModelAction');

class BaseActionIndex extends BaseModelAction
{
	public $config = array();
	public $enableUrlAttributes = false;
	public $allowedUrlScopes = array();

	function runAction($params)
	{
		parent::runAction($params);

		$id = Yii::app()->request->getQuery('id',null);
		
		$controller = $this->controller;

		if ($this->enableUrlAttributes) 
		{
			$instance = new $this->modelName;
			
			foreach ($instance->attributeNames() as $attribute) 
			{
				if (isset($_GET[$attribute]))
				{
					$this->config['criteria']['scopes']['_'.$attribute] = $_GET[$attribute];
				}
			}
		}

		foreach ($this->allowedUrlScopes as $scopeName) 
		{
			if (isset($_GET[$scopeName]))
			{
				$value = is_array($_GET[$scopeName]) ? array($_GET[$scopeName]) : $_GET[$scopeName];
				$this->config['criteria']['scopes']['_'.$scopeName] = $value;
			}
		}
		$this->onBeforeModelHandle(new CEvent($this));
		
		$model = $controller->loadModel($id,$this->modelName,$this->config);
		$model->unsetAttributes();
		
		$this->setModel($model);
		$this->onAfterModelHandle(new CEvent($this));
		
		$viewParams = array();

		if ($this->dataType == 'html')
		{
			$dataProvider = count($this->config)? new CActiveDataProvider($this->modelName,$this->config) :new CActiveDataProvider($this->modelName);
			
			$viewParams = array(
				'dataProvider'=>$dataProvider,
				'model'=>$model,
			);
		}
		if ($this->dataType == 'json')
		{
			if (isset($_GET[$this->modelName.'_part']))
			{
				if (!isset($this->config['criteria']))
				{
					$this->config['criteria'] = array();
				}
				$this->config['criteria']['offset'] = $_GET[$this->modelName.'_part']*$_GET[$this->modelName.'_size'];
				$this->config['criteria']['limit'] = $_GET[$this->modelName.'_size'];
			}

			$items = count($this->config) ? $model->findAll($this->config['criteria']) : $model->findAll();

			$viewParams = array(
				'items'=>$items,
			);
		}
		
		$this->setViewParams($viewParams);
	}
} 
?>