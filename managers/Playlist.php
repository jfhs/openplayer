<?php
namespace Manager;

class Playlist extends \Lib\Base\Manager {
    public function getUserPlaylists() {
        $user = User::getUser();
        if ( !$user ) {
            return array();
        }
        
        $q = "SELECT * FROM pl WHERE userId = {$user->id}";
        $res = $this->pdo->query( $q );

        if ( !$res ) {
            return array();
        }
        
        return $res->fetchAll( \PDO::FETCH_OBJ );
    }

    public function getSongs( $plId ) {
        $user = User::getUser();
        $q = "SELECT * FROM pl INNER JOIN pl_song pls ON pl.id = pls.plId WHERE pl.userId = {$user->id} AND pl.id = {$plId} ORDER BY pls.position";

        $res = $this->pdo->query( $q );

        return $res->fetchAll( \PDO::FETCH_OBJ );
    }

    public function addPL( $name ) {
        $user = User::getUser();

        $q = "INSERT INTO pl VALUES (null, {$user->id},'{$name}')";
        $res = $this->pdo->exec( $q );

        return $this->pdo->lastInsertId();
    }

    private function checkIfMine( $id ) {
        // xss protection
        $user = User::getUser();
        $q = "SELECT * FROM pl WHERE id = {$id} AND userId = {$user->id}";
        $res = $this->pdo->query( $q );
        
        if ( $res->fetchObject() ) {
            return true;
        }
        
        return false;
    }
    
    public function delPL( $id ) {
        if ( !$this->checkIfMine( $id ) ) {
            return false;
        }
        
        $q = "DELETE FROM pl_song WHERE plId = {$id}";
        $this->pdo->exec( $q );

        $q = "DELETE FROM pl WHERE id = {$id}";
        return $this->pdo->exec( $q );
    }

    public function editPL( $id, $name ) {
        $user = User::getUser();

        $q = "UPDATE pl SET name = '{$name}' WHERE id = {$id} AND userId = {$user->id}";
        return $this->pdo->exec( $q );
    }

    public function delSongFromPL( $id, $plId ) {
        if ( !$this->checkIfMine( $plId ) ) {
            return false;
        }
        
        $pos = $this->getSongPosition( $id, $plId );
        $this->downPositions( $pos, $plId );
        
        $q = "DELETE FROM pl_song WHERE plId = {$plId} AND songId = '{$id}'";
        return $this->pdo->exec( $q );
    }
    
    private function downPositions( $afterPosition, $plId ) {
        $q = "UPDATE pl_song SET position = position - 1 WHERE plId = {$plId} AND position > {$afterPosition}";
        $this->pdo->exec( $q );
    }
    
    private function upPositions( $afterPosition, $plId ) {
        $q = "UPDATE pl_song SET position = position + 1 WHERE plId = {$plId} AND position > {$afterPosition}";
        $this->pdo->exec( $q );
    }

    public function moveSongToPL( $fromId, $toId, $afterId, $songData ) {
        if ( !$this->checkIfMine( $toId ) || ($fromId && !$this->checkIfMine( $fromId )) ) {
            return false;
        }
        
        # positioning
        if ( $fromId ) {
            $oldPosition = $this->getSongPosition( $songData['id'], $fromId );
            $this->downPositions( $oldPosition, $fromId );
        }
        
        $newPosition = 1;
        if ( $afterId ) {
            $newPosition = $this->getSongPosition( $afterId, $toId );
            $newPosition++;
        }
        
        $this->upPositions( $newPosition-1, $toId );
        # /positioning

        if ( !$fromId && !$isExits ) {
            $songInfo = serialize( $songData );
            
            $q = "INSERT INTO pl_song VALUES (null, '{$songData['id']}', $toId, '{$songInfo}', {$newPosition})";
            $status = $this->pdo->exec( $q );
        } elseif( $isExits && ($fromId != $toId) ) {
            $this->delSongFromPL( $songData['id'], $fromId );
        } else{
            $q = "UPDATE pl_song SET plId = {$toId}, position = {$newPosition} WHERE songId='{$songData['id']}' AND plId = {$fromId}";
            $status = $this->pdo->exec( $q );
        }
        
        return $status;
    }
    
    private function getSongPosition( $songId, $plId ) {
        $q = "SELECT * FROM pl_song WHERE songId = '{$songId}' AND plId = $plId";
        $res = $this->pdo->query( $q );
        
        if (!$res) return 0;
        
        return ($res->fetchObject()->position) * 1;
    }
    
    public function updateSongInfo( $id, $songInfo ) {
        $q = "SELECT * FROM pl_song WHERE songId = '{$id}'";
        $res = $this->pdo->query($q);
        
        foreach ( $res->fetchAll( \PDO::FETCH_OBJ ) as $song) {
            $songInfo = array_merge(
                unserialize($song->songInfo),
                $songInfo
            );
            
            $songInfo = serialize($songInfo);
            
            $q = "UPDATE pl_song SET songInfo '{$songInfo}' WHERE songId = '{$id}'";
            $this->pdo->exec($q);
        }
        
        return true;
    }
    
}
