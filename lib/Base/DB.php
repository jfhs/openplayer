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
        $config = \Lib\Config::getInstance()->getOptions();
        $config = $config['database'];
        
        $this->pdo = new \PDO(
            "mysql:host={$config['host']};dbname={$config['db']}", 
            $config['user'], 
            $config['password'],
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
            )
        );
    }
    
    private function __clone() {}
    
    public function getPDO() {
        return $this->pdo;
    }
    
}