<?php
namespace Lib;

class Storage {
	
	private $path;
	private static $instance;
	
	private function __construct() {
		$this->path = ROOT.'/web/assets/';
	}
	
	public static function getInstance() {
		return self::$instance?self::$instance:self::$instance = new \Lib\Storage;
	}
	
	/**
	 * Calculate the size of a directory by iterating its contents
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.2.0
	 * @link        http://aidanlister.com/2004/04/calculating-a-directories-size-in-php/
	 * @param       string   $directory    Path to directory
	 */
	private function slow_size() {
		$path = $this->path;
	    // Init
	    $size = 0;
	 
	    // Trailing slash
	    if (substr($path, -1, 1) !== DIRECTORY_SEPARATOR) {
	        $path .= DIRECTORY_SEPARATOR;
	    }
	 
	    // Sanity check
	    if (is_file($path)) {
	        return filesize($path);
	    } elseif (!is_dir($path)) {
	        return false;
	    }
	 
	    // Iterate queue
	    $queue = array($path);
	    for ($i = 0, $j = count($queue); $i < $j; ++$i)
	    {
	        // Open directory
	        $parent = $i;
	        if (is_dir($queue[$i]) && $dir = @dir($queue[$i])) {
	            $subdirs = array();
	            while (false !== ($entry = $dir->read())) {
	                // Skip pointers
	                if ($entry == '.' || $entry == '..') {
	                    continue;
	                }
	 
	                // Get list of directories or filesizes
	                $path = $queue[$i] . $entry;
	                if (is_dir($path)) {
	                    $path .= DIRECTORY_SEPARATOR;
	                    $subdirs[] = $path;
	                } elseif (is_file($path)) {
	                    $size += filesize($path);
	                }
	            }
	 
	            // Add subdirectories to start of queue
	            unset($queue[0]);
	            $queue = array_merge($subdirs, $queue);
	 
	            // Recalculate stack size
	            $i = -1;
	            $j = count($queue);
	 
	            // Clean up
	            $dir->close();
	            unset($dir);
	        }
	    }
	 
	    return $size;
	}
	
	public function size() {
		if ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {
			if (class_exists('COM', false)) {
				$obj = new \COM ( 'scripting.filesystemobject' );
				if (is_object($obj)) {
					$ref = $obj->getfolder ( $this->path );
			
					$result = $ref->size;
			
					$obj = null;
				} else {
					$result = $this->slow_size();
				}
			} else {
				$result = $this->slow_size();
			}
		} elseif (function_exists('exec')) {
			$s = exec("du -s ".$this->path, $ret);
			if ($ret) {
				$result = $this->slow_size();
			} else {
				$result = explode("\t",$s,2);
				$result = intval($result[0])*1024;
				if ($result[1] != $this->path) {
					$result = $this->slow_size();
				}
			}
		} else {
			$result = $this->slow_size();
		}
		return $result; 
	}
	
	public function findSongToDelete($size) {
		$path = $this->path;
	 
	    $queue = array($path);
	    for ($i = 0, $j = count($queue); $i < $j; ++$i)
	    {
	        $parent = $i;
	        if (is_dir($queue[$i]) && $dir = @dir($queue[$i])) {
	            $subdirs = array();
	            while (false !== ($entry = $dir->read())) {
	                if ($entry == '.' || $entry == '..') {
	                    continue;
	                }
	                $path = $queue[$i] . $entry;
	                if (is_dir($path)) {
	                    $path .= DIRECTORY_SEPARATOR;
	                    $subdirs[] = $path;
	                } elseif (is_file($path)) {
	                	if (filesize($path) >= $size) {
	                		return str_replace($this->path, '', $path);
	                	}
	                }
	            }
	 
	            unset($queue[0]);
	            $queue = array_merge($subdirs, $queue);
	 
	            $i = -1;
	            $j = count($queue);
	 
	            $dir->close();
	            unset($dir);
	        }
	    }
	 
	    return false;
	}
	// Работает неправильно, удаляет существующие треки. 
    // Ссылка на песню будет дохнуть всегда, так как, она временная
	public function save($data, $filename) {
		if (!$data) {
			return false;
		}
		if (false && $max_size = Config::getInstance()->getOption('storage', 'max_size')*1024*1024) {
			$data_size = strlen($data);
			$my_size = $this->size(); 
			if (($my_size + $data_size) > $max_size) {
				$need =  $data_size + $my_size - $max_size;
				if (\Lib\Config::getInstance()->getOption('app', 'logSongs')) {
					$songs_manager = new \Manager\Songs;
					if ($song =  $songs_manager->findSongToDelete($need)) {
						$this->delete($song->filename);
						$songs_manager->updateSong($song->song_id, array('filename' => ''));
					} else {
						return false;
					}
				} else {
					if ($song =  $this->findSongToDelete($need)) {
						$this->delete($song);
					} else {
						return false;
					}
				}
			}
		}
        
		file_put_contents($this->path.$filename, $data);
		return true;
	}
	
	public function make_name($filename) {
		$dirlvls = \lib\Config::getInstance()->getOption('storage', 'dir_lvls');
		$fname = '';
		for($i = 0; $i < $dirlvls; $i++) {
			$fname .= $filename[$i].DIRECTORY_SEPARATOR;
			if (!file_exists($this->path.$fname)) {
				mkdir($this->path.$fname);
			}
		}
		return $fname.substr($filename, $dirlvls);
	}
	
	public function delete($filename) {
		unlink($this->path.$filename);
	}
	
	public function exists($filename) {
		return file_exists($this->path.$filename);
	}
	
}