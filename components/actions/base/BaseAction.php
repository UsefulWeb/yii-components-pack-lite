<?php
Yii::import('ext.components.actions.base.interfaces.IRenderAction');

class BaseAction extends CAction implements IRenderAction
{	
	// IRenderAction
	public $processOutput;
	public $redirectUrl = false;

	public $dataType; // html | json
	public $jsonAttributes = array();
	public $crossDomain;

	// DO not show params with current names
	public $disallowedViewParamNames = array();

	public $layout;
	public $theme;
	public $debug = false;

	public $allowedMethods = array('GET','POST','PUT','DELETE');
	//  CACtion behaviors
	public $behaviors = array();

	public $params = array();
	public $ajaxView;

	// You can specify list of partials istead of view
	public $partialsQueue = array();

	private $_viewParams = array();
	private $_paramsParsed = false;

	public function runWithParams($params)
	{	
		$this->runAction($params);

		return parent::runWithParams($params);
	}

	public function runAction($params)
	{
		$controller = $this->controller;
		
		if (!is_null($this->layout))
		{
			$controller->layout = $this->layout;
		}	

		if (!is_null($this->theme))
		{
			Yii::app()->theme = $this->theme;
		}

		if (is_null($this->ajaxView))
		{
			$this->ajaxView = $this->id;
		}

		if (isset(Yii::app()->actionSettings))
		{
			$this->behaviors = array_merge(
				Yii::app()->actionSettings->actionBehaviors(),
				$this->behaviors
			);

			if (is_null($this->dataType))
			{
				$this->dataType = Yii::app()->actionSettings->dataType;
			}

			if (is_null($this->redirectUrl))
			{
				$this->redirectUrl = Yii::app()->actionSettings->redirectUrl;
			}
			
			if (is_null($this->processOutput))
			{
				$this->processOutput = Yii::app()->actionSettings->processOutput;
			}

			if (is_null($this->crossDomain))
			{
				$this->crossDomain = Yii::app()->actionSettings->crossDomain;
			}

			if (count($this->jsonAttributes) == 0)
			{
				$this->jsonAttributes = Yii::app()->actionSettings->jsonAttributes;
			}
		}

		if (is_null($this->processOutput))
		{
			$this->processOutput = false;
		}
		if (!is_null($this->crossDomain))
		{
			header('Access-Control-Allow-Origin: '.$this->crossDomain);
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
			header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
		}

		if (is_null($this->dataType))
		{
			$this->dataType = 'html';
		}

		if (!in_array(Yii::app()->request->requestType, $this->allowedMethods))
		{
			Yii::app()->end();
		}

		$this->attachBehaviors($this->behaviors);

		$this->onAfterInit(new CEvent($this));
	}
	
	public function render()
	{
		$this->onBeforeRender(new CEvent($this));

		$params = $this->getViewParams();

		foreach ($this->disallowedViewParamNames as $name) 
		{
			if (isset($params[$name]))
			{
				unset($params[$name]);
			}
		}

		switch ($this->dataType) {
			case 'html':
				$this->renderHtml($params);
				break;
			case 'json':
				$this->renderJSON($params);
				break;
		}
		
		$this->onAfterRender(new CEvent($this));
	}

	public function renderHtml()
	{
		if ($this->processOutput)
		{	
			$params = $this->getViewParams();

			if (Yii::app()->request->isAjaxRequest)
			{
				$this->renderView($this->ajaxView,$params,true);
			}
			else
			{
				$this->renderView($this->id,$params);
			}
		}
	}

	public function renderJSON($params)
	{
		if ($this->processOutput)
		{
			$params = $this->prepareJSONAttributes($params);

			echo CJSON::encode($params);
		}
		Yii::app()->end();
	}

	public function renderView($view,$params = array(),$partial=false)
	{
		$controller = $this->controller;

		if ($controller->getViewFile($view) === false)
		{
			$defaultView = '//default/'.$view;
			if ($controller->getViewFile($view) !== false) 
			{
				$view = $defaultView;
			}
		}

		if (count($this->partialsQueue))
		{
			$controller->renderPartialsQueue($this->partialsQueue,$params);
		}
		else
		{
			if ($partial)
			{
				$controller->renderPartial($view,$params);
			}
			else
			{
				$controller->render($view,$params);
			}
		}
		
	}

	public function prepareJSONAttributes($data)
	{
		$result = array();
		if (is_array($data))
		{
			foreach ($data as $key=>$item) 
			{
				$result[$key] = $this->prepareJSONAttributes($item);
			}
		}
		elseif (is_object($data))
		{
			if (is_array($this->jsonAttributes))
			{
				foreach ($this->jsonAttributes as $modelName => $attributes) 
				{
					if (get_class($data) === $modelName)
					{
						if ($data instanceof CModel && $attributes === '*')
						{
							$result = $data->getAttributes();
						}
						else
						{
							foreach ($attributes as $attribute) 
							{
								if ($attribute == '*')
								{
									$result = array_merge($result , $data->getAttributes());
								}
								else
								{
									$attributeData = $data->$attribute;

									$result[$attribute] = $this->prepareJSONAttributes($attributeData);
								}
							}
						}
						
					}
				}
			}
		}
		else
		{
			$result = $data;
		}

		return $result;
	}

	public function redirect()
	{
		$controller = $this->controller;
		
		if (!Yii::app()->request->isAjaxRequest)
		{
			$controller->redirect($this->redirectUrl);
		}
	}

	public function setViewParams($params)
	{
		$this->_viewParams = $this->parseViewParams($params);
	}

	public function parseViewParams($params)
	{
		if (is_array($params))
		{
			$data = array();
			
			foreach ($params as $key => $param) 
			{
				$data[$key] = $this->parseViewParams($param);
			}
			return $data;
		}
		else
		{
			$allowedKeywords = array('eval');

			if (
				is_string($params) 
				&& ($pos = strpos($params,':')) !== false 
				&& in_array(($keyword = substr($params,0,$pos)), $allowedKeywords  ))
			{
				switch ($keyword) {
					case 'eval':
						$params = $this->evaluateExpression(substr($params,$pos+1));
						break;
				}
			}

			return $params;
		}
	}

	public function getViewParams()
	{
		if (!$this->_paramsParsed)
		{
			$this->_paramsParsed = true;
			$this->params = $this->parseViewParams($this->params);
		}
		
		return array_merge($this->params, $this->_viewParams);
	}

	//  Events
	public function onBeforeRender($event)
	{
		$this->raiseEvent('onBeforeRender', $event);
	}

	public function onAfterRender($event)
	{
		$this->raiseEvent('onAfterRender', $event);
	}

	public function onAfterInit($event)
	{
		$this->raiseEvent('onAfterInit', $event);
	}
}