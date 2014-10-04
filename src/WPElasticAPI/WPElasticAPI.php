<?php

namespace DigitalUnited\WPElasticAPI;

class WPElasticAPI {
	public static function init() {

		$app = new \Slim\Slim();
		$app->setName( 'wp-elastic-api' );
		Doc::createInstance( $app );
		Posts::createInstance( $app );

		if( strpos( $app->request()->getPath(), Application::BasePath() ) === 0 ) {
			$app->run();
			exit;
		}

	}

	public static function plugin_activated() {
	}
}
