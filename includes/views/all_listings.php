<?php

class PDL__Views__All_Listings extends PDL__View {

    public function get_title() {
        return _x( 'View All Listings', 'views', 'PDM' );
    }

    public function dispatch() {
        $args_ = isset( $this->query_args ) ? $this->query_args : array();

        $paged = get_query_var( 'page' ) ? get_query_var( 'page' ) : ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );
        $args = array(
            'post_type' => PDL_POST_TYPE,
            'posts_per_page' => pdl_get_option( 'listings-per-page' ) > 0 ? pdl_get_option( 'listings-per-page' ) : -1,
            'post_status' => 'publish',
            'paged' => intval($paged),
            'orderby' => pdl_get_option('listings-order-by', 'date'),
            'order' => pdl_get_option('listings-sort', 'ASC'),
            'pdl_main_query' => true
        );

        if ( isset( $args_['numberposts'] ) )
            $args['posts_per_page'] = $args_['numberposts'];

        if ( isset( $args_['items_per_page'] ) )
            $args['posts_per_page'] = $args_['items_per_page'];

        if ( ! empty( $args_['author'] ) )
            $args['author'] = $args_['author'];

        $args = array_merge( $args, $args_ );

        $q = new WP_Query( $args );

        // Try to trick pagination to remove it when processing a shortcode.
        if ( ! empty( $this->in_shortcode ) && empty( $this->pagination ) ) {
            $q->max_num_pages = 1;
        }
        pdl_push_query( $q );

        $show_menu = isset( $this->menu ) ? $this->menu : ( ! empty ( $args['tax_query'] ) ? false : true );

        $template_args = array( '_id' => $show_menu ? 'all_listings' : 'listings',
                                '_wrapper' => $show_menu ? 'page' : '',
                                '_bar' =>  $show_menu,
                                'query' => $q );

        if ( ! function_exists('wp_pagenavi' ) && is_front_page() ) {
            global $paged;
            $paged = $q->query['paged'];
        }

        $html = pdl_x_render( 'listings', $template_args );
        wp_reset_postdata();
        pdl_pop_query( $q );

        return $html;
    }

}
