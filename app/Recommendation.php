<?php

namespace App;

class Recommendation extends \Lib\Base\App {

    public function init() {
        $statManager = new \Manager\Stat;
        $this->recommendations = $statManager->getRecommendations(
            \Lib\Request::get('q')
        );
    }
    
    
}
