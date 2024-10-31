<?php

class PDL_FieldTypes_Image extends PDL_Form_Field_Type {

    public function __construct() {
        parent::__construct( _x( 'Image (file upload)', 'form-fields api', 'PDM' ) );

        // TODO(fes-revamp): maybe this should go somewhere else?
        add_action( 'wp_ajax_pdl-file-field-upload', array( $this, '_ajax_file_field_upload' ) );
        add_action( 'wp_ajax_nopriv_pdl-file-field-upload', array( $this, '_ajax_file_field_upload' ) );
    }

    public function get_id() {
        return 'image';
    }

    public function get_supported_associations() {
        return array( 'meta' );
    }

    public function setup_field( &$field ) {
        $field->remove_display_flag( 'search' ); // image fields are not searchable
    }

    public function render_field_inner( &$field, $value, $context, &$extra=null, $field_settings = array() ) {
        if ( $context == 'search' )
            return '';

        $html = '';
        $html .= sprintf( '<input type="hidden" name="listingfields[%d]" value="%s" />',
                          $field->get_id(),
                          $value
                        );

        $html .= '<div class="preview">';
        if ($value)
            $html .= wp_get_attachment_image( $value, 'pdl-mini', false );

        $html .= sprintf( '<a href="http://google.com" class="delete" onclick="return PDL.fileUpload.deleteUpload(%d);" style="%s">%s</a>',
                          $field->get_id(),
                          !$value ? 'display: none;' : '',
                          _x( 'Remove', 'form-fields-api', 'PDM' )
                        );

        $html .= '</div>';

        // We use $listing_id to prevent CSFR. Related to #2848.
        $listing_id  = 0;
        if ( 'submit' == $context ) {
            $listing_id = $extra->get_id();
        } else if ( is_admin() ) {
            global $post;
            if ( ! empty( $post ) && PDL_POST_TYPE == $post->post_type ) {
                $listing_id = $post->ID;
            }
        }

        if ( ! $listing_id ) {
            return pdl_render_msg( _x( 'Field unavailable at the moment.', 'form fields', 'PDM' ), 'error' );
        }

        $nonce = wp_create_nonce( 'pdl-file-field-upload-' . $field->get_id() . '-listing_id-' . $listing_id );
        $ajax_url = add_query_arg(
            array(
                'action'     => 'pdl-file-field-upload',
                'field_id'   => $field->get_id(),
                'element'    => 'listingfields[' . $field->get_id() . ']',
                'nonce'      => $nonce,
                'listing_id' => $listing_id
            ),
            admin_url( 'admin-ajax.php' )
        );

        $html .= '<div class="pdl-upload-widget">';
        $html .= sprintf( '<iframe class="pdl-upload-iframe" name="upload-iframe-%d" id="pdl-upload-iframe-%d" src="%s" scrolling="no" seamless="seamless" border="0" frameborder="0"></iframe>',
                          $field->get_id(),
                          $field->get_id(),
                          $ajax_url
                        );
        $html .= '</div>';

        return $html;
    }

    public function get_field_html_value( &$field, $post_id ) {
        $img_id = $field->value( $post_id );

        if ( ! $img_id )
            return '';

        _pdl_resize_image_if_needed( $img_id );
        $img = wp_get_attachment_image_src( $img_id, 'large' );

        $html  = '';
        $html .= '<br />';
        $html .= '<a href="' . esc_url( $img[0] ) . '" target="_blank" rel="noopener" ' . ( pdl_get_option( 'use-thickbox' ) ? 'class="thickbox" data-lightbox="pdlgal" rel="pdlgal"' : '' )  . '>';
        $html .= wp_get_attachment_image( $img_id, 'pdl-thumb', false );
        $html .= '</a>';

        return $html;
    }

    public function _ajax_file_field_upload() {
        $field_id   = ! empty( $_REQUEST['field_id'] ) ? absint( $_REQUEST['field_id'] ) : 0;
        $nonce      = ! empty( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';
        $listing_id = ! empty( $_REQUEST['listing_id'] ) ? absint( $_REQUEST['listing_id'] ) : 0;

        if ( ! $field_id || ! $nonce || ! $listing_id ) {
            die;
        }

        if ( ! wp_verify_nonce( $nonce, 'pdl-file-field-upload-' . $field_id . '-' . 'listing_id-' . $listing_id ) ) {
            die;
        }

        $field = pdl_get_form_field( $field_id );
        if ( ! $field || 'image' != $field->get_field_type_id() ) {
            die;
        }

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo '<input type="file" name="file" class="file-upload" onchange="return window.parent.PDL.fileUpload.handleUpload(this);"/>';
        echo '</form>';

        if ( isset($_FILES['file']) && $_FILES['file']['error'] == 0 ) {
            // TODO: we support only images for now but we could use this for anything later
            if ( $media_id = pdl_media_upload( $_FILES['file'],
                true,
                true,
                array( 'image' => true,
                'min-size' => intval( pdl_get_option( 'image-min-filesize' ) ) * 1024,
                'max-size' => intval( pdl_get_option( 'image-max-filesize' ) ) * 1024,
                'min-width' => pdl_get_option( 'image-min-width' ),
                'min-height' => pdl_get_option( 'image-min-height' )
            ),
            $errors ) ) {
            echo '<div class="preview" style="display: none;">';
            echo wp_get_attachment_image( $media_id, 'thumb', false );
            echo '</div>';

            echo '<script type="text/javascript">';
            echo sprintf( 'window.parent.PDL.fileUpload.finishUpload(%d, %d);', $field_id, $media_id );
            echo '</script>';
            } else {
                print $errors;
            }
        }

        echo sprintf( '<script type="text/javascript">window.parent.PDL.fileUpload.resizeIFrame(%d);</script>', $field_id );

        exit;
    }

}

