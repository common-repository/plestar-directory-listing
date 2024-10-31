<?php
/**
 * @since 5.1.6
 */
class PDL__Admin__Metaboxes__Listing_Flagging {

    public function __construct( $post_id ) {
        $this->listing = pdl_get_listing( $post_id );
    }

    public function render() {
        return pdl_render_page( PDL_PATH . 'templates/admin/metaboxes-listing-flagging.tpl.php', array( 'listing' => $this->listing ) );
    }
}
