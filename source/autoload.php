<?php

namespace PHPClassMapGenerator;

include_once( __DIR__ . '/Utility/AutoLoad.php' );
Utility\Autoload::set( include( __DIR__ . '/class-map.php' ) );