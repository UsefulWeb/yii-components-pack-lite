<?php

class ActionSettingsComponent extends CApplicationComponent
{
    public $dataType = 'html';
    public $redirectUrl = false;
    public $processOutput = false;
    public $jsonAttributes = null;
    public $crossDomain = null;
    
    public $actionBehaviors = array();

    public function actionBehaviors()
    {
        $behaviors = array();

        if (isset($this->behaviors['common']))
        {
            $behaviors = array_merge($behaviors,$this->behaviors['common']);
        }

        $actionId = Yii::app()->controller->action->id;
        
        if (isset($this->behaviors[$actionId]))
        {
            $behaviors = array_merge($behaviors,$this->behaviors[$actionId]);
        }

        return $behaviors;
    }
}

?>