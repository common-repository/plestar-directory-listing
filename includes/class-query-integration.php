<?php
/**
 * @since 4.0
 */
class PDL__Query_Integration {

    public function __construct() {
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

        add_action( 'parse_query', array( $this, 'set_query_flags' ), 50 );
        add_action( 'template_redirect', array( $this, 'set_404_flag' ), 0 );

        add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 10, 1 );
        add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 10, 2 );

        // Core sorting options.
        add_filter( 'pdl_listing_sort_options', array( &$this, 'sortbar_sort_options' ) );
        add_filter( 'pdl_query_fields', array( &$this, 'sortbar_query_fields' ) );
        add_filter( 'pdl_query_orderby', array( &$this, 'sortbar_orderby' ) );
    }

    public function add_query_vars( $vars ) {
        array_push( $vars, 'id' );
        array_push( $vars, 'listing' );
        array_push( $vars, 'category_id' ); // TODO: are we really using this var?
        array_push( $vars, 'category' );
        array_push( $vars, 'action' ); // TODO: are we really using this var?
        array_push( $vars, 'pdlx' );
        array_push( $vars, 'pdl-listener' );
        array_push( $vars, 'region' );
        array_push( $vars, 'pdl_view' );

        if ( pdl_get_option( 'disable-cpt' ) ) {
            array_push( $vars, '_' . pdl_get_option( 'permalinks-directory-slug' ) );
            array_push( $vars, '_' . pdl_get_option( 'permalinks-category-slug' ) );
            array_push( $vars, '_' . pdl_get_option( 'permalinks-tags-slug' ) );
        }

        return $vars;
    }

    public function set_query_flags( $query ) {
        if ( is_admin() )
            return;

        $main_query = ( ! $query->is_main_query() && isset( $query->query_vars['pdl_main_query'] ) && $query->query_vars['pdl_main_query'] )
                      || $query->is_main_query();

        if ( ! $main_query )
            return;

        // Defaults.
        $query->pdl_view = '';
        $query->pdl_is_main_page = false;
        $query->pdl_is_listing = false;
        $query->pdl_is_category = false;
        $query->pdl_is_tag = false;
        $query->pdl_our_query = false;

        // Is this a listing query?
        // FIXME: this results in false positives frequently
        $types = ( ! empty( $query->query_vars['post_type'] ) ? (array) $query->query_vars['post_type'] : array() );
        if ( $query->is_single && in_array( PDL_POST_TYPE, $types ) && count( $types ) < 2 ) {
            $query->pdl_is_listing = true;
            $query->pdl_view = 'show_listing';
        }

        // Is this a category query?
        $category_slug = pdl_get_option( 'permalinks-category-slug' );
        if ( ! empty( $query->query_vars[ PDL_CATEGORY_TAX ] ) ) {
            $query->pdl_is_category = true;
            $query->pdl_view = 'show_category';
        }

        // pdl_debug_e( $query );

        $tags_slug = pdl_get_option( 'permalinks-tags-slug' );
        if ( ! empty( $query->query_vars[ PDL_TAGS_TAX ] ) ) {
            $query->pdl_is_tag = true;
            $query->pdl_view = 'show_tag';
        }

        if ( $this->is_main_page( $query ) ) {
            $query->pdl_is_main_page = true;
        }

        if ( ! $query->pdl_view ) {
            if ( $query->get( 'pdl_view' ) )
                $query->pdl_view = $query->get( 'pdl_view' );
            elseif ( $query->pdl_is_main_page )
                $query->pdl_view = 'main';
        }

        $query->pdl_our_query = ( $query->pdl_is_listing || $query->pdl_is_category || $query->pdl_is_tag );

        if ( ! empty( $query->query_vars['pdl_main_query'] ) )
            $query->pdl_our_query = true;

        // Normalize view name.
        if ( ! empty( $query->pdl_view ) )
            $query->pdl_view = PDL_Utils::normalize( $query->pdl_view );

        do_action_ref_array( 'pdl_query_flags', array( $query ) );
    }

    /**
     * Uses the current query and the main query objects to determine if the current
     * request is for plugin's main page.
     *
     * FIXME: Can we make this more robust?
     *
     * @since 5.1.8
     */
    private function is_main_page( $query ) {
        global $wp_query;

        if ( ! $wp_query->is_page ) {
            return false;
        } 

        $plugin_page_ids = array_map( 'absint', pdl_get_page_ids() );

        if ( in_array( (int) $wp_query->get_queried_object_id(), $plugin_page_ids, true ) ) {
            return true;
        }

        if ( in_array( (int) $query->get_queried_object_id(), $plugin_page_ids, true ) ) {
            return true;
        }

        if ( in_array( (int) $query->get( 'page_id' ), $plugin_page_ids, true ) ) {
            return true;
        }

        return false;
    }

    public function set_404_flag() {
        global $wp_query;

        if ( ! $wp_query->pdl_our_query )
            return;

        if ( 'show_listing' == $wp_query->pdl_view && empty( $wp_query->posts ) )
            $wp_query->is_404 = true;
    }

    public function pre_get_posts( &$query ) {
        if ( is_admin() || ! isset( $query->pdl_our_query ) || ! $query->pdl_our_query )
            return;

        if ( ! $query->get( 'posts_per_page' ) )
            $query->set( 'posts_per_page', pdl_get_option( 'listings-per-page' ) > 0 ? pdl_get_option( 'listings-per-page' ) : -1 );

        if ( ! $query->get( 'orderby' ) )
            $query->set( 'orderby', pdl_get_option('listings-order-by', 'date' ) );

        if ( ! $query->get( 'order' ) )
            $query->set( 'order', pdl_get_option('listings-sort', 'ASC' ) );
    }

    public function posts_clauses( $pieces, $query ) {
        global $wpdb;

        if ( is_admin() || ! isset( $query->pdl_our_query ) || ! $query->pdl_our_query )
            return $pieces;

        $pieces = apply_filters( 'pdl_query_clauses', $pieces, $query );

        // Sticky listings.
        $is_sticky_query =  "(SELECT is_sticky FROM {$wpdb->prefix}pdl_listings wls WHERE wls.listing_id = {$wpdb->posts}.ID LIMIT 1) AS pdl_is_sticky";

        if ( in_array( pdl_current_view(), pdl_get_option( 'prevent-sticky-on-directory-view' ), true ) ) {
            $is_sticky_query = '';
        }

        $pieces['fields'] .= $is_sticky_query ? ', ' . $is_sticky_query : '';

        switch ( $query->get( 'orderby' ) ) {
        case 'paid':
            $pieces['fields'] .= ", (SELECT fee_price FROM {$wpdb->prefix}pdl_listings lp WHERE lp.listing_id = {$wpdb->posts}.ID LIMIT 1) AS pdl_plan_amount";
            $pieces['orderby'] = "pdl_plan_amount " . $query->get( 'order' ) . ", {$wpdb->posts}.post_date DESC, " . $pieces['orderby'];

            break;
        case 'paid-title':
            $pieces['fields'] .= ", (SELECT fee_price FROM {$wpdb->prefix}pdl_listings lp WHERE lp.listing_id = {$wpdb->posts}.ID LIMIT 1) AS pdl_plan_amount";
            $pieces['orderby'] = "pdl_plan_amount " . $query->get( 'order' ) . ", {$wpdb->posts}.post_title ASC, " . $pieces['orderby'];

            break;
        case 'plan-order-date':
            $plan_order = pdl_get_option( 'fee-order' );

            if ( 'custom' == $plan_order['method'] ) {
                $pieces['fields'] .= ", (SELECT po.weight FROM {$wpdb->prefix}pdl_plans po JOIN {$wpdb->prefix}pdl_listings pol ON po.id = pol.fee_id WHERE pol.listing_id = {$wpdb->posts}.ID ) AS pdl_plan_weight";
                $pieces['orderby'] = "pdl_plan_weight DESC, {$wpdb->posts}.post_date " . $query->get( 'order' ) . ", " . $pieces['orderby'];
            }

            break;
        case 'plan-order-title':
            $plan_order = pdl_get_option( 'fee-order' );

            if ( 'custom' == $plan_order['method'] ) {
                $pieces['fields'] .= ", (SELECT po.weight FROM {$wpdb->prefix}pdl_plans po JOIN {$wpdb->prefix}pdl_listings pol ON po.id = pol.fee_id WHERE pol.listing_id = {$wpdb->posts}.ID ) AS pdl_plan_weight";
                $pieces['orderby'] = "pdl_plan_weight DESC, {$wpdb->posts}.post_title " . $query->get( 'order' ) . ", " . $pieces['orderby'];
            }

            break;
        default:
            break;
        }

        $pieces['fields'] = apply_filters('pdl_query_fields', $pieces['fields'] );
        $pieces['custom_orderby'] = ( $is_sticky_query ? 'pdl_is_sticky DESC' : '' ) . apply_filters( 'pdl_query_orderby', '' );
        $pieces['orderby'] = ( $pieces['custom_orderby'] ? $pieces['custom_orderby'] . ', ' : '' ) . $pieces['orderby'];

        return $pieces;
    }

    // {{ Sort bar.
    public function sortbar_sort_options( $options ) {
        $sortbar_fields = pdl_sortbar_get_field_options();
        $sortbar = pdl_get_option( 'listings-sortbar-fields' );

        // Using the default argument for pdl_get_option does not work,
        // because a non-array value may already be stored in the settings array.
        if ( ! is_array( $sortbar ) ) {
            $sortbar = array();
        }

        foreach ( $sortbar as $field_id) {
            if ( ! array_key_exists( $field_id, $sortbar_fields ) )
                continue;
            $options[ 'field-' . $field_id ] = array( $sortbar_fields[ $field_id ], '', 'ASC' );
        }

        return $options;
    }

    public function sortbar_query_fields( $fields ) {
        global $wpdb;

        $sort = pdl_get_current_sort_option();

        if ( ! $sort || ! in_array( str_replace( 'field-', '', $sort->option ), pdl_get_option( 'listings-sortbar-fields' ) ) )
            return $fields;

        $sname = str_replace( 'field-', '', $sort->option );
        $q = '';

        switch ( $sname ) {
        case 'user_login':
            $q = "(SELECT user_login FROM {$wpdb->users} WHERE {$wpdb->users}.ID = {$wpdb->posts}.post_author) AS user_login";
            break;
        case 'user_registered':
            $q = "(SELECT user_registered FROM {$wpdb->users} WHERE {$wpdb->users}.ID = {$wpdb->posts}.post_author) AS user_registered";
            break;
        case 'date':
        case 'modified':
            break;
        default:
            $field = pdl_get_form_field( $sname );

            if ( ! $field || 'meta' != $field->get_association() )
                break;

            $q = $wpdb->prepare( "(SELECT {$wpdb->postmeta}.meta_value FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID AND {$wpdb->postmeta}.meta_key = %s) AS field_{$sname}", '_pdl[fields][' . $field->get_id() . ']' );
            break;
        }

        if ( $q )
            return $fields . ', ' . $q;
        else
            return $fields;
    }

    public function sortbar_orderby( $orderby ) {
        global $wpdb;

        $sort = pdl_get_current_sort_option();

        if ( ! $sort || ! in_array( str_replace( 'field-', '', $sort->option ), pdl_get_option( 'listings-sortbar-fields' ) ) )
            return $orderby;

        $sname = str_replace( 'field-', '', $sort->option );
        $qn = '';

        switch ( $sname ) {
        case 'user_login':
        case 'user_registered':
            $qn = $sname;
            break;
        case 'date':
        case 'modified':
            $qn = "{$wpdb->posts}.post_{$sname}";
            break;
        default:
            $field = pdl_get_form_field( $sname );

            if ( ! $field )
                break;

            switch ( $field->get_association() ) {
            case 'title':
            case 'excerpt':
            case 'content':
                $qn = "{$wpdb->posts}.post_" . $field->get_association();
                break;
            case 'meta':
                $qn = "field_{$sname}";
                break;
            }

            break;
        }

        if ( $qn )
            return $orderby . ', ' . $qn . ' ' . $sort->order;
        else
            return $orderby;
    }
    // }}


}

