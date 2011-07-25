<?php
namespace Manager;

class Stat extends \Lib\Base\Manager {
    
    public function log( $artist ) {
        $ip = ip2long($_SERVER['REMOTE_ADDR']);
        
        $q = "SELECT * FROM stat WHERE artist = '{$artist}' AND ip = {$ip}";
        $res = $this->pdo->query($q);
        
        $q = "";
        if ( $res->fetchObject() ) {
            $q = "UPDATE stat SET cnt = cnt + 1, updatedAt = NOW() WHERE artist = '{$artist}' AND ip = {$ip}";
        } else {
            $q = "INSERT INTO stat VALUES (null, {$ip}, '{$artist}', 1, NOW(), NOW())";
        }
        
        return $this->pdo->exec($q);
    }
    
    public function getRecommendations( $artist ) {
        $ip = ip2long($_SERVER['REMOTE_ADDR']);
        
        $q = "SELECT * FROM stat WHERE artist = '{$artist}'";
        $res = $this->pdo->query($q);
        
        $ips = array();
        foreach ($res->fetchAll( \PDO::FETCH_OBJ ) as $song) {
            $ips[] = $song->ip;
        }
        
        $stat =array();
        if ( count($ips) ) { // @todo !!!!!!!!!!! sum count group by limit 5
            $q = "SELECT * FROM stat WHERE ip IN (".join(',', $ips).")";
            $res = $this->pdo->query($q);
            
            foreach ($res->fetchAll( \PDO::FETCH_OBJ ) as $song) {
                if ( !isset($stat[$song->artist]) ) {
                    $stat[$song->artist] = 0;
                }
                
                $stat[$song->artist] += $song->cnt;
            }
            
            arsort($stat);
            unset($stat[$artist]);
            
            $stat = array_slice($stat, 0, 5);
        } 
        
        return $stat;
    }
    
    
}