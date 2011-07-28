<?php
namespace Manager;

class User extends \Lib\Base\Manager {
	const SESS_KEY = 'user';

	public static function getUser() {
		if ( !isset($_SESSION[ 'op' ]) ) {
			return false;
		}
		
		return $_SESSION[ 'op' ][ User::SESS_KEY ];
	}
    
    public function getHistory() {
        return $_SESSION[ 'op' ][ User::SESS_KEY ]->settings['history'];
    }

    public function updateSettings( $settings ) {
        $user = User::getUser();
		$userId = intval($user->id);
        
        $_SESSION[ 'op' ][ User::SESS_KEY ]->settings = $settings;
        
        $serializedSettings = json_encode($settings);
		
		$res = $this->pdo->prepare("UPDATE user SET settings = ? WHERE id = ?");
		$res->execute(array($serializedSettings, $userId));
        
        return $res;
    }


    public function updatePLSettings ( $plId, $status ) {
		$plId = intval($plId);
		$status = 1*$status;

		$settings = $user->settings;
		$settings['pl'][$plId] = $status;
		
		$res = $this->updateSettings($settings);
		return $res;
	}

	public static function getUserOption( $key ) {
		$user = self::getUser();
		return isset( $user->$key )
			? $user->$key
			: null;
	}

	public static function isLoggedIn() {
		return (boolean) self::getUser();
	}
	
	public function login ( $login, $password = null ) {
		$login = strip_tags($login);
		
		$loginQ = $this->pdo->quote($login);
		if ($password) {
			$password = strip_tags($password);
			$passwordMd5 = $this->pdo->quote(md5($password));
		}
		
		if ( !$login ) return false;
		
		$q = "SELECT * FROM user WHERE login = {$loginQ}";
		
		if ( $password ) {
			$q .= " AND password = {$passwordMd5} ";
		} else {
			$q .= " AND password IS NULL";
		}
		$res = $this->pdo->query($q);
		$user = $res->fetchObject();
		
		if ( !$user ) {
			$q = "INSERT INTO user VALUES (null, {$loginQ}, ". ( $password ? $passwordMd5 : "null" ) . ", null, null)";
			$res = $this->pdo->exec($q);
			
			if ($res) {
				return $this->login( $login, $password );
			} else {
				return false;
			}
		}
		
		return $this->store($user);
	}
	
	public function store( $user ) {
		$this->generateSessionKey( $user );
		
		unset( $user->password );
		unset( $user->sessionKey );
		$user->settings = (array) json_decode($user->settings);
		@$user->settings['pl'] = (array) $user->settings['pl'];
		
		return $_SESSION[ 'op' ][ User::SESS_KEY ] = $user;
	}
	
	private function generateSessionKey( $user ) {
		$userId = intval($user->id);
		
		$key = md5( microtime(true) . $user->id . 'secret' );
		
		setcookie('sessionKey', $key, time()+(60*60*60*24*14) , '/');
		
		$q = "UPDATE user SET sessionKey = '{$key}' WHERE id = {$userId}";
		$this->pdo->exec($q);
	}


	public function logout() {
		unset($_SESSION['op']);
		setcookie('sessionKey', null, time() , '/');
	}
	
	public function autologin() {
		if ( !User::isLoggedIn() && isset($_COOKIE[ 'sessionKey' ]) ) {
			$q = "SELECT * FROM user WHERE sessionKey = ?";
			$res = $this->pdo->prepare($q);
			$res->execute(array($_COOKIE['sessionKey']));
			
			$user = $res->fetchObject();
			
			if ( $user ) {
				self::store( $user );
			}
		}
	}
    
    public function logHistory( $q ) {
        $user = User::getUser();
        $settings = $user->settings;
		$settings['history'][] = $q;
        
        if ( count( $settings['history'] ) > \Lib\Config::getInstance()->getOption('app', 'historyLength') ) {
            array_shift($settings['history']);
        }
		
		$res = $this->updateSettings( $settings );
    }
        
}