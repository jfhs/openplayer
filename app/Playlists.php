<?php

namespace App;

class Playlists extends \Lib\Base\App {

    public function init() {
        $playlistManager = new \Manager\Playlist;
        $this->playlists = $playlistManager->getUserPlaylists();
        
        $this->user = \Manager\User::getUser();
    }

}
