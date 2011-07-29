<?php
namespace Lib\Base;

class DB {
    private static $instance = null;
    private $pdo= null;
    
    /**
     *
     * @return DB
     */
    public static function getInstance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    
    private function __construct() {
        $options = array();
        
        if ( 0 == strpos(\Lib\Config::getInstance()->getOption('database', 'dsn'), 'mysql') ) {
            $options = array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".\Lib\Config::getInstance()->getOption('database', 'charset')
            );
        }
        
        $this->pdo = new \PDO(
            \Lib\Config::getInstance()->getOption('database', 'dsn'), 
            \Lib\Config::getInstance()->getOption('database', 'user'), 
            \Lib\Config::getInstance()->getOption('database', 'password'),
            $options
        );
    }
    
    private function __clone() {}
    
    public function getPDO() {
        return $this->pdo;
    }
    
}