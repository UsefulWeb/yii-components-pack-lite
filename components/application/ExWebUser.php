<?php
	class ExWebUser extends CWebUser{
        
        private $_model;
        
        public function prolongSession()
        {
            if (count($this->getState('__states')) == 0&&!is_null($this->id))
            {   
                $identity = new UserIdentity($this->id,false);
                $identity->authenticate(true);
                $this->login($identity,3600*24*30*12);
            }
        }

        public function getRefererUrl($absolute = false)
        {
            $request = Yii::app()->request;
            
            $referrer = $request->getUrlReferrer();
            if ($absolute)
                return $referrer;

            $baseUrl = Yii::app()->getBaseUrl(true);
            return str_replace($baseUrl, "", $referrer);
        }
        
        public function getModel()
        {
            return $this->_model = User::model()->findByPk($this->id);
        }
	}
?>
