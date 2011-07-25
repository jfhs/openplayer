<?php
namespace Lib;

class Request {
    public static function get( $key, $default = null ) {
        return isset( $_REQUEST[$key] ) 
            ? $_REQUEST[$key]
            : $default;
    }
    
    public static function getGet( $key, $default = null ) {
        return isset( $_GET[$key] ) 
            ? $_GET[$key]
            : $default;
    }
    
    public static function getPost( $key, $default = null ) {
        return isset( $_POST[$key] ) 
            ? $_POST[$key]
            : $default;
    }
    
    public static function getRequest() {
        return $_REQUEST;
    }
    
    public static function isPost() {
        return ( $_SERVER['REQUEST_METHOD'] == 'POST' );
    }
    
    public static function isGet() {
        return ( $_SERVER['REQUEST_METHOD'] == 'GET' );
    }
    
    public static function getAllGet() {
        return $_GET;
    }
    
    public static function getAllPost() {
        return $_POST;
    }
    
    
}