<?php
namespace Manager;

class Songs extends \Lib\Base\Manager {
    
    public function findSongToDelete($min_size) {
    	$q = $this->pdo->prepare("SELECT * FROM songs WHERE size >= ? AND filename!='' ORDER BY hits ASC LIMIT 1");
    	$q->execute(array($min_size));
    	return $q->fetch(\PDO::FETCH_OBJ);
    }
    
    public function addSong($id, $filename, $name, $artist) {
    	return $this->pdo->prepare("INSERT INTO songs SET song_id=?, filename=?, name=?, artist=?")
    		->execute(array($id, $filename, $name, $artist));
    }
    
    public function updateSong($id, $data) {
    	if (!count($data)) {
    		return;
    	}
    	$q = '';
    	foreach($data as $k => $v) {
    		if ($q) {
    			$q .= ',';
    		}
    		$q .= '`'.$k.'`=?';
    	}
    	$q = "UPDATE songs SET ".$q." WHERE song_id=?";
    	$data = array_values($data);
    	$data[] = $id;
    	return $this->pdo->prepare($q)
    		->execute($data);
    }
}