<?php 
	
	require_once "../vendor/autoload.php";
	require_once "../app/db.php";

	$app = new Silex\Application();

	$app['debug'] = true;

	require_once "../app/routes.php";


	$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
		'db.options' 	=> [
			'driver' 	=> 'pdo_mysql',
			'host' 		=> DBHOST,
			'dbname' 	=> DBNAME,
			'user' 		=> DBUSER,
			'password' 	=> DBPASS,
			'charset' 	=> 'utf8',
			'port'		=> '3307'
		]
	));

	$app->register(new Silex\Provider\TwigServiceProvider(), array(
	    'twig.path' => __DIR__.'/../templates',
	));

	$app->register(new Silex\Provider\SessionServiceProvider());

	$app->run();
?>