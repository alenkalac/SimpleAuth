<?php 
	$app->post('/gen', getCtrlPath("auth", "gen"));
	$app->post('/login', getCtrlPath("auth", "login"));
	$app->post('/register', getCtrlPath("auth", 'register'));

	function getCtrlPath($class, $function) {
		return "xample\\$class::$function";
	}
?>