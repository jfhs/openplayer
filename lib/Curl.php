<?php
namespace Lib;

class Curl {

    public static function process( $url, $cookie=false, $headers=false, $post=false ) {
        $ch = curl_init($url);
        
        curl_setopt(
            $ch, 
            CURLOPT_RETURNTRANSFER, 
            true
        );
        
        curl_setopt(
            $ch, 
            CURLOPT_HEADER, 
            $headers
        );
        
        curl_setopt(
            $ch, 
            CURLOPT_FOLLOWLOCATION, 
            0
        );
        
        curl_setopt(
            $ch, 
            CURLOPT_COOKIE, 
            $cookie
        );
        
        curl_setopt(
            $ch, 
            CURLOPT_USERAGENT, 
            Config::getInstance()->getOption('vk', 'userAgent')
        );
        
        if ($post) {
            curl_setopt(
                $ch, 
                CURLOPT_POST, 
                1
            );
            
            curl_setopt(
                $ch, 
                CURLOPT_POSTFIELDS, 
                $post
            );
        }
        
        $response = curl_exec($ch);
        
        curl_close($ch);
        
        return $response;
    }

}