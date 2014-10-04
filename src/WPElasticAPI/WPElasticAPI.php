<?php

namespace DigitalUnited\WPElasticAPI;

class WPElasticAPI {
	public static function init() {

		$app = new \Slim\Slim();
		$app->setName( Application::InstanceName() );

		if( strpos( $app->request()->getPath(), Application::BasePath() ) === 0 ) {
			Doc::createInstance( $app );
			Posts::createInstance( $app );
			$app->run();
			exit;
		}

	}

	public static function plugin_activated() {
	}
}
