<?php

spl_autoload_register(function($classScalar) {
    $classVector = explode('\\', $classScalar);
    if (count($classVector) > 1 && $classVector[0] == 'MongoAR') {
        $filename = dirname(__FILE__)
            . DIRECTORY_SEPARATOR
            . implode(DIRECTORY_SEPARATOR, array_slice($classVector, 1))
            . '.php';
        if (file_exists($filename)) {
            require_once $filename;
        }
    }
});
