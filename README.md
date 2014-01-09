yii-components-pack-lite
========================

This components allow you to reduce a lot of code in Yii's controller.
You don't need to use controller actions at many cases with this components.

This project need giix extension

1. How to Configure
place this project into ext
main.php

'import'=>array(
    ...
		'ext.giix-components.*',
		'ext.components.application.ExController',
		'ext.components.application.ExActiveRecord',
		'ext.components.actions.ActionsTemplate',
),

2.Default Actions Folder Structure:
  ---actions
  --+base
    |--+interfaces
       |--IModelAction.php
       |--IRenderAction.php
    |--BaseAction.php
    |--BaseActionAdmin.php
    |--BaseActionDelete.php
    |--BaseActionIndex.php
    |--BaseActionInlineUpdate.php
    |--BaseActionUpdate.php
    |--BaseActionView.php
    |--BaseModelAction.php
  |--ActionAdmin.php
  |--ActionDelete.php
  |--ActionError.php
  |--ActionLogin.php
  |--ActionLogout.php
  |--ActionModelRedirect.php
  |--ActionRedirect.php
  |--ActionToggleState.php
  |--ActionUpdate.php
  |--ActionView.php
  |--ActionsTemplate.php
