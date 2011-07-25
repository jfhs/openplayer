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
    
    public function getOptions() {
        return $this->config;
    }
    
    public function getOption( $section, $key ) {
        return $this->config[$section][$key];
    }
    
}