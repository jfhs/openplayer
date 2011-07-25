<?php
session_start();

define('ROOT', __DIR__);

function classLoad( $className ) {
    $namespaces = array(
        "Lib" => "lib",
        "App" => "app",
        "Manager" => "managers"
    );

    foreach ( $namespaces as $ns => $path ) {
        if ( 0 === strpos( $className, "{$ns}\\" ) ) {
            $pathArr = explode( "\\", $className );
            $pathArr[0] = $path;

            $class = implode( DIRECTORY_SEPARATOR, $pathArr );

            require_once ROOT . DIRECTORY_SEPARATOR . "{$class}.php";
        }
    }
}

spl_autoload_register("classLoad");

$app = strtolower( Lib\Request::get('app', 'index') );

$appClass = "App\\".ucfirst($app);
$appObj = new $appClass;
$appObj->run( $app );
