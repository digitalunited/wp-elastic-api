<?php

namespace DigitalUnited\WPElasticAPI;

/**
 * Handles the configuration located in config folder.  Also responsible for folder creation
 * and checking permissions
 */
class Configuration {
    /**
     * @var StdObj
     */
    protected static $_configuration;

    /**
     * Overwrites keys if wpbase-prefixed constant is defined
     * Ex. WPBASE_FOOBAR overwrites the foobar-property
     *
     * @param mixed $configuration Configuration array
     *
     * @return array Updated configuration array
     */
    protected static function apply_user_overwrites( $configuration ) {
        foreach ( $configuration as $param => $val ) {
            $const_name = 'WP_ELASTIC_API_' . strtoupper( $param );

            if ( defined( $const_name ) ) {
                $configuration[ $param ] = constant( $const_name );
            }
        }

        return $configuration;
    }

    /**
     * If a configuration exists it will be overwritten
     *
     * @param $configuration_name mixed config-file available in config folder, default: dev/prod dependent on wp_debug
     */
    public static function load_configuration( $configuration_name = false ) {
        $configuration_folder = __DIR__ . '/config/';
        if ( $configuration_name ) {
            $file_name = $configuration_name . '.php';
        } else {
            $file_name = defined('WP_DEBUG') && WP_DEBUG ? 'development.php' : 'production.php';
        }
        $full_path = $configuration_folder . $file_name;

        if ( ! is_readable( $full_path ) ) {
            throw new \Exception( sprintf( "File %s could not be read", $full_path ) );
        }

        $default_config = include $full_path;

        self::$_configuration = (object) self::apply_user_overwrites( $default_config );

        return $default_config;
    }

    /**
     * @param mixed $property requested configuration property
     *
     * @return mixed StdObj|string
     */
    public static function get( $property = false ) {
        if ( ! self::$_configuration ) {
            self::load_configuration();
        }

        if ( $property ) {
            if ( self::$_configuration->$property ) {
                return self::$_configuration->$property;
            } else {
                throw new \Exception( sprintf( 'Property %s do not exist', $property ) );
            }
        }

        return self::$_configuration;
    }

}
