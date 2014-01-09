<?php

Yii::import('ext.components.actions.base.BaseModelAction');

class ActionAddComment extends BaseModelAction
{	
	public $attribute;
	public $text_attribute = 'text';

	public function run()
	{
		$id = Yii::app()->request->getQuery('id',null);
		
		$model = new $this->modelName;
		if(isset($_POST[$this->modelName]))
		{
			$model->attributes=$_POST[$this->modelName];
			$model->{$this->attribute} = $id;

			$text = preg_replace("/[\s;]/", '', $model->{$this->text_attribute});
			if(!empty($text))
			{
				$model->save();
			}
		}
		$this->controller->redirect(array('view','id'=>$id));
	}
}