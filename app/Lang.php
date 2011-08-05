<?php
namespace App;

class Lang extends \Lib\Base\App {
    public function init() {
        if ( \Lib\Request::get('lang') && in_array(\Lib\Request::get('lang'), \Lib\Config::getInstance()->getOption('app', 'availableLangs')) ) {
            $_SESSION['op']['lang'] = \Lib\Request::get('lang');
            
            if ( $user = \Manager\User::getUser() ) {
                $settings = $user->settings;
                $settings['lang'] = \Lib\Request::get('lang');
                \Manager\User::create()->updateSettings( $settings );
            }
        }
        
        header("Location:" . \Lib\Config::getInstance()->getOption('app', 'baseUrl'));
    }

}
