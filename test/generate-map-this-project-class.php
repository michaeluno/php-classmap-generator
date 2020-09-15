<?php

include( dirname( __DIR__ ) . '/source/autoload.php' );


new \PHPClassMapGenerator\PHPClassMapGenerator(
    dirname( __DIR__ ) . '/source', // base dir
    dirname( __DIR__ ) . '/source', // scan dir name
    dirname( __DIR__ ) . '/source/class-map.php',
    [

        'header_class_path'		=> __DIR__ . '/ProjectHeader.php',
        'header_type'			=> 'CONSTANT', // or 'CONSTANT
        'exclude_classes'       => [ 'ProjectHeader' ],
        'base_dir_var'          => '__DIR__',
        'output_var_name'		=> 'return',
        'search'                => [
            'exclude_dir_names'		=> [ 'test' ],
        ]
    ]
);