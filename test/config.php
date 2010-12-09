<?php
 return array(
	'app' => array(
		'name' => 'FormDemo',
		'configPath' => 'D:\\PHPAPP\\phpwindframework\\demos\\formapp\\compile\\config.php',
		'rootPath' => 'D:\\PHPAPP\\phpwindframework\\demos\\formapp',
	),
	'modules' => array(
		'default' => array(
			'name' => 'default',
			'path' => 'form',
		),
	),
	'template' => array(
		'path' => 'templates',
		'name' => 'index',
		'ext' => 'html',
		'resolver' => 'default',
		'isCache' => 'false',
		'cacheDir' => 'cache',
		'compileDir' => 'compile',
	),
	'applications' => array(
		'web' => array(
			'name' => 'default',
			'class' => 'WIND:core.WindWebApplication',
		),
		'command' => array(
			'name' => 'default',
			'class' => 'WIND:core.WindCommandApplication',
		),
	),
	'errorMessage' => array(
		'errorAction' => 'WIND:core.WindErrorAction',
	),
	'filters' => array(
		'WindFormFilter' => array(
			'filterName' => 'WindFormFilter',
			'filterPath' => 'WIND:component.form.WindFormFilter',
		),
	),
	'viewerResolvers' => array(
		'default' => 'WIND:component.viewer.WindViewer',
		'pw' => 'WIND:component.viewer.WindPWViewer',
		'smarty' => 'libs.WindSmarty',
	),
	'router' => array(
		'parser' => 'url',
	),
	'routerParsers' => array(
		'url' => array(
			'rule' => array(
				'a' => 'run',
				'c' => 'index',
				'm' => 'default',
			),
			'path' => 'WIND:component.router.WindUrlBasedRouter',
		),
	),
	'dbConfig' => array(
		'phpwind' => array(
			'dbtype' => 'mysql',
			'dbhost' => 'localhost',
			'dbuser' => 'root',
			'dbpass' => 'suqian0512h',
			'dbport' => '3306',
			'dbname' => 'phpwind',
		),
		'user' => array(
			'dbtype' => 'mssql',
			'dbhost' => 'localhost',
			'dbuser' => 'sa',
			'dbpass' => 'suqian0512h',
			'dbport' => '3306',
			'dbname' => 'user',
		),
	),
	'dbDriver' => array(
		'mysql' => array(
			'path' => 'WIND:component.db.WindMySql',
			'className' => 'WindMySql',
		),
		'mssql' => array(
			'path' => 'WIND:component.db.WindMsSql',
			'className' => 'WindMsSql',
		),
	),
);
?>