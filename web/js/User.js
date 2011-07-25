var User = {
    init: function() {
        this.setupEvent();
    },
    
    setupEvent: function() {
        $('#opLinkLogin').unbind();
        $('#opLinkLogin').click(function() {
            $('#opDialogLogin').dialog({
                modal: true
            });
        });
        
        $('#opLinkLogout').unbind();
        $('#opLinkLogout').click(function() {
            Loading.on();
            
            $.ajax({
                url: './',
                data: {
                    app:    'ajax',
                    query:  'logout'
                },
                type: 'post',
            
                success: function( html ) {
                    $('#opContainerUser').html(html);
            
                    Playlists.reload();
                }
            });
            return false;
        });
        
        $('#opDialogLogin form').unbind();
        $('#opDialogLogin form').submit(function() {
            Loading.on();
            $.ajax({
                url: './',
                data: {
                    app:    'ajax',
                    query:  'login',
                    user:   $(this).serialize()
                },
                type: 'post',
            
                success: function( html ) {
                    $('#opContainerUser').html(html);
                    $('#opDialogLogin').dialog("close");
                    $('#opDialogLogin').remove();
                    
                    Playlists.reload();
                }
            });
            return false;
        });
    }
    
}

User.init();