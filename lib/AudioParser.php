<?php
namespace Lib;

class AudioParser {

    public static function search($query, $offset = 0) {
        $cookie = VkLogin::getCookie();

        $post = array(
            'act' => 'search',
            'al' => '1',
            'gid' => '0',
            'id' => Config::getInstance()->getOption('vk', 'id'),
            'offset' => $offset,
            'q' => $query,
//            'count' => '5',
            'sort' => '2'
        );

        $answer = Curl::process( 
            'http://vkontakte.ru/audio',
            $cookie,
            false,
            http_build_query($post)
        );
        
        $matches = explode(
            '<div class="fl_l" style="width:31px;height:21px;">', 
            $answer
        );

        $songs = array();
        $songs_manager = new \Manager\Songs;
        foreach ($matches as $audioItem) {
            preg_match_all(
                '/<div class="duration fl_r">(.*)<\/div>/', 
                $audioItem, 
                $res
            );

            if ( ! isset( $res[1][0] ) ) continue;

            $song['duration'] = $res[1][0];

            preg_match_all(
                '<input type="hidden" id=".*?" value="(.*)?" />', 
                $audioItem, 
                $res
            );

            $songName = explode( ',', $res[1][0] );

            $song['url'] = $songName[0];

            preg_match_all(
                '/<div class="title_wrap">(.*)?<\/div>/', 
                $audioItem, 
                $res
            );

            $songname = preg_replace(
                '/\(.*\)/', 
                '', 
                strip_tags($res[1][0])
            );

            $songname = mb_convert_encoding(
                $songname, 
                Config::getInstance()->getOption('app', 'charset'), 
                'Windows-1251'
            );

			if (Config::getInstance()->getOption('app', 'fair_id') == 'yes') {
				$headers = \Lib\Curl::get_headers($song['url'], true);
				if (!isset($headers['Content-Length'])) {
					//this could be caused by expired token...invoke re-search or skip track?
					continue;
				}
				$song['id'] = md5($songname.$headers['Content-Length']);
			} else {
				$song['id'] = md5($songname.$song['duration']);
			}

            @list(
                $song['artist'], 
                $song['name']
            ) = explode(
                '-', 
                $songname
            );

            $songs[$song['id']] = $song;
            $songs_manager->addSong($song['id'], '', $song['name'], $song['artist']);
        }

        return $songs;
    }

}