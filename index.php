<?php

function loadSSWAP($class){
    // Validate class name to prevent directory traversal
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $class)) {
        return false;
    }
    
    // Remove any potential path traversal characters
    $class = preg_replace('/[^a-zA-Z0-9_]/', '', $class);
    
    $pathControllers = "controller/{$class}.php";
    $pathLibs = "libs/{$class}.php";
    $pathModels = "model/{$class}.php";
    $pathInterfaces = "libs/Interfaces/{$class}.php";
    $pathExceptions = "libs/Exceptions/{$class}.php";
    $pathConfig = "config/{$class}.php";
    $websockets = "websockets/{$class}.php";

    // Check if file exists before including to prevent errors
    if (file_exists($websockets)) {
        require_once $websockets;
    }elseif (file_exists($pathControllers)) {
        require_once $pathControllers;
    } elseif (file_exists($pathModels)) {
        require_once $pathModels;
    } elseif (file_exists($pathLibs)) {
        require_once $pathLibs;
    } elseif (file_exists($pathConfig)) {
        require_once $pathConfig;
    }elseif (file_exists($pathInterfaces)) {
        require_once $pathInterfaces;
    }elseif (file_exists($pathExceptions)) {
        require_once $pathExceptions;
    }
}

spl_autoload_extensions('.php,.phar');
spl_autoload_register('loadSSWAP');

//set config variables
new errorCodes();
new filePaths();
new serverDat();


//let us start the engine
new bootstrap();


