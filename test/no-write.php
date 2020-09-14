<?php

include( dirname( __DIR__ ) . '/source/PHPClassMapGenerator.php' );


$_oGenerator = new \PHPClassMapGenerator\PHPClassMapGenerator(
    __DIR__, // base dir
    __DIR__ . '/_scandir', // scan dir name
    __DIR__ . '/class-map.php',
    [
        'output_var_name'		=> 'return',
        'do_in_constructor'     => false,
    ]
);

print_r( $_oGenerator->getItems() );