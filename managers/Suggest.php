<?php
namespace Manager;
use \Lib\Config;

class Suggest extends \Lib\Base\Manager {
    
    public function get($term, $limit = 0) {
    	$limit = intval($limit);
    	if (!$limit) {
    		$limit = Config::getInstance()->getOption('app', 'suggestion_count');
    	}
    	$limit = intval($limit);
    	$term = $this->pdo->quote($term.'%');
    	$q = $this->pdo->query("SELECT artist FROM stat WHERE artist LIKE {$term} GROUP BY artist ORDER BY SUM(cnt) DESC LIMIT {$limit}");
        $results = array();
    	foreach($q->fetchAll(\PDO::FETCH_OBJ) as $row) {
    		$results[] = $row->artist;
    	}
    	if (count($results) < $limit) {
	    	$q = $this->pdo->query("SELECT artist, name FROM songs WHERE (artist LIKE {$term} OR name LIKE {$term}) AND name != null GROUP BY artist ORDER BY hits DESC LIMIT $limit");
	    	foreach($q->fetchAll(\PDO::FETCH_OBJ) as $row) {
	    		$results[] = $row->artist.' - '.$row->name;
	    		if (count($results) == $limit) {
	    			break;
	    		}
	    	}
    	}
    	return $results;
    }
}