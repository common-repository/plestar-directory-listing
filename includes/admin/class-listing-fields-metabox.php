<?php
class PDL_Admin_Listing_Fields_Metabox {
    private $listing = null;

    public function __construct( &$listing ) {
        $this->listing = $listing;
    }

    public function render() {
        $image_count = count( $this->listing->get_images( 'ids' ) );

        echo '<div id="pdl-submit-listing">';

        echo '<ul class="pdl-admin-tab-nav subsubsub">';
        echo '<li><a href="#pdl-listing-fields-fields">' . _x( 'Fields', 'admin', 'PDM' )  . '</a> | </li>';
        echo '<li><a href="#pdl-listing-fields-images">';
        echo '<span class="with-image-count ' . ( $image_count > 0 ? '' : ' hidden' ) . '">' . sprintf( _x( 'Images (%s)', 'admin', 'PDM' ), '<span>' . $image_count . '</span>' ) . '</span>';
        echo '<span class="no-image-count' . ( $image_count > 0 ? ' hidden' : '' ) . '">' . _x( 'Images', 'admin', 'PDM' ) . '</span>';
        echo '</a></li>';
        echo '</ul>';

        echo '<div id="pdl-listing-fields-fields" class="pdl-admin-tab-content" tabindex="1">';
        $this->listing_fields();
        echo '</div>';

        echo '<div id="pdl-listing-fields-images" class="pdl-admin-tab-content" tabindex="2">';
        $this->listing_images();
        echo '</div>';

        echo '</div>';
    }

    private function listing_fields() {
        foreach ( pdl_get_form_fields( array( 'association' => 'meta' ) ) as $field ) {
            if ( ! empty( $_POST['listingfields'][ $field->get_id() ] ) ) {
                $value = $field->convert_input( $_POST['listingfields'][ $field->get_id() ] );
            } else {
                $value = $field->value( $this->listing->get_id() );
            }
            echo $field->render( $value, 'admin-submit' );
        }

        wp_nonce_field( 'save listing fields', 'pdl-admin-listing-fields-nonce', false );
    }

    private function listing_images() {
        if ( ! current_user_can( 'edit_posts' ) )
            return;

        $images = $this->listing->get_images( 'all', true );
        $thumbnail_id = $this->listing->get_thumbnail_id();

        echo '<div class="pdl-submit-listing-section-listing_images">';
        echo pdl_render( 'submit-listing-images',
                            array(
                                'admin'        => true,
                                'thumbnail_id' => $thumbnail_id,
                                'listing'      => $this->listing,
                                'images'       => $images ) );
        echo '</div>';
    }

    public static function metabox_callback( $post ) {
        $listing = PDL_Listing::get( $post->ID );

        if ( ! $listing )
            return '';

        $instance = new self( $listing );
        return $instance->render();
    }
}

