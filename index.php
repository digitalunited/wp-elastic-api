<?php
/*
Plugin Name: WP Elasticsearch API
Description: API to store posts from WordPress to Elasticsearch and back.
Author: Digital United
Version: 0.1
Author URI: http://www.careofhaus.io/
*/

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require __DIR__ . '/vendor/autoload.php';
}

DigitalUnited\WPElasticAPI\WPElasticAPI::init();

if( function_exists( 'register_activation_hook' )) {
	register_activation_hook(__FILE__, array('\DigitalUnited\WPElasticAPI\WPElasticAPI', 'plugin_activated'));
}
