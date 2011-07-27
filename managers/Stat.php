<?php
namespace Manager;

class Stat extends \Lib\Base\Manager {
    
    public function log( $artist ) {
        $artist = strip_tags(trim($artist));
        //$artist = $this->pdo->quote($artist);
        
        $ip = ip2long($_SERVER['REMOTE_ADDR']);
        
        $res = $this->pdo->prepare("SELECT * FROM stat WHERE artist = ? AND ip = ?");
        $res->execute(array($artist, $ip));
        
        $q = "";
        if ( $res->fetchObject() ) {
            $q = "UPDATE stat SET cnt = cnt + 1, updatedAt = NOW() WHERE artist = :artist AND ip = :ip";
        } else {
            $q = "INSERT INTO stat VALUES (null, :ip, :artist, 1, NOW(), NOW())";
        }
        $res = $this->pdo->prepare($q);
      	$res->execute(array(':ip' => $ip, ':artist' => $artist));
        return $res;
    }
    
    public function logSong($id) {
    	return $this->pdo->prepare("UPDATE songs SET hits=hits+1 WHERE song_id=?")
    		->execute(array($id));
    }
    
    public function getRecommendations( $artist ) {
        $artist = strip_tags($artist);
        $artist = $this->pdo->query($artist);
        
        $ip = ip2long($_SERVER['REMOTE_ADDR']);
        
        $q = "SELECT * FROM stat WHERE artist = ?";
        $res = $this->pdo->prepare($q);
        $res->execute(array($artist));
        
        $ips = array();
        foreach ($res->fetchAll( \PDO::FETCH_OBJ ) as $song) {
            $ips[] = $song->ip;
        }
        
        $stat =array();
        if ( count($ips) ) {
            $q = "SELECT artist, SUM(cnt) as cnt FROM stat WHERE ip IN (".join(',', $ips).") GROUP BY artist ORDER BY cnt LIMIT 5";
            $res = $this->pdo->query($q);
            foreach ($res->fetchAll( \PDO::FETCH_OBJ ) as $s) {
				$stat[$s->artist] = $s->cnt;
			}
		} 

		return $stat;
	}
}