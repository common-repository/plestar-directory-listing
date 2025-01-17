jQuery(function($) {
    var pdl_settings_dep_handling = {
        init: function() {
            var self = this;

            self.watch = {};
            self.requirements = {};

            $( '.pdl-settings-setting[data-requirements][data-requirements!=""]' ).each(function() {
                var setting_id = $(this).data('setting-id');
                var reqs = $( this ).data( 'requirements' );

                self.requirements[ setting_id ] = reqs;

                $.each( reqs, function(i, req) {
                    var rel_setting_id = req[0];

                    if ( 'undefined' === typeof self.watch[rel_setting_id] ) {
                        self.watch[ rel_setting_id ] = [];
                    }

                    self.watch[ rel_setting_id ].push(setting_id);
                } );
            });

            $.each( self.watch, function( setting_id, affected_settings ) {
                $( '[name="pdl_settings[' + setting_id + ']"], [name="pdl_settings[' + setting_id + '][]"]' ).change(function(){
                    $.each( affected_settings, function(i, v) {
                        self.check_requirements( v );
                    } );
                });
            } );

            $.each( self.requirements, function( setting_id, reqs ) {
                self.check_requirements( setting_id );
            } );
        },

        check_requirements: function( setting_id ) {
            var reqs     = this.requirements[ setting_id ];
            var $setting = $( '#pdl-settings-' + setting_id );
            var $row     = $setting.parents( 'tr' );
            var passes   = true;

            for ( var i = 0; i < reqs.length; i++ ) {
                var req_name = reqs[ i ][0].replace( '!', '' );
                var not      = ( -1 !== reqs[ i ][0].indexOf( '!' ) );
                var value    = reqs[ i ][1];

                // Obtain updated value (if possible).
                var $rel_setting = $( '#pdl-settings-' + req_name );
                if ( $rel_setting.length > 0 ) {
                    if ( $rel_setting.parents( 'tr' ).hasClass( 'pdl-setting-disabled' ) ) {
                        value = false;
                    } else {
                        var $field = $rel_setting.find( '[name="pdl_settings[' + req_name + ']"]:checked, [name="pdl_settings[' + req_name + '][]"]:checked' );
                        value = $field.length > 0;
                    }
                }

                passes = ( ( not && ! value ) || value );

                if ( ! passes ) {
                    break;
                }
            }

            if ( passes ) {
                $row.removeClass( 'pdl-setting-disabled' );
            } else {
                $row.addClass( 'pdl-setting-disabled' );
            }

            // Propagate.
            if ( 'undefined' !== typeof this.watch[ setting_id ] ) {
                $setting.find( '[name="pdl_settings[' + setting_id + ']"], [name="pdl_settings[' + setting_id + '][]"]' ).trigger( 'change' );
            }
        }
    };
    pdl_settings_dep_handling.init();

    /**
     * License activation/deactivation.
     */
    var pdl_settings_licensing = {
        init: function() {
            var self = this;

            if ( 0 == $( '.pdl-settings-type-license_key' ).length ) {
                return;
            }

            $( '.pdl-license-key-activate-btn, .pdl-license-key-deactivate-btn' ).click(function(e) {
                e.preventDefault();

                var $button  = $(this);
                var $setting = $(this).parents( '.pdl-license-key-activation-ui' );
                var $msg     = $setting.find( '.pdl-license-key-activation-status-msg' );
                var $spinner = $setting.find( '.spinner' );
                var activate = $(this).is( '.pdl-license-key-activate-btn' );
                var $field   = $setting.find( 'input.pdl-license-key-input' );
                var data     = $setting.data( 'licensing' );

                $msg.hide();
                $button.data( 'original_label', $(this).val() );
                $button.val( $(this).data( 'working-msg' ) );
                $button.prop( 'disabled', true );

                if ( activate ) {
                    data['action'] = 'pdl_activate_license';
                } else {
                    data['action'] = 'pdl_deactivate_license';
                }

                data['license_key'] = $field.val();

                $.post(
                    ajaxurl,
                    data,
                    function( res ) {
                        if ( res.success ) {
                            $msg.removeClass( 'status-error' ).addClass( 'status-success' ).html( res.message ).show();

                            if ( activate ) {
                                var classes = $setting.attr( 'class' ).split( ' ' ).filter( function( item ) {
                                    var className = item.trim();

                                    if ( 0 === className.length ) {
                                        return false;
                                    }

                                    if ( className.match( /^pdl-license-status/ ) ) {
                                        return false;
                                    }

                                    return true;
                                } );

                                classes.push( 'pdl-license-status-valid' );

                                $setting.attr( 'class', classes.join( ' ' ) );
                            } else {
                                $setting.removeClass( 'pdl-license-status-valid' ).addClass( 'pdl-license-status-invalid' );
                            }

                            $field.prop( 'readonly', activate ? true : false );
                        } else {
                            $msg.removeClass( 'status-success' ).addClass( 'status-error' ).html( res.error ).show();
                            $setting.removeClass( 'pdl-license-status-valid' ).addClass( 'pdl-license-status-invalid' );
                            $field.prop( 'readonly', false );
                        }

                        $button.val( $button.data( 'original_label' ) );
                        $button.prop( 'disabled', false );
                    },
                    'json'
                );
            });
        }
    };
    pdl_settings_licensing.init();

    /**
     * E-Mail template editors.
     */
    var pdl_settings_email = {
        init: function() {
            var self = this;

            $( '.pdl-settings-email-preview, .pdl-settings-email-edit-btn' ).click(function(e) {
                e.preventDefault();

                var $email = $( this ).parents( '.pdl-settings-email' );
                $( this ).hide();
                $email.find( '.pdl-settings-email-editor' ).show();
            });

            $( '.pdl-settings-email-editor .cancel' ).click(function(e) {
                e.preventDefault();

                var $email = $( this ).parents( '.pdl-settings-email' );
                var $editor = $email.find( '.pdl-settings-email-editor' );

                // Add-new editor.
                if ( $email.parent().is( '#pdl-settings-expiration-notices-add' ) ) {
                    $email.hide();
                    $( '#pdl-settings-expiration-notices-add-btn' ).show();
                    return;
                }

                // Sync editor with old values.
                var subject = $editor.find( '.stored-email-subject' ).val();
                var body = $editor.find( '.stored-email-body' ).val();
                $editor.find( '.email-subject' ).val( subject );
                $editor.find( '.email-body' ).val( body );

                if ( $email.hasClass( 'pdl-expiration-notice-email' ) ) {
                    var event = $editor.find( '.stored-notice-event' ).val();
                    var reltime = $editor.find( '.stored-notice-relative-time' ).val();

                    $editor.find( '.notice-event' ).val( event );
                    $editor.find( '.notice-relative-time' ).val( reltime );

                    if ( ! reltime ) {
                        reltime = '0 days';
                    }

                    $editor.find( 'select.relative-time-and-event' ).val( event + ',' + reltime );
                }

                // Hide editor.
                $editor.hide();
                $email.find( '.pdl-settings-email-preview' ).show();
            });

            $( '.pdl-settings-email-editor .delete' ).click(function(e) {
                e.preventDefault();

                var $email = $( this ).parents( '.pdl-settings-email' );
                $email.next().remove();
                $email.remove();
                $( '#pdl-admin-page-settings form:first' ).submit();
            });

            // Expiration notices have some additional handling to do.
            $( '.pdl-expiration-notice-email select.relative-time-and-event' ).change(function(e) {
                var parts = $( this ).val().split(',');
                var event = parts[0];
                var relative_time = parts[1];

                var $email = $( this ).parents( '.pdl-settings-email' );
                $email.find( '.notice-event' ).val( event );
                $email.find( '.notice-relative-time' ).val( relative_time );
            });

            $( '#pdl-settings-expiration-notices-add-btn' ).click(function(e) {
                e.preventDefault();

                var $container = $( '#pdl-settings-expiration-notices-add .pdl-expiration-notice-email' );
                var $editor = $container.find( '.pdl-settings-email-editor' );

                $( this ).hide();
                $container.show();
                $editor.show();
            });

            $( '#pdl-settings-expiration-notices-add input[type="submit"]' ).click(function(e) {
                var $editor = $( this ).parents( '.pdl-settings-email-editor' );

                $editor.find( 'input, textarea, select' ).each( function(i) {
                    var name = $( this ).attr( 'name' );

                    if ( ! name || -1 == name.indexOf( 'new_notice' ) )
                        return;

                    name = name.replace( 'new_notice', 'pdl_settings[expiration-notices]' );
                    $( this ).prop( 'name', name );
                } );

                return true;
            });
        },
    };
    pdl_settings_email.init();

});

