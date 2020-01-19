<?php

include( dirname( __DIR__ ) . '/PHPClassMapGenerator.php' );


new \PHPClassMapGenerator\PHPClassMapGenerator(
    dirname( __DIR__ ), // base dir
    dirname( __DIR__ ), // scan dir name
    dirname( __DIR__ ) . '/class-map.php',
    [
        'base_dir_var' => '__DIR__',
        'search' => [
            'exclude_dir_names'		=> [ 'test' ],
        ]
    ]
);