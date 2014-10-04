<?php

namespace DigitalUnited\WPElasticAPI;

class Application {

	public $slim;
	public $elastica;

	public function __construct( \Slim\Slim $slim = null ) {
		$this->slim     = ! empty( $slim ) ? $slim : \Slim\Slim::getInstance( $this->getInstanceName() );
		$this->elastica = new \Elastica\Client( array(
			'host' => $this->getElasticsearchHost(),
			'port' => $this->getElasticsearchPort()
		) );
	}

	/**
	 * TODO: Configuration option to be created
	 */
	public function getElasticsearchIndex() {
		return defined( 'WP_ELASTIC_API_INDEX' ) ? WP_ELASTIC_API_INDEX : 'wp-elastic-api';
	}

	/**
	 * TODO: Configuration option to be created
	 */
	public function getElasticsearchHost() {
		return defined( 'WP_ELASTIC_API_HOST' ) ? WP_ELASTIC_API_HOST : 'flowcom:Arkitekt1@elasticsearch.flowcom.io';
	}

	/**
	 * TODO: Configuration option to be created
	 */
	public function getElasticsearchPort() {
		return defined( 'WP_ELASTIC_API_PORT' ) ? WP_ELASTIC_API_PORT : '80';
	}

	/**
	 * TODO: Configuration option to be created
	 */
	public function getInstanceName() {
		return 'wp-elastic-api';
	}

	public function getBasePath() {
		return Application::BasePath();
	}

	/**
	 * TODO: Configuration option to be created
	 */
	public static function BasePath() {
		return '/app/plugins/wp-elastic-api';
	}

	public function getBodyAsArray() {
		$body = $this->slim->request()->getBody();
		$body = json_decode( $body );
		if ( is_object( $body ) ) {
			$body = get_object_vars( $body );
		}

		return $body;
	}

	public function blockInvalidIPAddress() {
		if( !in_array( $this->slim->request()->getIp(), $this->getValidIPAddresses() ) ) {
			throw new \Exception( 'Request made from invalid IP-address: ' . $this->slim->request()->getIp() );
		}
	}

	public function getValidIPAddresses() {

		/**
		 * TODO: Configuration option to be created
		 */
		$result = array(
			'127.0.0.1',
			'192.168.50.1',
		);

		return $result;
	}


	public function check_req_opt_param( $rparam = array(), $optparam = array(), $uparam = array() ) {

		if ( ! is_array( $rparam )
		     || ! is_array( $optparam )
		     || ! is_array( $uparam )
		) {

			throw new \Exception( 'Parameters has to be arrays.' );
		}

		//Final User i/p array
		$uparam_final_array = array();

		//Checking if req parameter exsist in user parameter
		for ( $i = 0; $i < sizeof( $rparam ); $i ++ ) {

			$req_param_chk = array_key_exists( $rparam[ $i ], $uparam );

			//If Key Dont Exsist
			if ( $req_param_chk == false ) {
				throw new \Exception( "Required Parameter: '" . $rparam[ $i ] . "' is missing" );
			} else { //If Key Present
				// Checking If not set
				if ( ! isset( $uparam[ $rparam[ $i ] ] ) ) {
					throw new \Exception( "Parameter: '" . $rparam[ $i ] . "' - must be specified" );
				} else { //if Not empty
					$uparam_final_array[ $rparam[ $i ] ] = $uparam[ $rparam[ $i ] ];
					unset( $uparam[ $rparam[ $i ] ] );
				}
			}
		}

		// Optional Parameter Check
		for ( $j = 0; $j < sizeof( $optparam ); $j ++ ) {
			$optl_param_chk = array_key_exists( $optparam[ $j ], $uparam );
			//If Key Dont Exsist
			if ( $optl_param_chk == false ) {
				$uparam_final_array[ $optparam[ $j ] ] = null;
			} else { //if Key Present
				//Checking if empty
				if ( empty( $uparam[ $optparam[ $j ] ] ) ) {
					$uparam_final_array[ $optparam[ $j ] ] = null;
				} else { //if Not empty
					$uparam_final_array[ $optparam[ $j ] ] = $uparam[ $optparam[ $j ] ];
				}
				unset( $uparam[ $optparam[ $j ] ] );
			}
		}

		// Checking for invalid user parameters
		if ( sizeof( $uparam ) >= 1 ) {
			throw new \Exception( 'Invalid parameters for this command (' . join( ',', array_keys( $uparam ) ) . ')' );
		}

	}


}