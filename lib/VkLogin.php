<?php
namespace Lib;

class VkLogin {
    const COOK_PATH = 'cookie';

    public static function getCookie() {
        $cookie = '';

        if ( file_exists( VkLogin::COOK_PATH ) ) {
            $cookie = file_get_contents( VkLogin::COOK_PATH );
        }

        if ( !$cookie ) {
            $cookie = self::login();
        }
        
        return $cookie;
    }

    private static function login() {
        $email = Config::getInstance()->getOption('vk', 'login');
        $pass = Config::getInstance()->getOption('vk', 'password');

        $sid = self::vkAuth( $email, $pass );

        file_put_contents(
            VkLogin::COOK_PATH, 
            $sid
        );

        return $sid;
    }

    private static function vkAuth( $email, $pass ) {
        $res = Curl::process('http://vk.com/login.php?op=a_login_attempt');
        if ($res <> 'vklogin') return false;

        $res = Curl::process(
            'http://login.vk.com/', 
            false, 
            1, 
            'act=login&success_url=&fail_url=&try_to_login=1&to=&vk=1&al_test=3&email=' . 
                urlencode($email) . '&pass=' . 
                urlencode($pass) . '&expire='
        );

        preg_match(
            '#hash=([0-9a-f]+)#', 
            $res, 
            $tmp
        );
        $hash = $tmp[1];

        $res = Curl::process(
            "http://vk.com/login.php?act=slogin&fast=1&hash={$hash}&s=1", 
            false, 
            1
        );
        preg_match( '#remixsid=([0-9a-f]+);#', $res, $tmp );
        
        if ( $tmp[1] == '' ) return false;

        $cookie = "remixsid={$tmp[1]};";
        
        return $cookie;
    }

}