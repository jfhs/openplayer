<?php
namespace Manager;

class Playlist extends \Lib\Base\Manager {
    public function getUserPlaylists() {
        $user = User::getUser();
        if ( !$user ) {
            return array();
        }
        
        $userId = intval($user->id);
        
        $q = "SELECT * FROM pl WHERE userId = {$userId}";
        $res = $this->pdo->query( $q );

        if ( !$res ) {
            return array();
        }
        
        return $res->fetchAll( \PDO::FETCH_OBJ );
    }

    public function getSongs( $plId ) {
        $plId = intval($plId);
        
        $user = User::getUser();
        $userId = intval($user->id);
        
        $q = "SELECT * FROM pl INNER JOIN pl_song pls ON pl.id = pls.plId WHERE pl.userId = {$userId} AND pl.id = {$plId} ORDER BY pls.position";

        $res = $this->pdo->query( $q );

        return $res->fetchAll( \PDO::FETCH_OBJ );
    }

    public function addPL( $name ) {
        $name = strip_tags($name);
        $user = User::getUser();
        $userId = intval($user->id);
        $name = $this->pdo->quote($name);

        $q = "INSERT INTO pl VALUES (null, {$userId}, {$name})";
        $res = $this->pdo->exec( $q );

        return $this->pdo->lastInsertId();
    }

    private function checkIfMine( $id ) {
        $id = intval($id);
        
        $user = User::getUser();
        $userId = intval($user->id);
        
        
        $q = "SELECT * FROM pl WHERE id = {$id} AND userId = {$userId}";
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
        $id = intval($id);
        
        $q = "DELETE FROM pl_song WHERE plId = {$id}";
        $this->pdo->exec( $q );

        $q = "DELETE FROM pl WHERE id = {$id}";
        return $this->pdo->exec( $q );
    }

    public function editPL( $id, $name ) {
        $id = intval($id);
        $name = strip_tags($name);
        $name = $this->pdo->quote($name);
        
        $user = User::getUser();
        $userId = intval($user->id);
        
        $q = "UPDATE pl SET name = {$name} WHERE id = {$id} AND userId = {$userId}";
        return $this->pdo->exec( $q );
    }

    public function delSongFromPL( $id, $plId ) {
        if ( !$this->checkIfMine( $plId ) ) {
            return false;
        }
        
        $pos = $this->getSongPosition( $id, $plId );
        $this->downPositions( $pos, $plId );
        
        $id = $this->pdo->quote($id);
        $plId = intval($plId);
        
        $q = "DELETE FROM pl_song WHERE plId = {$plId} AND songId = {$id}";
        return $this->pdo->exec( $q );
    }
    
    private function downPositions( $afterPosition, $plId ) {
        $plId = intval($plId);
        
        $q = "UPDATE pl_song SET position = position - 1 WHERE plId = {$plId} AND position > {$afterPosition}";
        $this->pdo->exec( $q );
    }
    
    private function upPositions( $afterPosition, $plId ) {
        $plId = intval($plId);
        
        $q = "UPDATE pl_song SET position = position + 1 WHERE plId = {$plId} AND position > {$afterPosition}";
        $this->pdo->exec( $q );
    }

    public function moveSongToPL( $fromId, $toId, $afterId, $songData ) {
        $fromId = intval($fromId);
        $toId = intval($toId);
        $afterId = $this->pdo->quote($afterId);

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

        if ( !$fromId ) {
            $songInfo = serialize( $songData );
            $songInfo = $this->pdo->quote($songInfo);
            $songInfo = strip_tags($songInfo);
            
            $q = "INSERT INTO pl_song VALUES (null, '{$songData['id']}', $toId, {$songInfo}, {$newPosition})";
            $status = $this->pdo->exec( $q );
        } elseif( false && ($fromId != $toId) ) { //@todo
            $this->delSongFromPL( $songData['id'], $fromId );
        } else{
            $songId = $this->pdo->quote($songData['id']);
            $q = "UPDATE pl_song SET plId = {$toId}, position={$newPosition} WHERE songId={$songId} AND plId={$fromId}";
            $status = $this->pdo->exec( $q );
        }
        
        return $status;
    }
    
    private function getSongPosition( $songId, $plId ) {
        $plId = intval($plId);
        
        $q = "SELECT * FROM pl_song WHERE songId = {$songId} AND plId = $plId";
        $res = $this->pdo->query( $q );
        
        if (!$res) return 0;
        
        return ($res->fetchObject()->position) * 1;
    }
    
    public function updateSongInfo( $id, $songInfo ) {
        $id = $this->pdo->quote($id);
        
        $q = "SELECT * FROM pl_song WHERE songId = {$id}";
        $res = $this->pdo->query($q);
        
        foreach ( $res->fetchAll( \PDO::FETCH_OBJ ) as $song) {
            $songInfo = array_merge(
                unserialize($song->songInfo),
                $songInfo
            );
            
            $songInfo = serialize($songInfo);
            $songInfo = $this->pdo->quote($songInfo);
//            $songInfo = strip_tags($songInfo);
            
            $q = "UPDATE pl_song SET songInfo = {$songInfo} WHERE songId = '{$id}'";
            $this->pdo->exec($q);
        }
        
        return true;
    }
    
}
