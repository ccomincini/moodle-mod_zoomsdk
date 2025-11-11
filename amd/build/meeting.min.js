define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {
    
    return {
        init: function(params) {
            console.log('Zoom SDK Meeting Module initialized', params);
            
            var self = this;
            
            $('#join-meeting-btn').on('click', function(e) {
                e.preventDefault();
                $(this).prop('disabled', true).text('Connessione in corso...');
                self.joinMeeting(params.zoomsdkid);
            });
        },
        
        joinMeeting: function(zoomsdkid) {
            var self = this;
            
            $.ajax({
                url: M.cfg.wwwroot + '/mod/zoomsdk/generate_signature.php',
                type: 'POST',
                data: {
                    zoomsdkid: zoomsdkid,
                    sesskey: M.cfg.sesskey
                },
                dataType: 'json',
                success: function(data) {
                    console.log('Signature received:', data);
                    self.initSDK(data);
                },
                error: function(xhr) {
                    console.error('Signature error:', xhr);
                    notification.exception({message: 'Errore connessione Zoom'});
                    $('#join-meeting-btn').prop('disabled', false).text('Entra nel Meeting');
                }
            });
        },
        
        initSDK: function(config) {
            console.log('Initializing Zoom SDK (EMBEDDED MODE)...');
            
            // Nascondi bottone, mostra container
            $('#join-meeting-btn').hide();
            $('#zmmtg-root').show();
            
            // Prepara SDK
            ZoomMtg.setZoomJSLib('https://source.zoom.us/3.9.0/lib', '/av');
            ZoomMtg.preLoadWasm();
            ZoomMtg.prepareWebSDK();
            
            ZoomMtg.init({
                leaveUrl: config.leaveUrl,
                disableInvite: true, // Non serve invitare altri
                disableRecord: true, // Recording gestito da host
                success: function() {
                    console.log('SDK initialized successfully');
                    
                    ZoomMtg.join({
                        signature: config.signature,
                        sdkKey: config.sdkKey,
                        meetingNumber: config.meetingNumber,
                        userName: config.userName, // FORZATO da Moodle
                        userEmail: config.userEmail, // FORZATO da Moodle
                        passWord: config.passWord,
                        
                        success: function(res) {
                            console.log('✅ Joined meeting successfully (EMBEDDED)', res);
                        },
                        error: function(error) {
                            console.error('❌ Join error:', error);
                            notification.exception({message: 'Errore join: ' + error.reason});
                            $('#zmmtg-root').hide();
                            $('#join-meeting-btn').show().prop('disabled', false).text('Riprova');
                        }
                    });
                },
                error: function(error) {
                    console.error('❌ SDK init error:', error);
                    notification.exception({message: 'Errore SDK: ' + error.reason});
                }
            });
        }
    };
});
