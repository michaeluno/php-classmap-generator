<?php

include( dirname( __DIR__ ) . '/source/PHPClassMapGenerator.php' );


new \PHPClassMapGenerator\PHPClassMapGenerator(
    dirname( __DIR__ ) . '/source', // base dir
    dirname( __DIR__ ) . '/source', // scan dir name
    dirname( __DIR__ ) . '/source/class-map.php',
    [
        'base_dir_var' => '__DIR__',
        'search' => [
            'exclude_dir_names'		=> [ 'test' ],
        ]
    ]
);