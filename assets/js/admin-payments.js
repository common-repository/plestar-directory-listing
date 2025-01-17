jQuery(function($) {
    $( '#pdl-admin-page-payments-details .postbox .handlediv.button-link' ).click( function(e) {
        var $p = $( this ).parents( '.postbox' );
        $p.toggleClass( 'closed' );
        $( this ).attr( 'aria-expanded', ! $p.hasClass( 'closed' ) );
    });

    $( '#pdl-admin-payment-info-box input[name="payment[created_on_date]"]' ).datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $( '#pdl-payment-notes-add' ).click(function(e) {
        e.preventDefault();
        var $note = $( 'textarea[name="payment_note"]' );

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'pdl_admin_ajax',
                handler: 'payments__add_note',
                payment_id: $( this ).data( 'payment-id' ),
                note: $note.val()
            },
            success: function( res ) {
                if ( ! res.success )
                    return;

                $( '#pdl-payment-notes .no-notes' ).hide();
                $( '#pdl-payment-notes' ).prepend( res.data.html );

                $note.val('');
            }
        });

        // var border_color = $('#edd-payment-note').css('border-color');
		// 			$('#edd-payment-note').css('border-color', 'red');
		// 			setTimeout( function() {
		// 				$('#edd-payment-note').css('border-color', border_color );
		// 			}, 500 );
    });

    $( document ).on( 'click', '.pdl-payment-note .pdl-admin-delete-link', function( e ) {
        e.preventDefault();

        var url = $( this ).attr( 'href' );
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function( res ) {
                if ( ! res.success )
                    return;

                $( '.pdl-payment-note[data-id="' + res.data.note.id + '"]' ).remove();

                if ( 0 == $( '.pdl-payment-note' ).length )
                    $( '#pdl-payment-notes .no-notes' ).show();
            }
        });
    } );

});
