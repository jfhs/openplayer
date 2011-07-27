<?php
namespace Lib;

class Helper {
    const FOLDER_LEN = 8;
    
    public static function calcPath( $id ) {
        if ( strlen($id) > self::FOLDER_LEN ) {
            $ret = 
                substr( 
                    $id, 
                    0, 
                    self::FOLDER_LEN 
                ) . 
                DIRECTORY_SEPARATOR . 
                self::calcPath(
                    substr(
                        $id, 
                        self::FOLDER_LEN
                    )
                );
            
            return $ret;
        } else {
            return $id;
        }
        
        
    }
	
}