<?php /**
* 
*/
interface IRenderAction
{	
	public function render();
	public function redirect();
	public function runAction($params);
} 
?>