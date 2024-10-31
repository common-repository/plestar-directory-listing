<?php
require_once( PDL_PATH . 'includes/helpers/class-authenticated-listing-view.php' );

/**
 * @since 4.0
 */
class PDL__Views__Delete_Listing extends PDL__Authenticated_Listing_View {

    public function dispatch() {
        $this->listing = PDL_Listing::get( intval( $_REQUEST['listing_id'] ) );
        $this->_auth_required();

        $nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';

        if ( $nonce && wp_verify_nonce( $nonce, 'delete listing ' . $this->listing->get_id() ) ) {
            $this->listing->delete();
            return pdl_render_msg( _x( 'Your listing has been deleted.', 'delete listing', 'PDM' ) );
        }

        return pdl_render( 'delete-listing-confirm', array( 'listing' => $this->listing,
                                                              'has_recurring' => $this->has_recurring_fee() ) );
    }

    private function has_recurring_fee() {
        global $wpdb;

        return (bool) $wpdb->get_var( $wpdb->prepare(
            "SELECT 1 AS x FROM {$wpdb->prefix}pdl_listings WHERE listing_id = %d AND is_recurring = %d",
            $this->listing->get_id(),
            1 ) );
    }

}
