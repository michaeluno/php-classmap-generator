<?php

include( dirname( __DIR__ ) . '/source/autoload.php' );

new \PHPClassMapGenerator\PHPClassMapGenerator(
    dirname( __DIR__ ) . '/source', // base dir
    dirname( __DIR__ ) . '/source', // scan dir name
    dirname( __DIR__ ) . '/source/class-map.php',
    [
        'exclude_classes'       => [ 'ProjectHeader' ],
        'base_dir_var'          => '__DIR__',
        'output_var_name'		=> 'return',
        'search'                => [
            'exclude_dir_names'		=> [ 'test' ],
        ],
        'comment_header'        => [
            'path'  => dirname( __DIR__ ) . '/source/PHPClassMapGenerator.php',
        ],
    ]
);