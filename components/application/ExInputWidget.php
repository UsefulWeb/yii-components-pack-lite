<?php

class ExInputWidget extends CInputWidget
{
    private $_assetsBase;
    private $_assets;

    public function getAssets()
    {
	$c = new ReflectionClass($this);

	if ($this->_assets === null) {
	    $this->_assets = Yii::app()->assetManager->publish(
		dirname($c->getFileName()).'/assets',
		false,
		-1,
		YII_DEBUG
	    );
	}
	return $this->_assets;
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
}