yii-components-pack-lite
========================

This components allow you to reduce a lot of code in Yii's controller.

You don't need to use controller actions at many cases with this components.

This project need giix extension

### Requirements

1. Yii 1 ( http://www.yiiframework.com/ )
2. giix extension ( http://www.yiiframework.com/extension/giix/ )


### How to Configure

Copy `components` folder to `/protected/extensions/`.
Update your config (usually `main.php`) like

```PHP
'import'=>array(
    ...
		'ext.giix-components.*',
		'ext.components.application.ExController',
		'ext.components.application.ExActiveRecord',
		'ext.components.actions.ActionsTemplate',
),
```

