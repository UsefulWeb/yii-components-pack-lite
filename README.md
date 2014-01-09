yii-components-pack-lite
========================

This components allow you to reduce a lot of code in Yii's controller.

You don't need to use controller actions at many cases with this components.

This project need giix extension

1. How to Configure

place this project into ext

main.php
```PHP
'import'=>array(
    ...
		'ext.giix-components.*',
		'ext.components.application.ExController',
		'ext.components.application.ExActiveRecord',
		'ext.components.actions.ActionsTemplate',
),
```

