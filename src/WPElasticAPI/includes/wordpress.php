<?php

namespace DigitalUnited\WPElasticAPI\api;

include_once 'includes.php';

class WordPress {

	function __construct() {
		add_filter( 'rewrite_rules_array', array( &$this, 'rewrite_rules_array' ) );
		add_action( 'init', array( &$this, 'init' ) );
	}

	function rewrite_rules_array( $rules ) {
		$new_rules = array(
				'(api/)' => 'index.php',
		);
		$rules     = $new_rules + $rules;
		return $rules;
	}

	function init() {
		if ( strstr( $_SERVER['REQUEST_URI'], 'api/' ) ) {
			define( 'SHORTINIT', true );
			$app = new \Slim\Slim();
			$app->setName('wp-elastic-api');
			new DocRoutes( $app );
			new v1\Routes( $app );
			$app->run();
			exit;
		}
	}

}

new WordPress();