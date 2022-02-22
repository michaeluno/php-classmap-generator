<?php

namespace PHPClassMapGenerator\Utility;

/**
 *
 * ### Examples
 * #### Including an array of class list
 * ```
 * $_aClassMap = [
 *   "PHPClassMapGenerator\\PHPClassMapGenerator" => __DIR__ . "/PHPClassMapGenerator.php",
 *   "PHPClassMapGenerator\\Autoload" => __DIR__ . "/autoload.php",
 * ];
 * PHPClassMapGenerator\Utility\Autoload::set( $_aClassMap );
 * ```
 * #### Including the class map file that returns an array of class list
 * ```
 * PHPClassMapGenerator\Utility\Autoload::set( include( __DIR__ . '/class-map.php' ) );
 * ```
 * @since 1.3.0
 */
class AutoLoad {

    public $aClasses = [];

    /**
     * @param array $aClasses
     */
    public function __construct( array $aClasses ) {
        $this->aClasses = $aClasses;
        if ( version_compare(PHP_VERSION, '8.0.0', '>=') ) {
            spl_autoload_register( array( $this, 'replyToIncludeClass' ) );
        } else {
            spl_autoload_register( array( $this, 'replyToIncludeClass' ), false );
        }
    }

    /**
     * @param    string                  $sUnknownClassName
     * @callback sql_autoload_register()
     */
    public function replyToIncludeClass( $sUnknownClassName ) {
        if ( ! isset( $this->aClasses[ $sUnknownClassName ] ) ) {
            return;
        }
        include( $this->aClasses[ $sUnknownClassName ] );
    }

    /**
     * @param array $aClasses
     */
    static public function set( array $aClasses ) {
        $_sSelfClass = __CLASS__;
        new $_sSelfClass( $aClasses );
    }

}