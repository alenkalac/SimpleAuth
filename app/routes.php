<?php 
	$app->get('/gen', getCtrlPath("mainController", "getKey"));
	$app->get('/', getCtrlPath("mainController", "homepage"));
	$app->post('/login', getCtrlPath("auth", "login"));
	$app->post('/register', getCtrlPath("auth", 'register'));

	function getCtrlPath($class, $function) {
		return "xample\\$class::$function";
	}
?>