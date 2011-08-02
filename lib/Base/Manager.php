<?php
namespace Lib\Base;

class Manager {
    /**
     * @var PDO
     */
    protected $pdo;
    
    public function __construct() {
       $this->pdo = DB::getInstance()->getPDO();
       $this->init();
    }
    
    public static function create() {
        $className = get_called_class();
        return new $className;
    }


    public function init() {}
    
    
    
}