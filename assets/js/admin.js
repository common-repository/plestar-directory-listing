var PDL_associations_fieldtypes = {};

(function($) {

    /* Form Fields */
    var PDLAdmin_FormFields = {
        $f_association: null,
        $f_fieldtype: null,

        init: function() {
            PDLAdmin_FormFields.$f_association = $('form#pdl-formfield-form select#field-association');
            PDLAdmin_FormFields.$f_association.change( PDLAdmin_FormFields.onAssociationChange );

            PDLAdmin_FormFields.$f_fieldtype = $('form#pdl-formfield-form select#field-type');
            PDLAdmin_FormFields.$f_fieldtype.change( PDLAdmin_FormFields.onFieldTypeChange );

            $( '#pdl-fieldsettings .iframe-confirm a' ).click(function(e) {
                e.preventDefault();

                if ( $( this ).hasClass( 'yes' ) ) {
                    $( this ).parents( '.iframe-confirm' ).hide();
                } else {
                    $( '#pdl-fieldsettings input[name="field[allow_iframes]"]' ).removeAttr( 'checked' );
                    $( this ).parents( '.iframe-confirm' ).hide();
                }
            })

            $( '#pdl-fieldsettings input[name="field[allow_iframes]"]' ).change(function() {
                if ( $( this ).is(':checked') ) {
                    $( '.iframe-confirm' ).show();
                } else {
                    $( '.iframe-confirm' ).hide();
                }
            });

            $( '#pdl-formfield-form input[name="field[display_flags][]"][value="search"]' ).change(function(){
                $( '.if-display-in-search' ).toggle( $( this ).is( ':checked' ) );
            });

            $('table.formfields tbody').sortable({
                placeholder: 'pdl-draggable-highlight',
                handle: '.pdl-drag-handle',
                axis: 'y',
                cursor: 'move',
                opacity: 0.9,
                update: function( event, ui ) {
                    var sorted_items = [];
                    $( this ).find( '.pdl-drag-handle' ).each( function( i, v ) {
                        sorted_items.push( $( v ).attr('data-field-id') );
                    } );

                    if ( sorted_items )
                        $.post( ajaxurl, { 'action': 'pdl-formfields-reorder', 'order': sorted_items } );
                }
            });
        },

        onFieldTypeChange: function() {
            var $field_type = $(this).find('option:selected');

            if ( !$field_type.length )
                return;

            var field_type = $field_type.val();

            $( 'select#field-validator' ).prop( 'disabled', false );

            // URL fields can only have the 'url' validator.
            if ( 'url' == field_type ) {
                $( 'select#field-validator option' ).not( '[value="url"]' ).attr( 'disabled', 'disabled' ).removeAttr( 'selected' );
                $( 'select#field-validator option[value="url"]' ).attr( 'selected', 'selected' );
            } else {
                $( 'select#field-validator option' ).removeAttr( 'disabled' );
            }

            // Twitter fields can not have a validator.
            if ( 'social-twitter' == field_type ) {
                $( 'select#field-validator' ).prop( 'disabled', true );
            }

            var request_data = {
                action: "pdl-renderfieldsettings",
                association: PDLAdmin_FormFields.$f_association.find('option:selected').val(),
                field_type: field_type,
                field_id: $('#pdl-formfield-form input[name="field[id]"]').val()
            };

            $.post( ajaxurl, request_data, function(response) {
                if ( response.ok && response.html ) {
                    $('#pdl-fieldsettings-html').html(response.html);
                    $('#pdl-fieldsettings').show();
                } else {
                    $('#pdl-fieldsettings-html').empty();
                    $('#pdl-fieldsettings').hide();
                }
            }, 'json' );
        },

        onAssociationChange: function() {
            $f_fieldtype = PDLAdmin_FormFields.$f_fieldtype;

            var association = $(this).val();
            var valid_types = PDL_associations_fieldtypes[ association ];
            var private_option = $( '#pdl_private_field' );

            $f_fieldtype.find('option').prop('disabled', false);

            $f_fieldtype.find('option').each(function(i,v){
                if ( $.inArray( $(v).val(), valid_types ) < 0 ) {
                    $(v).prop('disabled', true);
                }
            });

            $f_fieldtype.change();

            if ( 0 <= [ 'title', 'content', 'category'].indexOf( association ) ) {
                private_option.find( 'input' ).prop( 'disabled', true );
                private_option.hide();
            } else {
                private_option.find( 'input' ).prop( 'disabled', false );
                private_option.show();
            }
        }
    };


    $(document).ready(function(){
        PDLAdmin_FormFields.init();
    });

})(jQuery);


jQuery(document).ready(function($){

    // {{ Manage Fees.
    $('.pdl-admin-page-fees .wp-list-table.fees tbody').sortable({
        placeholder: 'pdl-draggable-highlight',
        handle: '.pdl-drag-handle',
        axis: 'y',
        cursor: 'move',
        opacity: 0.9,
        update: function( event, ui ) {
            var rel_rows = $( '.free-fee-related-tr' ).remove();
            $( 'tr.free-fee' ).after( rel_rows );

            var sorted_items = [];
            $( this ).find( '.pdl-drag-handle' ).each( function( i, v ) {
                sorted_items.push( $( v ).attr('data-fee-id') );
            } );

            if ( sorted_items )
                $.post( ajaxurl, { 'action': 'pdl-admin-fees-reorder', 'order': sorted_items } );
        }
    });

    $( 'select[name="fee_order[method]"], select[name="fee_order[order]"]' ).change(function(e) {
        $.ajax({
            url: ajaxurl,
            data: $(this).parent('form').serialize(),
            dataType: 'json',
            type: 'POST',
            success: function(res) {
                if ( res.success )
                    location.reload();
            }
        });
    });

    if ( 'custom' == $('select[name="fee_order[method]"]').val() ) {
        $( '.pdl-admin-page-fees .wp-list-table .pdl-drag-handle' ).show();
    }
    // }}


    /* Ajax placeholders */

    $('.pdl-ajax-placeholder').each(function(i,v){
        pdl_load_placeholder($(v));
    });

    /*
     * Admin bulk actions
     */

    $('input#doaction, input#doaction2').click(function(e) {
        var action_name = ( 'doaction' == $(this).attr('id') ) ? 'action' : 'action2';
        var $selected_option = $('select[name="' + action_name + '"] option:selected');
        var action_val = $selected_option.val();

        if (action_val.split('-')[0] == 'listing') {
            var action = action_val.split('-')[1];

            if (action != 'sep0' && action != 'sep1' && action != 'sep2') {
                var $checked_posts = $('input[name="post[]"]:checked');
                var uri = $selected_option.attr('data-uri');

                $checked_posts.each(function(i,v){
                    uri += '&post[]=' + $(v).val();
                });

                window.location.href = uri;

                return false;
            }
        }

        return true;
    });

    /* Form fields form preview */
    $('.pdl-admin.pdl-page-formfields-preview form input[type="submit"]').click(function(e){
        e.preventDefault();
        alert('This form is just a preview. It doesn\'t work.');
    });

    /* Debug info page */
    $('#pdl-admin-debug-info-page a.nav-tab').click(function(e){
        e.preventDefault();

        $('#pdl-admin-debug-info-page a.nav-tab').not(this).removeClass('nav-tab-active');

        var $selected_tab = $(this);
        $selected_tab.addClass( 'nav-tab-active' );

        $( '.pdl-debug-section' ).hide();
        $( '.pdl-debug-section[data-id="' + $(this).attr('href') + '"]' ).show();
    });

    if ( $('#pdl-admin-debug-info-page a.nav-tab').length > 0 )
        $('#pdl-admin-debug-info-page a.nav-tab').get(0).click();

    /* Transactions */
    $( '.pdl-page-admin-transactions .column-actions a.details-link' ).click(function(e){
        e.preventDefault();
        var $tr = $(this).parents('tr');
        var $details = $tr.find('div.more-details');

        var $tr_details = $tr.next('tr.more-details-row');
        if ( $tr_details.length > 0 ) {
            $tr_details.remove();
            $(this).text( $(this).text().replace( '-', '+' ) );
            return;
        } else {
            $(this).text( $(this).text().replace( '+', '-' ) );
        }

        $tr.after( '<tr class="more-details-row"><td colspan="7">' + $details.html() + '</td></tr>' ).show();
    });

});

function pdl_load_placeholder($v) {
    var action = $v.attr('data-action');
    var post_id = $v.attr('data-post_id');
    var baseurl = $v.attr('data-baseurl');

    $v.load(ajaxurl, {"action": action, "post_id": post_id, "baseurl": baseurl});
}


var PDL_Admin = {};
PDL_Admin.payments = {};

// TODO: integrate this into $.
PDL_Admin.ProgressBar = function($item, settings) {
    $item.empty();
    $item.html('<div class="pdl-progress-bar"><span class="progress-text">0%</span><div class="progress-bar"><div class="progress-bar-outer"><div class="progress-bar-inner" style="width: 0%;"></div></div></div>');

    this.$item = $item;
    this.$text = $item.find('.progress-text');
    this.$bar = $item.find('.progress-bar');

    this.set = function( completed, total ) {
        var pcg = Math.round( 100 * parseInt( completed) / parseInt( total ) );
        this.$text.text(pcg + '%');
        this.$bar.find('.progress-bar-inner').attr('style', 'width: ' + pcg + '%;');
    };
};

(function($) {
    PDL_Admin.dialog = {};
    var dialog = PDL_Admin.dialog;

        // if ($('#pdl-modal-dialog').length == 0) {
        //     $('body').append($('<div id="pdl-modal-dialog"></div>'));
        // }
})(jQuery);



(function($) {
    var payments = PDL_Admin.payments;

    payments._initialize = function() {
        $('#PlestarDirectory_listinginfo a.payment-details-link').click(function(e) {
            e.preventDefault();
            payments.viewPaymentDetails( $(this).attr('data-id') );
        });

        if ($('#pdl-modal-dialog').length == 0) {
            $('body').append($('<div id="pdl-modal-dialog"></div>'));
        }
    };

    payments.viewPaymentDetails = function(id) {
        $.get( ajaxurl, { 'action': 'pdl-payment-details', 'id': id }, function(res) {
            if (res && res.success) {
                if ($('#pdl-modal-dialog').length == 0) {
                    $('body').append($('<div id="pdl-modal-dialog"></div>'));
                }

                $('#pdl-modal-dialog').html(res.data.html);
                tb_show('', '#TB_inline?inlineId=pdl-modal-dialog');

                // Workaround WP bug https://core.trac.wordpress.org/ticket/27473.
                $( '#TB_window' ).width( $( '#TB_ajaxContent' ).outerWidth() );

                if ( $( '#TB_window' ).height() > $( '#TB_ajaxContent' ).outerHeight() )
                    $( '#TB_ajaxContent' ).height( $( '#TB_window' ).height() );

                $('#pdl-modal-dialog').remove();
            }
        }, 'json' );
    };

    // Initialize payments.
    $(document).ready(function(){ payments._initialize(); });

})(jQuery);

/* {{ Settings. */
(function($) {
    var s = PDL_Admin.settings = {
        init: function() {
            var t = this;

            $( '#pdl-settings-quick-search-fields' ).on( 'change', ':checkbox', function() {
                var $container = $( '#pdl-settings-quick-search-fields' );
                var text_fields = $container.data( 'text-fields' );
                var selected = $container.find( ':checkbox:checked' ).map(function(){ return parseInt($(this).val()); }).get();
                var show_warning = false;

                if ( selected.length > 0 && text_fields.length > 0 ) {
                    for ( var i = 0; i < text_fields.length; i++ ) {
                        if ( $.inArray( text_fields[i], selected ) > -1 ) {
                            show_warning = true;
                            break;
                        }
                    }
                }

                if ( show_warning ) {
                    $( '#pdl-settings-quick-search-fields .text-fields-warning' ).fadeIn( 'fast' );
                } else {
                    $( '#pdl-settings-quick-search-fields .text-fields-warning' ).fadeOut( 'fast' );
                }
            });
        }
    };

    $(document).ready(function(){
        if ( $( '#pdl-admin-page-settings' ).length > 0 ) {
            s.init();
        }
    });
})(jQuery);
/* }} */

/* {{ Uninstall. */
jQuery(function($) {
    if ( 0 == $( '.pdl-admin-page-uninstall' ).length ) {
        return;
    }

    var $warnings = $( '#pdl-uninstall-messages' );
    var $confirm_button = $( '#pdl-uninstall-proceed-btn' );
    var $form = $( '#pdl-uninstall-capture-form' );

    $( '#pdl-uninstall-proceed-btn' ).click(function(e) {
        e.preventDefault();
        $warnings.fadeOut( 'fast', function() {
            $form.fadeIn( 'fast' );
        } );
    });
    
    $( '#pdl-uninstall-capture-form' ).submit(function() {
        var $no_reason_error = $( '.pdl-validation-error.no-reason' ).hide();
        var $no_text_error   = $( '.pdl-validation-error.no-reason-text' ).hide();
        var $reason_checked = $( 'input[name="uninstall[reason_id]"]:checked' );

        if ( 0 == $reason_checked.length ) {
            $no_reason_error.show();
            return false;
        }

        if ( '0' == $reason_checked.val() ) {
            var $reason_text = $( 'textarea[name="uninstall[reason_text]"]' );
            var reason_text = $.trim( $reason_text.val() );

            $reason_text.removeClass( 'invalid' );

            if ( ! reason_text ) {
                $no_text_error.show();
                $reason_text.addClass( 'invalid' );

                return false;
            }
        }

        return true;
    });

    $( 'form#pdl-uninstall-capture-form input[name="uninstall[reason_id]"]' ).change(function(e) {
        var val = $(this).val();

        if ( '0' == val ) {
            $( 'form#pdl-uninstall-capture-form .custom-reason' ).fadeIn();
        } else {
            $( 'form#pdl-uninstall-capture-form .custom-reason' ).fadeOut( 'fast', function() {
                $(this).val('');
            } );
        }
    });
    

});

// {{ Widgets.
(function($) {
    $(document).ready(function() {
        if ( $('body.wp-admin.widgets-php').length == 0 ) {
            return;
        }

        $( 'body.wp-admin.widgets-php' ).on( 'change', 'input.pdl-toggle-images', function() {
            var checked = $(this).is(':checked');

            if ( checked ) {
                $(this).parents('.widget').find('.thumbnail-width-config, .thumbnail-height-config').fadeIn('fast');
            } else {
                $(this).parents('.widget').find('.thumbnail-width-config, .thumbnail-height-config').fadeOut('fast');
            }
        });

    });
})(jQuery);
// }}

// {{ Create main page warning.
(function($) {
    $(document).ready(function() {
        $( 'a.pdl-create-main-page-button' ).click(function(e) {
            e.preventDefault();
            var $msg = $(this).parents('div.error');

            $.ajax({
                'url': ajaxurl,
                'data': { 'action': 'pdl-create-main-page',
                          '_wpnonce': $(this).attr('data-nonce') },
                'dataType': 'json',
                success: function(res) {
                    if ( ! res.success )
                        return;

                    $msg.fadeOut( 'fast', function() {
                        $(this).html( '<p>' + res.message + '</p>' )
                        $(this).removeClass('error')
                        $(this).addClass('updated')
                        $(this).fadeIn( 'fast' );
                    } );
                }
            });
        });
    });
})(jQuery);
// }}

// Dismissible Messages
(function($) {
    $(function(){
        var dismissNotice = function( $notice, notice_id, nonce ) {
            $.post( ajaxurl, {
                action: 'pdl_dismiss_notification',
                id: notice_id,
                nonce: nonce
            }, function() {
                $notice.fadeOut( 'fast', function(){ $notice.remove(); } );
            } );
        };

        $( '#wpbody-content' )
            .on( 'click', '.pdl-notice.dismissible > .notice-dismiss', function( e ) {
                e.preventDefault();

                var $notice = $( this ).parent( '.pdl-notice' );
                var dismissible_id = $( this ).data( 'dismissible-id' );
                var nonce = $( this ).data( 'nonce' );

                dismissNotice( $notice, dismissible_id, nonce );
            } )
            .on( 'click', '.pdl-notice.is-dismissible > .notice-dismiss', function( e ) {
                e.preventDefault();

                var $notice = $( this ).parent( '.pdl-notice' );
                var dismissible_id = $notice.data( 'dismissible-id' );
                var nonce = $notice.data( 'nonce' );

                dismissNotice( $notice, dismissible_id, nonce );
            } );
    });
})(jQuery);

// Some utilities for our admin forms.
jQuery(function( $ ) {

    $( '.pdl-js-toggle' ).change(function() {
        var name = $(this).attr('name');
        var value = $(this).val();
        var is_checkbox = $(this).is(':checkbox');
        var is_radio = $(this).is(':radio');
        var is_select = $(this).is('select');
        var toggles = $(this).attr('data-toggles');

        if ( is_select ) {
            var $option = $( this ).find( ':selected' );
            var toggles = $option.attr( 'data-toggles' );

            if ( ! toggles || 'undefined' == typeof toggles )
                toggles = '';
        }

        if ( toggles ) {
            var $dest = ( toggles.startsWith('#') || toggles.startsWith('-') ) ? $(toggles) : $( '#' + toggles + ', .' + toggles );

            if ( 0 == $dest.length || ( ! is_radio && ! is_checkbox && ! is_select ) )
                return;

            if ( is_checkbox && $(this).is(':checked') ) {
                $dest.toggleClass('hidden');
                return;
            }
        }

        if ( is_select ) {
            var other_opts = $( this ).find( 'option' ).not( '[value="' + value + '"]' );
        } else {
            var other_opts = $('input[name="' + name + '"]').not('[value="' + value + '"]');
        }

        other_opts.each(function() {
            var toggles_i = $(this).attr('data-toggles');

            if ( ! toggles_i )
                return;

            var $dest_i = ( toggles_i.startsWith('#') || toggles_i.startsWith('-') ) ? $(toggles_i) : $( '#' + toggles_i + ', .' + toggles_i );
            $dest_i.addClass('hidden');
        });

        if ( toggles ) {
            $dest.toggleClass('hidden');
        }
    });

});

//
// {{ Admin tab selectors.
//
jQuery(function($) {
    $('.pdl-admin-tab-nav a').click(function(e) {
        e.preventDefault();

        var $others = $( this ).parents( 'ul' ).find( 'li a' );
        var $selected = $others.filter( '.current' );

        $others.removeClass( 'current' );
        $( this ).addClass( 'current' );

        var href = $( this ).attr('href');
        var $content = $( href );

        if ( $selected.length > 0 )
            $( $selected.attr( 'href' ) ).hide();

        $content.show().focus();
    });

    $( '.pdl-admin-tab-nav' ).each(function(i, v) {
        $(this).find('a:first').click();
    });
});
//
// }}
//

jQuery( function( $ ) {
        $( document ).on( 'click', '.pdl-admin-confirm', function( e ) {
            // e.preventDefault();

            var message = $( this ).data( 'confirm' );
            if ( ! message || 'undefined' == typeof message )
                message = 'Are you sure you want to do this?';

            var confirm = window.confirm( message );
            if ( ! confirm ) {
                e.stopImmediatePropagation();
                return false;
            }

            return true;
        } );
});
