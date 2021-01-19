<?php

include( dirname( __DIR__ ) . '/source/autoload.php' );


$_oGenerator = new \PHPClassMapGenerator\PHPClassMapGenerator(
    __DIR__, // base dir
    __DIR__ . '/_scandir/alias', // scan dir name
    '',
    [
        'output_var_name'		=> 'return',
        'do_in_constructor'     => false,
    ]
);

print_r( $_oGenerator->getItems() );