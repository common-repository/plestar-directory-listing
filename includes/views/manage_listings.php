<?php
/**
 * @since 4.0
 */
class PDL__Views__Manage_Listings extends PDL__View {

    public function dispatch() {
        $current_user = is_user_logged_in() ? wp_get_current_user() : null;

        if ( ! $current_user ) {
            $login_msg = _x( 'Please <a>login</a> to manage your listings.', 'view:manage-listings', 'PDM' );
            $login_msg = str_replace( '<a>', '<a href="' . esc_url( pdl_url( 'login' ) ) . '">', $login_msg );
            return $login_msg;
        }

        $args = array(
            'post_type' => PDL_POST_TYPE,
            'post_status' => 'publish',
            'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
            'author' => $current_user->ID,
            'pdl_main_query' => true
        );
        $q = new WP_Query( $args );
        pdl_push_query( $q );

        $html = $this->_render_page( 'manage_listings', array( 'current_user' => $current_user,
                                                               'query' => $q,
                                                               '_bar' => $this->show_search_bar ) );

        pdl_pop_query();

        return $html;
    }

}
