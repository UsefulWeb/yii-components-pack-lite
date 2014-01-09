<?php /**
* 
*/
class CAuthBizRule
{
	protected static function getId($params = array())
	{	
		$session=new CHttpSession;
  		$session->open();
		if (isset($params['id']))
		{
			return $params['id'];
		}
		if (isset($session['bizRuleDataId']))
		{
			return $session['bizRuleDataId'];
		}
		if (isset($_GET['id']))
		{
			return $_GET['id'];
		}
	}
} ?>