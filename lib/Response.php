<?php
namespace Lib;

class Response {
	
    public static function sendfile($params) {
    	if ($params['filepath']) {
    		$params['size'] = filesize($params['filepath']);
    		if (!isset($params['filename'])) {
    			$params['filename'] = basename($params['filepath']);
    		}
    	}
    	if (!$params['size']) {
    		$params['size'] = strlen($params['content']);
    	}
    	if (!isset($params['mime'])) {
    		$params['mime'] = 'application/octet-stream';
    	}
	    header('Content-Description: File Transfer');
	    header('Content-Type: '.$params['mime']);
	    header('Content-Disposition: attachment; filename="'.$params['filename'].'"');
	    header('Content-Transfer-Encoding: binary');
	    if (isset($params['size'])) {
	    	header('Content-Length: ' . $params['size']);
	    }
	    ob_clean();
	    flush();
	    if (isset($params['filepath'])) {
	    	readfile($params['filepath']);
	    } else {
	    	echo $params['content'];
	    }
		die;
    }
}