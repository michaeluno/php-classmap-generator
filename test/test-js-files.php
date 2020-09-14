<?php

include( dirname( __DIR__ ) . '/source/PHPClassMapGenerator.php' );

use PHPClassMapGenerator\PHPClassMapGenerator;

$_oGenerator = new PHPClassMapGenerator(
    __DIR__, // base dir
    __DIR__ . '/_scandir', // scan dir name
    __DIR__ . '/class-map.php',
    [
        'output_var_name'		=> 'return',
        'do_in_constructor'     => false,
        'structure'             => 'PATH',
        'search'                => [
            'allowed_extensions'     => [ 'js' ],
        ],
    ]
);

print_r( $_oGenerator->get() );