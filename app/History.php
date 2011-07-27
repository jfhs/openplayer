<?php

namespace App;

class History extends \Lib\Base\App {

    public function init() {
        $userManager = new \Manager\User;
        $this->history = $userManager->getHistory();
    }
    
    
}
