jQuery( function( $ ) {
    var reCAPTCHA_Handler = function() {
        this.max_attempts = 20;
        this.attempts = 0;
        this.max_delay = 1500;
        this.timeout = false;
    }

    $.extend( reCAPTCHA_Handler.prototype, {
        render_widgets: function() {
            if ( this.timeout )
                clearTimeout( this.timeout );

            $( '.pdl-recaptcha' ).each(function(i, v) {
                var $captcha = $(v);

                if ( $captcha.data( 'pdl-recaptcha-enabled' ) )
                    return;

                grecaptcha.render( $captcha[0], { 'sitekey': $captcha.attr( 'data-key' ),
                                                  'theme': 'light' } );
                $captcha.data( 'pdl-recaptcha-enabled', true );
            });
        },

        render_widgets_when_ready: function() {
            if ( typeof grecaptcha !== 'undefined' )
                return this.render_widgets();

            var self = this;
            this.timeout = setTimeout( function() { self.render_widgets_when_ready() }, this.max_delay * Math.pow( this.attempts / this.max_attempts, 2 ) );
            this.attempts++;
        }
    });

    var pdl_rh = new reCAPTCHA_Handler();
    pdl_rh.render_widgets_when_ready();

    window.pdl_recaptcha_callback = function() {
        if ( typeof pdl_rh == 'undefined' )
            pdl_rh = new reCAPTCHA_Handler();
        pdl_rh.render_widgets();
    }

    // Handle submit reCAPTCHA.
    $( window ).on( 'pdl_submit_refresh', function( event, submit, section_id ) {
        pdl_rh.render_widgets();
    } );
} );
