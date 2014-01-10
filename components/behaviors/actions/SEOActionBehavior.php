<?php /**
* 
*/
Yii::import('ext.components.behaviors.actions.BaseModelActionBehavior');

class SEOActionBehavior extends BaseModelActionBehavior
{
	public $titleAttribute;
	public $keywordsAttribute;
	public $descriptionAttribute;

	public $titleExpression;
	public $keywordsExpression;
	public $descriptionExpression;

	public $pageTitleTemplate;
	
	public function setModel($event)
	{
		parent::setModel($event);

		$model = $this->owner->getModel();
		$controller = $this->owner->controller;

		if (!is_null($this->pageTitleTemplate))
		{
			$controller->pageTitleTemplate = $this->pageTitleTemplate;
		}
		
		if (!is_null($this->titleAttribute))
		{
			$controller->contentTitle = $model->{$this->titleAttribute};
		}
		if (!is_null($this->keywordsAttribute))
		{
			$controller->keywords = $model->{$this->keywordsAttribute};
		}
		if (!is_null($this->descriptionAttribute))
		{
			$controller->description = $model->{$this->descriptionAttribute};
		}

		if (!is_null($this->titleExpression))
		{
			$controller->contentTitle = $this->evaluateExpression($this->titleExpression,array('model'=>$model));
		}
		if (!is_null($this->keywordsExpression))
		{
			$controller->keywords = $this->evaluateExpression($this->keywordsExpression,array('model'=>$model));
		}
		if (!is_null($this->descriptionExpression))
		{
			$controller->description = $this->evaluateExpression($this->descriptionExpression,array('model'=>$model));
		}
	}

} ?>