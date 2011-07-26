<?php
namespace Lib;

class Storage {
	
	private $path;
	private static $instance;
	
	private function __construct() {
		$this->path = ROOT.'/assets/';
	}
	
	public static function getInstance() {
		return self::$instance?self::$instance:self::$instance = new \Lib\Storage;
	}
	
	public function size() { 
		$result = explode("\t",exec("du -s ".$this->path),2); 
		return ($result[1]==$this->path ? intval($result[0])*1024 : -1); 
	}
	
	public function save($data, $filename) {
		if ($max_size = Config::getInstance()->getOption('storage', 'max_size')*1024*1024) {
			$data_size = strlen($data);
			$my_size = $this->size(); 
			if (($my_size + $data_size) > $max_size) {
				$need =  $data_size + $my_size - $max_size;
				$songs_manager = new \Manager\Songs;
				if ($song =  $songs_manager->findSongToDelete($need)) {
					$this->delete($song->filename);
					$songs_manager->updateSong($song->song_id, array('filename' => ''));
				} else {
					return false;
				}
			}
		}
		file_put_contents($this->path.$filename, $data);
		return true;
	}
	
	public function delete($filename) {
		unlink($this->path.$filename);
	}
	
	public function exists($filename) {
		return file_exists($this->path.$filename);
	}
	
}