<?php

namespace Lib;

class Lang {
    public static $translate = null;

    public static function init() {
        $curlang = isset( $_SESSION['op']['lang'] ) 
            ? $_SESSION['op']['lang'] 
            : \Lib\Config::getInstance()->getOption('app', 'defLang', 'ru');

        self::$translate = require_once ROOT . "/i18n/{$curlang}.php";
    }

    
}