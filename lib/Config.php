<?php
namespace Lib;

class Config {
    private static $instance = null;
    private $config= null;
    
    /**
     *
     * @return Config
     */
    public static function getInstance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    
    private function __construct() {
        $this->config = parse_ini_file(ROOT . '/configs/app.ini', true);
    }
    private function __clone() {}
    
    public function getOptions( $section = null ) {
        if ( null == $section) {
            return $this->config;
        } else {
            return $this->config[$section];
        }
    }
    
    public function getOption( $section, $key, $default = null ) {
        if ( !isset( $this->config[$section] ) ) return $default;
        
        if ( !isset( $this->config[$section][$key] ) ) return $default;
            
        return $this->config[$section][$key];
    }
    
}