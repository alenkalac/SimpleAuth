<?php 
	namespace xample;

	use Silex\Application;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpFoundation\Request;
	use xample\keygen;

	class mainController {

		public function homepage(Request $r, Application $app) {
			if($app['session']->has("key"))
				$app['session']->remove("key");
			return $app['twig']->render('home.twig', []);
		}

		public function getKey(Request $r, Application $app) {
			$keygen = new keygen();

			if(!$app['session']->has("key")) {
				$key = $keygen->generateKey();
				$app['session']->set("key", $key);
				$keygen->savekey($key, $app['db']);
			}
			return $app['twig']->render('key.twig', ["key" => $app['session']->get("key")]);
		}


	}
?>