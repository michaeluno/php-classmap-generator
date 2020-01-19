<?php

include( dirname( __DIR__ ) . '/PHPClassMapGenerator.php' );




new \PHPClassMapGenerator\PHPClassMapGenerator(
    __DIR__, // base dir
    __DIR__ . '/_scandir', // scan dir name
    __DIR__ . '/class-map.php',
    []
);