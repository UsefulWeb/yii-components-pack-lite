<?php

Yii::import('ext.components.actions.base.BaseModelAction');

class ActionAddComment extends BaseModelAction
{	
	public $attribute;
	public $text_attribute = 'text';
    
    /**
     * If set null - by default
     * CHttpRequest::getParam method
     * will be invoked and
     * GET and then POST global vars
     * will be checked
     * 
     * if GET or POST is specified
     * only specific global var will be
     * checked
     * 
     * @var [null|string]
     */
    public $request_type = null;

	public function run()
	{
        $id = $this->getId();
		
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
    
    /**
     * Grab ID from $_GET or $_POST
     * null by default
     */
    private function getId()
    {
        $default_val = null;
        if(is_null($this->request_type) || !in_array($this->request_type, array('GET', 'POST')))
        {
            $id = Yii::app()->request->getParam('id', $default_val);
        }
        else if($this->request_type === 'GET')
        {
            $id = Yii::app()->request->getJquery('id', $default_val);
        }
        else if($this->request_type === 'GET')
        {
            $id = Yii::app()->request->getPost('id', $default_val);
        }
    }
}