<?php
/**
 * @since 5.0
 */
class PDL__Widgets {

    public function __construct() {
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
    }

    public function register_widgets() {
        include_once ( PDL_INC . 'widgets/widget-featured-listings.php' );
        register_widget('PDL_FeaturedListingsWidget');

        include_once ( PDL_INC . 'widgets/widget-latest-listings.php' );
        register_widget('PDL_LatestListingsWidget');

        include_once ( PDL_INC . 'widgets/widget-random-listings.php' );
        register_widget('PDL_RandomListingsWidget');

        include_once ( PDL_INC . 'widgets/widget-search.php' );
        register_widget('PDL_SearchWidget');
    }

}
