<?php

Yii::import('ext.components.actions.base.BaseAction');
Yii::import('ext.components.actions.base.interfaces.IModelAction');

class BaseModelAction extends BaseAction implements IModelAction
{
	// IModelInterface
	public $modelName;
	public $related = array();
	// params that will be assigned into view

	private $_model;

	public function runAction($params)
	{
		parent::runAction($params);
		
		$controller = $this->controller;
		
		if (is_null($this->modelName))
		{
			if (!is_null($controller->modelName))
			{
				$this->modelName = $controller->modelName;
			}
			else
			{
				$classname = get_class(Yii::app()->controller);

				$modelName = str_replace('Controller', '', $classname);

				$this->modelName = $modelName;
			}
		}
	}
	
	public function redirect()
	{
		if ($this->redirectUrl !== false && !Yii::app()->request->isAjaxRequest)
		{
			$model = $this->getModel();
			$controller = $this->controller;

			if (isset($_POST['returnUrl']))
			{
				$url = $_POST['returnUrl'];
			}
			elseif (is_string($this->redirectUrl))
			{
				if (strstr('"', $this->redirectUrl) === false)
				{
					$url = $this->evaluateExpression($this->redirectUrl,array(
						'model'=>$model,
						'controller'=>$controller,
						'action'=>$this,
					));
				}
				else
				{
					$url = $this->redirectUrl;
				}
				
			}
			elseif (is_array($this->redirectUrl))
			{
				$url = $this->redirectUrl;
			}
			elseif (is_null($this->redirectUrl))
			{
				$url = Yii::app()->user->returnUrl;
			}
			
			$controller->redirect($url);
		}
	}

	public function getModel()
	{
		return $this->_model;
	}
	public function setModel($model)
	{
		$this->_model = $model;
	}

	public function onAfterModelHandle($event)
	{
		$this->raiseEvent('onAfterModelHandle', $event);
	}

	public function onBeforeModelHandle($event)
	{
		$this->raiseEvent('onBeforeModelHandle', $event);
	}
}