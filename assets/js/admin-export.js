jQuery(function($) {
    var progressBar = new PDL_Admin.ProgressBar($('.step-2 .export-progress'));

    var exportInProgress = false;
    var cancelExport = false;
    var lastState = null;

    var handleError = function(msg, res) {
        if (msg)
            $('.pdl-page-csv-export div.error p').text(msg);

        if (res && res.state) {
            $.ajax(ajaxurl, {data: { 'action': 'pdl-csv-export', 'state': state, 'cleanup': 1}, type: 'POST' });
        }

        cancelExport = true;
        exportInProgress = false;

        $('.step-1, .step-2, .step-3').hide();
        $('.pdl-page-csv-export div.error').show();
        $('.canceled-export').show();

        $('html, body').animate({ scrollTop: 0 }, 'medium');
    };

    var advanceExport = function(state) {
        if (!exportInProgress)
            return;

        lastState = state;

        if (cancelExport) {
            exportInProgress = false
            cancelExport = false;
            
            $('.step-2').fadeOut(function() {
                $('.canceled-export').fadeIn();
            });
            
            $.ajax(ajaxurl, {
                data: { 'action': 'pdl-csv-export', 'state': state, 'cleanup': 1 },
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                }
            });
            return;
        }
            
        $.ajax(ajaxurl, {
            data: { 'action': 'pdl-csv-export', 'state': state },
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (!res || res.error) {
                    exportInProgress = false;
                    handleError((res && res.error) ? res.error : null, res);
                    return;
                }

                $('.step-2 .listings').text(res.exported  + ' / ' + res.count);
                $('.step-2 .size').text(res.filesize);
                progressBar.set(res.exported, res.count);
                
                if (res.isDone) {
                    exportInProgress = false;
                    
                    $('.step-2').fadeOut(function() {
                        $('.step-3 .download-link a').attr('href', res.fileurl);
                        $('.step-3 .download-link a .filename').text(res.filename);
                        $('.step-3 .download-link a .filesize').text(res.filesize);                        
                        
                        $('.step-3').fadeIn(function() {
                            $('.step-3 .cleanup-link').hide();
                        })
                    } );

                } else {                
                    advanceExport(res.state);
                }
            },
            error: function() { handleError(); }
        });
    };
    
    
    $('form#pdl-csv-export-form').submit(function(e) {
        e.preventDefault();
        
        var data = $(this).serialize() + '&action=pdl-csv-export';
        $.ajax(ajaxurl, {
           data: data,
           type: 'POST',
           dataType: 'json',
           success: function(res) {
                if (!res || res.error) {
                    exportInProgress = false;
                    handleError((res && res.error) ? res.error : null, res);
                    return;
                }
            
               $('.step-1').fadeOut(function(){
                   exportInProgress = true;
                   $('.step-2 .listings').text('0 / ' + res.count);
                   $('.step-2 .size').text('0 KB');
                   
                   $('.step-2').fadeIn(function() {
                       advanceExport(res.state);
                   });
               });
           },
           error: function() { handleError(); }
        });
    });
    
    $('a.cancel-import').click(function(e) {
        e.preventDefault();
        cancelExport = true;
    });
    
    $('.step-3 .download-link a').click(function(e) {
        $('.step-3 .cleanup-link').fadeIn(); 
    });

    $('.step-3 .cleanup-link a').click(function(e) {
        e.preventDefault();
        $.ajax(ajaxurl, {
            data: { 'action': 'pdl-csv-export', 'state': lastState, 'cleanup': 1 },
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                location.href = '';
            }
        });
    });
});
