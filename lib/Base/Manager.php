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
        return new self;
    }


    public function init() {}
    
    
    
}