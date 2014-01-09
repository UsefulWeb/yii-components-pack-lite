<?php 

class ExController extends GxController
{
	public $pageTitleTemplate = '" $contentTitle $action $controller $appName"';
	public  $theme;
	public  $modelName;
	
	public  $contentTitle = '';
	public  $keywords = '';
	public  $description = '';

	// PRIVATE
	private $_pageTitle;
	private $_assetsBase;
	
	public function init()
	{
		parent::init();

		if (!is_null($this->theme))
		{
			Yii::app()->theme = $this->theme;
		}	
	}

   	function loadModel($id=null,$modelClass,$params=array())
	{

		if (!is_null($id))
		{
			$model = parent::loadModel($id,$modelClass);
		}
		else
		{
			$model = (isset($params['scenario']))?new $modelClass($params['scenario']):new $modelClass;
			
			if (isset($params['unsetAttributes'])&&$params['unsetAttributes'])
			{
				$model->unsetAttributes();
			}
		}
		if (isset($params['resetScope'])&&$params['resetScope'])
		{
			$model->resetScope();
		}
		if (isset($params['criteria']))
		{
			$model->getDbCriteria()->mergeWith($params['criteria']);
		}
		
		return $model;
	}

	public function getPageTitle()
	{
	    if($this->_pageTitle!==null)
	        return $this->_pageTitle;
	    else
	    {
	        $controller=ucfirst(basename($this->getId()));
	        $action = '';
	        $name = $controller;

	        if($this->getAction()!==null && strcasecmp($this->getAction()->getId(),$this->defaultAction))
	        {
	        	$action = ucfirst($this->getAction()->getId());
	        	$name = $action.' '.$controller;
	        }
	        if (!is_null($this->module))
	        {
	        	$controller = $this->module->name.' '.$controller;
	        }

	        $appName = trim(Yii::app()->name);
	        $name = trim(Yii::t('pageTitle',$name));
	        $action = trim(Yii::t('pageTitle',$action));
	        $contentTitle = $this->contentTitle;
	        $controller = trim(Yii::t('pageTitle',$controller));

	        if (!empty($controller))
	        {
	        	$controller .= ' |';
	        }
	        if (!empty($name))
	        {
	        	$name .= ' |';
	        }
	        if (!empty($contentTitle))
	        {
	        	$contentTitle .= ' |';
	        }
	        if (!empty($action))
	        {
	        	$action .= ' |';
	        }

	        return $this->_pageTitle=$this->evaluateExpression($this->pageTitleTemplate,array(
	        	'appName'=>$appName,
	        	'name'=>$name,
	        	'action'=>$action,
	        	'contentTitle'=>$contentTitle,
	        	'controller'=>$controller,
	        ));
	    }
	}

	public function renderPartialsQueue($queue,$params = array())
	{
		
		foreach ($queue as $partial) 
		{
			$this->renderPartial($partial,$params);
		}
	}
 	
 	public function getAssetsBase(){
        if ($this->_assetsBase === null) {
            $this->_assetsBase = Yii::app()->assetManager->publish(
                Yii::getPathOfAlias('application.assets'),
                false,
                -1,
                YII_DEBUG
            );
        }
        return $this->_assetsBase;
    }
    public function getThemeAssets()
    {
    	return $this->assetsBase.'/'.Yii::app()->theme->name;
    }
}
 