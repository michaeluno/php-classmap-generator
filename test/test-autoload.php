<?php

define( 'CLASS_MAP_BASE_DIR_VAR', __DIR__ );


\Autoload::set( include( __DIR__ . '/class-map.php' ) );

$_o = new \Foo\FooClass;
var_dump( get_class( $_o ) );

class Autoload {
    static public function set( array $aClasses ) {
        self::$aAutoLoadClasses = $aClasses + self::$aAutoLoadClasses;
        $_sFunc = function ( $sCalledUnknownClassName ) {
            if ( ! isset( self::$aAutoLoadClasses[ $sCalledUnknownClassName ] ) ) {
                return;
            }
            include( self::$aAutoLoadClasses[ $sCalledUnknownClassName ] );
        };
        spl_autoload_register( $_sFunc, false );
    }
    static public $aAutoLoadClasses = [];
}