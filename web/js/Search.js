var Search = {
//    data: null,
    
    artistClick: function() {
        $('.op-atrist').unbind();
        $('.op-atrist').click(function() {
            var artist = $(this).html();
            $('.op-form-search form input[type=text]').val(artist);
            $('.op-form-search form').submit();
        });
    },
    
    init: function() {
    	$('.op-form-search input[type=text]').autocomplete({
    			minLength: 0,
    			source: "?app=ajax&query=suggest",
    	});
        $('.op-form-search form').submit(function() {
            var data = $(this).serialize();
//            Search.data = data;
            
            Search.loadSongs(data);
            
            
            return false;
        });
    
        $(document).ready(function() {
            Search.artistClick();
        });
        
        Search.pagerEvents();
    },
    
    pagerEvents: function() {
        $('#opPagerSongsPrev').click(function() {
            Search.loadSongs($(this).attr('href').replace('?',''));
            return false;
        });
        
        $('#opPagerSongsNext').click(function() {
            Search.loadSongs($(this).attr('href').replace('?',''));
            return false;
        });
    },
    
    loadSongs: function(data) {
        Loading.on();
        
        var query = data;
        
        location.hash = "!?"+query;
        
        $.ajax({
            url: './',
            data: query+'&app=ajax&query=search',
            type: 'post',
            dataType:   'json',

            success: function(data) {
            	if (!(/offset=[0-9]+/.test(query))) {
            		$('#opSongsPlace').html('');
            		$('#opSongsPlace').append(data.recomendation);
            	}
            	if ($("#opContainerSongs").size()) {
            		$('#opContainerSongs').append(data.songs);	
            	} else {
            		$('#opSongsPlace').append(data.songs);
            	}
                Search.pagerEvents();
                Playlists.init();
                
                Search.artistClick();
                
                Loading.off();
            }

        });
    }
    
}

Search.init();