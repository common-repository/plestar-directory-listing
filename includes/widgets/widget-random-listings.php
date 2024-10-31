<?php
require_once( PDL_PATH . 'includes/widgets/class-listings-widget.php' );

/**
 * Random listings widget.
 * @since 2.1
 */
class PDL_RandomListingsWidget extends PDL_Listings_Widget {

    public function __construct() {
        parent::__construct( _x( 'Plestar Directory Listing - Random Listings', 'widgets', 'PDM' ),
                             _x( 'Displays a list of random listings from the Plestar Directory Listing.', 'widgets', 'PDM' ) );

        $this->set_default_option_value( 'title', _x( 'Random Listings', 'widgets', 'PDM' ) );
    }

    public function get_listings( $instance ) {
        return get_posts( array( 'post_type' => PDL_POST_TYPE,
                                 'post_status' => 'publish',
                                 'numberposts' => $instance['number_of_listings'],
                                 'orderby' => 'rand',
                                 'suppress_filters' => false ) );
    }

}
