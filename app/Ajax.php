<?php
namespace App;

use \Lib\Helper,
	\Lib\Request,
	\Lib\Response,
    \Manager\Playlist,
    \Manager\User,
    \Manager\Suggest;

class Ajax extends \Lib\Base\App {
    public function init() {
    	define('AJAX', true);
        switch (Request::get('query')) {
            case 'search':
                if ( !Request::get('offset') ) {
                    \Manager\User::create()
                        ->logHistory( Request::get('q') );
                }
                
                echo $this->render('songs');
                die;
                break;
            
            case 'suggest':
            	$suggest = new Suggest;
            	echo json_encode($suggest->get(Request::get('term', '')));
            	die;
                
            case 'addPL':
                $playlistsManager = new Playlist;
                $status = $playlistsManager->addPL(
                    Request::get('name')
                );

                echo json_encode(array(
                    'status' => $status
                ));
                die;
                break;
            
            case 'delPL':
                $playlistsManager = new Playlist;
                $status = $playlistsManager->delPL(Request::get('id'));

                echo json_encode(array(
                    'status' => $status
                ));
                die;
                break;
            
            case 'editPL':
                $playlistsManager = new Playlist;
                $status = $playlistsManager->editPL(
                    Request::get('id'), 
                    Request::get('name')
                );

                echo json_encode(array(
                    'status' => $status
                ));
                die;
                break;
            
            case 'plStatus':
                $userManager = new User;
                $status = $userManager->updatePLSettings(
                    Request::get('id'), 
                    Request::get('status')
                );

                echo json_encode(array(
                    'status' => $status
                ));
                die;
                break;
            
            case 'moveSongToPL':
                $playlistsManager = new Playlist;
                $status = $playlistsManager->moveSongToPL(
                    Request::get('fromId'), 
                    Request::get('toId'), 
                    Request::get('afterId'),
                    Request::get('songData')
                );

                echo json_encode(array(
                    'status' => $status
                ));
                die;
                break;
            
            case 'delSongFromPL':
                $playlistsManager = new Playlist;
                $status = $playlistsManager->delSongFromPL(
                    Request::get('id'), Request::get('plId')
                );

                echo json_encode(array(
                    'status' => $status
                ));
                die;
                break;
            
            case 'reloadPL':
                echo $this->render('playlists');
                die;
                break;

            case 'login':
                $request = Request::get('user');
                parse_str($request, $request);
                
                $usermanager = new User;
                $user = $usermanager->login(
                    $request['login'], $request['password']
                );
                
                echo $this->render('user');
                die;
                break;

            case 'logout':
                $usermanager = new User;
                $usermanager->logout();

                echo $this->render('user');
                die;
                break;
            
            case 'deleteSong':
            	if (\Lib\Config::getInstance()->getOption('client', 'deleteSong')) {
                    $storage = \Lib\Storage::getInstance();
                    $path = $storage->makeName(Request::get('id').".mp3"); 
                    
                    if ( !$storage->exists( $path ) ) {
	                    $storage->delete( $path );
	                }
            	}
                die;
                break;
			
            case 'dl':
            case 'getSong':
                # stat
                if ( Request::get('artist') ) {
                    $statManager = new \Manager\Stat;
                    
                    $statManager->log(
                        Request::get('artist')
                    );
                }
                # /stat
				
                $id = Request::get('id');
                
                $storage = \Lib\Storage::getInstance();
               	$path = $storage->makeName("{$id}.mp3"); 
                
                if ( !$storage->exists( $path ) ) {
                    $url = Request::get('url');
                    
                    $headers = get_headers($url);
                    $status = substr($headers[0], 9, 3);
                    
                    if ('404' == $status) {
                        $song = reset(\Lib\AudioParser::search (
                            Request::get('artist') . ' - ' . Request::get('name')
                        ));

                        $url = $song['url'];
                        $playlistsManager = new Playlist;
                        $playlistsManager->updateSongInfo(
                            $id, 
                            array(
                                'url' => $url
                            )
                        );
                    }
                    
                    $song = file_get_contents($url);
                    $result = $storage->save($song, $path);
                    
                    if ( $result && \Lib\Config::getInstance()->getOption('app', 'logSongs') ) {
                        $songsManager = new \Manager\Songs;
                        
                    	$songsManager->updateSong(
                            $id, 
                            array(
                                'filename' => $path, 
                                'size' => strlen($song)
                            )
                        );
                    }
                }
				
                # stat
                if ( \Lib\Config::getInstance()->getOption( 'app', 'logSongs' ) ) {
					if ( !isset($statManager) ) {
						$statManager = new \Manager\Stat; 
					}
                    
					$statManager->logSong($id);
                }
				# /stat
				
				if (Request::get('query') == 'dl') {
					$fname = Helper::makeValidFname(
						Request::get('artist') . ' - ' . Request::get('name')
					).'.mp3';
					$path = './web/assets/'.$path;
					Response::sendfile(array(
						'filepath' => $path,
						'filename' => $fname,
					));
				}
				
                echo json_encode(array(
                    'url' => "./web/assets/{$path}",
                	'status' => $result
                ));
                die;
                break;

            default:
                break;
        }
    }

}
