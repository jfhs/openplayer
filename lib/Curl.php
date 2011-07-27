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

    //This functions uses HEAD instead of GET to get headers
    public static function get_headers($url, $format=0) {
        $old = stream_context_get_options(stream_context_get_default(array()));
        $opts = array('http' =>
            array('method' => 'HEAD'),
        );
        stream_context_set_default($opts);
        $headers = get_headers($url, $format);
        if (!isset($old['http']['method'])) {
            $old['http']['method'] = 'GET';
        }
        stream_context_set_default($old);
        return $headers;
    }
}