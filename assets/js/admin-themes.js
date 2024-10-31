jQuery(function($) {
    $( '#pdl-admin-page-themes-install #begin-theme-upload' ).prop( 'disabled', true );
    $( '#pdl-admin-page-themes-install input[name="themezip"]' ).change( function( e ) {
        var v = $( this ).val();

        if ( v )
            $( '#pdl-admin-page-themes-install #begin-theme-upload' ).prop( 'disabled', false );
    } );


    $( '#pdl-admin-page-themes .license-activation input[type="button"]' ).click( function() {
        var activate = $( this ).is( '[name="activate"]' );

        var $form = $( this ).parents( '.license-activation' );
        var $license = $( 'input[name="license"]', $form );
        var $msg = $( '.status-message', $form );
        var data = { 'nonce': $( this ).attr( 'data-nonce' ),
                     'theme': $( this ).attr( 'data-theme' ) };

        if ( activate ) {
            data['action'] = 'pdl-themes-activate-license';
            data['license'] = $license.val();
        } else {
            data['action'] = 'pdl-themes-deactivate-license';
        }

        $msg.removeClass( 'ok error' );
        $msg.html( $( this ).attr( 'data-l10n' ) );

        $.post( ajaxurl, data, function( res ) {
            if ( ! res.success ) {
                $msg.hide()
                    .html( res.error )
                    .removeClass( 'ok' )
                    .addClass( 'error' )
                    .show();
                return;
            }

            $msg.hide()
                .html( res.message )
                .removeClass( 'error' )
                .addClass( 'ok' )
                .show();

            if ( activate ) {
                $( 'input[name="activate"]', $form ).hide();
                $( 'input[name="deactivate"]', $form ).show();
                $license.prop( 'readonly', true );
            } else {
                $license.prop( 'readonly', false ).val( '' );
                $( 'input[name="deactivate"]', $form ).hide();
                $( 'input[name="activate"]', $form ).show();
            }
        }, 'json' );
    } );

    $( '#pdl-admin-page-themes .pdl-theme .update-link' ).click( function( e ) {
        e.preventDefault();

        var $theme = $( this ).parents( '.pdl-theme' );
        var $info = $( '.pdl-theme-update-info', $theme );
        var $msg = $( '.update-message', $info );

        $msg.html( $info.attr( 'data-l10n-updating' ) );

        $.post( ajaxurl, {
            'action': 'pdl-themes-update',
            '_wpnonce': $( this ).attr( 'data-nonce' ),
            'theme': $( this ).attr( 'data-theme-id' ) }, function( res ) {
                if ( ! res.success ) {
                    $info.addClass( 'update-error' );
                    $msg.html( res.error );
                    return;
                }

                var $html = $( res.data.html );
                $( '.pdl-theme-details-wrapper', $theme ).replaceWith( $( '.pdl-theme-details-wrapper', $html ) );
                $info.removeClass( 'update-available' ).addClass( 'theme-updated' );
                $msg.html( $info.attr( 'data-l10n-updated' ) );
        }, 'json' );

    } );

});

