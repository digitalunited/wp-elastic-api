<?php

namespace DigitalUnited\WPElasticAPI;

class Doc extends Application  {

	static function createInstance( $app ) {
		$app->container->singleton('Doc', function () {
			return new Doc();
		});
		$app->Doc->routes( $app );
	}

	function routes( \Slim\Slim $app ) {

		$base = $this->getBasePath();

		$app->get( $base . '/', function () use ($app) {
			$app->redirect( $app->Doc->getBasePath() . '/api-doc/' );
		});

		$app->get( $base . '/api-doc/json', function () use ($app) {
			$app->Doc->json();
		});

		$app->get( $base . '/api-doc/', function () use ($app) {
			$app->Doc->ui();
		});

		$app->get( $base . '/api-doc/json/:name', function ( $name ) use ($app) {
			$app->Doc->api( $name );
		});

	}

	function json() {

		$swagger = new \Swagger\Swagger( dirname( __DIR__ ) );

		header("Content-Type: application/json");

		$result = $swagger->getResourceList(array(
			//'basePath' => $this->slim->request()->getUrl() . $this->getBasePath()
		));

		$result['info'] = array(
			'title' => 'WP elasticsearch API',
			'description' => 'API to store posts from WordPress to Elasticsearch and back.<hr/>'
		);

		echo json_encode( $result );
	}

	function ui() {

		/**
		 * TODO: replace this ugly code with handlebars?
		 */
		$url = $this->getBasePath() . '/src/WPElasticAPI/ui';
		$json_url = $this->getBasePath() . '/api-doc/json';
		include 'ui/index.php';
	}

	function api( $name ) {

		$swagger = new \Swagger\Swagger( dirname( __DIR__ ) );

		header("Content-Type: application/json");

		$result = $swagger->getResource( '/' . $name, array('output' => 'json'));
		$result = get_object_vars( json_decode( $result ) );
		$result = str_replace( '{baseUrl}', "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['SERVER_NAME']. $this->getBasePath(), $result );

		echo json_encode( $result );

	}

}