<?php 

 class ActionsTemplate
 {

 	public static function getTemplate($actions = array('create','view','update','delete','index'),$path = 'default')
 	{
 		
 		$template = array();

 		foreach ($actions as $key => $value) 
 		{
 			$params = array();
 			$actionPath = $path;

 			if (is_numeric($key))
 			{
 				$action = $value;
 			}
 			else
 			{
 				$action = $key;
 				$params = $value;
 			}
 			
 			$type = self::getActionType($action);
 			$_path = self::getActionPath($action);

			$action = self::getActionName($action);

			if ($_path != $actionPath)
			{
				$actionPath = $_path;
			}

 			$params['class'] = isset($params['class']) ? $params['class'] : self::getActionClass($type,$actionPath,$params);
 			

 			$template[$action] = $params;
 		}
 		return $template;
 	}

 	public static function getActionClass($action,$path = 'default',$params = array())
 	{

 		$actionName = ($path == 'default' && $action == 'create') ? 'ActionUpdate' : 'Action'.strtoupper($action[0]).substr($action, 1);
 		
 		$class = '';

 		if (($pos = strpos($path, '.')) !== false)
 		{
 			$class = "{$path}.{$actionName}";
 		}
 		else
 		{
 			$class = "actions.{$path}.{$actionName}";
 		}
 		
 		$class = str_replace('actions.default','ext.components.actions',$class);

 		return $class;
 	}

 	public static function getActionName($action)
 	{
 		if (($pos = strpos($action, ':')) !== false)
		{
			return substr($action, 0,$pos);
		}
		else
		{
			if (($pos = strrpos($action, '.')) !== false)
			{
				$actionPath = substr($action,0,$pos);
				$action = substr($action,$pos+1);
			}
			return $action;
		}
 	}
 	public static function getActionType($action)
 	{
 		if (($pos = strpos($action, ':')) !== false)
		{
			return substr($action, $pos+1);
		}
		else
		{
			return self::getActionName($action);
		}
 	}
 	public static function getActionPath($action)
 	{
 		if (($pos = strrpos($action, '.')) !== false)
		{
			$actionPath = substr($action,0,$pos);
			return $actionPath;
		}
		else
		{
			return 'default';
		}
 	}
 }