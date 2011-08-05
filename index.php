<?php
session_start();
define('ROOT', __DIR__);

# autoload
function classLoad( $className ) {
    $namespaces = array(
        "Lib" => "lib",
        "App" => "app",
        "Manager" => "managers"
    );

	foreach ( $namespaces as $ns => $path ) {
		if (( 0 === strpos( $className, "{$ns}\\" ) ) || ( 0 === strpos( $className, "\\{$ns}\\"))) {
			$pathArr = explode( "\\", $className );
			if ($pathArr[0] == '') {
				array_shift($pathArr);
			}
			$pathArr[0] = $path;

			$class = implode( DIRECTORY_SEPARATOR, $pathArr );

			require_once ROOT . DIRECTORY_SEPARATOR . "{$class}.php";
		}
	}
}

spl_autoload_register("classLoad");
# /autoload


# i18n
Lib\Lang::init();

function __ ( $word ) {
    return isset( Lib\Lang::$translate[$word] )
        ? Lib\Lang::$translate[$word]
        : $word;
}
# /i18n


# app init
$app = strtolower( Lib\Request::get('app', 'index') );

$appClass = "App\\".ucfirst($app);
$appObj = new $appClass;
$appObj->run( $app );
# /app init