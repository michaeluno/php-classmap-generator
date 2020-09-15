<?php

include( dirname( __DIR__ ) . '/source/autoload.php' );

new \PHPClassMapGenerator\PHPClassMapGenerator(
    __DIR__, // base dir
    __DIR__ . '/_scandir', // scan dir name
    __DIR__ . '/class-map.php',
    [
        'output_var_name'		=> 'return',
    ]
);