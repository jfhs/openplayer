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
	private static $translit_arr = array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
        "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
        "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
        "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
    );
	
	public static function translit($name) {
		return strtr($name, self::$translit_arr);
	}
}