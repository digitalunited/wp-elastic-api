<?php
/*
 * Development configuration for application
 *
 * Used in case of WP_DEBUG == true
 */
return array(
	'host'          => 'elasticsearch.flowcom.io',
	'port'          => '80',
	'index'         => 'wp-elastic-api',
	'instance_name' => 'wp-elastic-api',
	'base_path'     => '/app/plugins/wp-elastic-api',
	'valid_ip'      => array(
		'127.0.0.1',
		'192.168.50.1',
	)
);
