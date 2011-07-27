var Playlists = {
    reload:function() {
        Loading.on();
        $.ajax({
            url: './',
            data: {
                app:    'ajax',
                query:  'reloadPL'
            },
            type: 'post',
            
            success: function(html) {
                Loading.off();
                $('#opContainerPlaylists').html(html);
            }
        });
    },
    
    player_init: false,
    
    init: function() {
    	$('.jp-progress').css("width", ($('.jp-progress').parent().width()-150));
    	$(document).resize(function() {
    		$('.jp-progress').css("width", ($('.jp-progress').parent().width()-150));
    	});
    	if (!this.player_init) {
    		this.player_init = true;
	        $("#jquery_jplayer_1").jPlayer({
	        	swfPath: "./web/lib",
	        	solution: "flash, html",
	        	supplied: "mp3",
	
	        	ready: function() {
	        		//should we wait until it will say it is ready or leave it like this is ok?
	        	},
		        ended: function() {
		        	var next = Playlists.prevSong.next();
		        	if (next.size() == 0) {
		        		if (Playlists.prevSong.parents("#opContainerSongs").size()) {
		        			Search.loadNext(function() {
		        				next = Playlists.prevSong.next();
		    		        	Playlists.playSong( next );
		        			});
		        		}
		        	} else {
		        		Playlists.playSong( next );
		        	}
		        },
		        pause: function() {
		        	$(".jp-pause").hide();
		        	$(".jp-play").show();
		        },
		        play: function() {
		        	$(".jp-pause").show();
		        	$(".jp-play").hide();
		        },
		        mute: function() {
		        	$(".jp-mute").hide();
		        	$(".jp-unmute").show();
		        },
		        unmute: function() {
		        	$(".jp-mute").show();
		        	$(".jp-unmute").hide();
		        },
	        });
    	}
        $(".jp-progress").hover(function() {
        	$("#song-title").show();
        }, function() {
        	$("#song-title").hide();
        });
        $("#song-title").click(function(e) {
        	var offset;
        	if (typeof(e.offsetX) == 'undefined') {
        		offset = e.layerX;
        	} else {
        		offset = e.offsetX;
        	}
        	$("#jquery_jplayer_1").jPlayer("playHead", (100*offset)/$(this).width());
        });
        $('.op-link-song-del').unbind();
        $('.op-link-song-del').click(function() {
            if ( confirm( 'Уверен что хочешь удалить песню из плейлиста?' ) ) {
                var id = $(this).data('id');
                var plId = $(this).data('plid');
                
                $(this).parents('.op-song').remove();

                $.ajax({
                    url: './',
                    data: {
                        app:    'ajax',
                        query:  'delSongFromPL',
                        id:     id,
                        plId:   plId
                    },
                    type: 'post'
                });

            }
        });
        
        $('.op-link-pl-edit').unbind();
        $('.op-link-pl-edit').click(function() {
            var id = $(this).data('id');
            
            var name = prompt( 
                'Введи новое имя для плейлиста', 
                $( '#opLinkPlaylistName' + id ).html().trim()
            );

            if ( name && name.trim() ) {
                Loading.on();
                
                $.ajax({
                    url: './',
                    data: {
                        app:    'ajax',
                        query:  'editPL',
                        id:     id,
                        name:   name.trim()
                    },
                    dataType:   'json',
                    type:     'post',

                    success: function(data) {
                        Playlists.reload();
                    }
                });
            }
        });
        
        $('.op-link-pl-del').unbind();
        $('.op-link-pl-del').click(function() {
            if ( confirm('Уверен что хочешь удалить плейлист?') ) {
                var id = $(this).data('id');
                
                $(this).parents('.op-playlist').remove();
                
                $.ajax({
                    url: './',
                    data: {
                        app:    'ajax',
                        query:  'delPL',
                        id:     id
                    },
                    dataType:   'json',
                    type:       'post'
                });
            }
        });
        
        $('.op-link-pl-openhide').unbind();
        $('.op-link-pl-openhide').click(function() {
            var id = $(this).data('id');

            $('#opLinkPlaylistSongs'+id).toggleClass('op-hide');

            $(this).toggleClass('op-icon-open');
            $(this).toggleClass('op-icon-closed');

            $.ajax({
                url: './',
                data: {
                    app:    'ajax',
                    query:  'plStatus',
                    id:     id,
                    status: $(this).hasClass('op-icon-open')
                },
                type: 'post'
            });
        });
        
        $('#opLinkNewPlaylist').unbind();
        $('#opLinkNewPlaylist').click(function() {
            var name = prompt('Введи имя для нового плейлиста', 'Новый плейлист');

            if ( name && name.trim() ) {
                Loading.on();
                
                $.ajax({
                    url: './',
                    data: {
                        app:    'ajax',
                        query:  'addPL',
                        name:   name.trim()
                    },
                    dataType:   'json',
                    type:     'post',

                    success: function(data) {
                        if ( data.status ) {
                            Playlists.reload();
                        } else {
                            alert('Что-то пошло не так, попробуй еще раз.');
                            Loading.off();
                        }
                    }
                });
            }
        });
        
        $('.op-container-songbox, #opContainerSongs').sortable({
            connectWith: ".op-container-songbox",
            revert: 100,
            
            stop: function(event, ui) {
                var fromId = $(this).parents('.op-playlist').data('id');
                var song = $(ui.item);
                
                var toId = $(ui.item).parents('.op-playlist').data('id');
                if ( toId ) {
                    var delLink = $(ui.item).find('.op-song-del-span');
                    delLink.find('.op-link-song-del').data('plid', toId);
                    delLink.show();
                    
                    Loading.on();

                    var afterId = $(ui.item).prev().data('id');
                    if (undefined == afterId) {
                        afterId = null;
                    }

                    $.ajax({
                        url: './',
                        data: {
                            app:        'ajax',
                            query:      'moveSongToPL',
                            fromId:     fromId,
                            toId:       toId,
                            afterId:    afterId,
                            songData: {
                                id:         song.data('id'),
                                plid:       song.data('plid'),
                                name:       song.data('name'),
                                artist:     song.data('artist'),
                                url:        song.data('url'),
                                duration:   song.data('duration'),
                                position:   song.data('position')
                            }
                        },
                        dataType:   'json',
                        type:       'post',

                        success: function(data) {
                            Loading.off();
//                            Playlists.reload(); // @todo, make without
                        }
                    });

                } else {
                    $(this).sortable('cancel');
                }
                
            }
		}).disableSelection();
        
        $('.op-link-song-play').unbind();
        $('.op-link-song-play').click(function() {
            Playlists.playSong( $(this).parents('.op-song') );
        });
        
    },
    
    prevSong: null,
    
    playSong: function( par ) {
        var self = this;
        Loading.on();
        
        if ( null != self.prevSong ) {
            // detele prev song from server
            $.ajax({
                url: './',
                data: {
                    app:    'ajax',
                    query:  'deleteSong',
                    id:     self.prevSong.data('id')
                },
                type: 'post',
                dataType: 'json'
            });
        }
        self.prevSong = $(par);
        
        $.ajax({
            url: './',
            data: {
                app:    'ajax',
                query:  'getSong',
                url:    $(par).data('url'),
                artist: $(par).data('artist'),
                name:   $(par).data('name'),
                id:     $(par).data('id')
            },
            type:       'post',
            dataType:   'json',

            success: function(data) {
                if (data.url) {
                    $('.op-nowplaying').removeClass('op-nowplaying');
                    $('.op-song[data-id='+$(par).data('id')+']').addClass('op-nowplaying');
                    $("#jquery_jplayer_1").jPlayer("setMedia", {"mp3": data.url}).jPlayer("play");
                    var title = self.prevSong.data('artist') + ' - ' + self.prevSong.data('name');
                    $("#song-title").html(title);
                    $("title").html(title);
                } else {
                    alert("Что-то пошло не так:(");
                }
                
                Loading.off();
            }
        });
    }
}
