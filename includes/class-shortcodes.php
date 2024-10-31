<?php
/**
 * @since 4.0
 */
class PDL__Shortcodes {

    private $shortcodes = array();
    private $output = array();


    public function __construct() {
        add_action( 'pdl_loaded', array( $this, 'register' ) );
    }

    /**
     * Returns shortcodes being handled by Plestar Directory Listing.
     * @return array array of `shortcode => callback` items.
     */
    public function get_shortcodes() {
        return $this->shortcodes;
    }

    public function register() {
        if ( ! empty( $this->shortcodes ) )
            return;

        // TODO: change this to use the actual views or a "generic callback" that actually loads the view and returns
        // the output.
        global $pdl;

        /*
         * WordPress Shortcode:
         *  [plestardirectory], [directory-listing], [PLSBUSMANUI]
         * Used for:
         *  Displaying the main directory page and all directory content.
         * Notes:
         *  Required. Installed by PDL automatically. Cannot be removed from site unless you plan to uninstall PDL.
         * Example:
         *  `[plestardirectory]`
         */
        $this->add( 'plestardirectory',
                    array( $this, 'sc_main' ),
                    array( 'directory-listing', 'PLSBUSMANUI' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-submit-listing], [PLSBUSMANADDLISTING]
         * Used for:
         *  Creating a separate "Submit Listing" page for PDL.
         * Notes:
         *  Optional. Not needed if you are just using the standard directory links and buttons. This allows you to have a separate page if you want to have some special content around the page.
         * Example:
         *  `[plestardirectory-submitlisting]`
         */
        $this->add( 'plestardirectory-submit-listing',
                    array( $this, 'sc_submit_listing' ),
                    array( 'plestardirectory-submitlisting', 'directory-listing-submitlisting', 'directory-listing-submit-listing', 'PLSBUSMANADDLISTING' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-manage-listings], [directory-listing-managelistings], [PLSBUSMANMANAGELISTING]
         * Used for:
         *  Bulk listing editor page for users to see and manage their listings when logged in.
         * Parameters:
         *  - showsearchbar  Allows you to control the visibility of the search bar at the top of the page. Default is 1 if not specified. (Allowed Values: 0 or 1.)
         * Example:
         *  `[plestardirectory-manage-listings]`
         */
        $this->add( 'plestardirectory-manage-listings',
                    array( $this, 'sc_manage_listings' ),
                    array( 'plestardirectory-managelistings', 'directory-listing-manage-listings', 'plestardirectory-manage_listings', 'PLSBUSMANMANAGELISTING' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-listings], [plestardirectory-viewlistings], [PLSBUSMANVIEWLISTINGS], [PLSBUSMANMVIEWLISTINGS]
         * Used for:
         *  Showing listings with a certain type, tag or filter.
         * Notes:
         *  Good for displaying listings in a single category or from a single tag.
         * Parameters:
         *  - tag       Shows the listings with a certain tag name. (Allowed Values: Any valid tag name within the directory. Can be a comma separated list too (eg. "New, Hot").)
         *  - category  Shows the listings with a certain category. (Allowed Values: Any valid category name or ID you have configured under Directory -> Directory Categories. Can be a comma separated list too (e.g. "Dentists, Doctors" or 1,2,56).)
         *  - title     Adds a title to the page of listings to indicate what they are for. (Allowed Values: Any non-blank string.)
         * Example:
         *  - Display listings from category "Dentists" with tag "New" and include a title.
         *
         *    `[plestardirectory-listings tag="New" category="Dentists" title="Recent Listings for Dentists"]`
         *
         */
        $this->add( 'plestardirectory-listings',
                    array( $this, 'sc_listings' ),
                    array( 'PLSBUSMANVIEWLISTINGS', 'PLSBUSMANMVIEWLISTINGS', 'plestardirectory-view_listings', 'plestardirectory-viewlistings' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-search], [directory-listing-search]
         * Used for:
         *  Shows the Advanced Search Screen on any single page.
         * Parameters:
         *  - return_url  After the search is performed, when no results are found, a "Return to Search" link is shown with this parameter as target. Default value is the URL of the Advanced Search screen. (Allowed Values: Any valid URL or 'auto' to mean the URL of the page where the shortcode is being used.)
         * Example:
         *  `[plestardirectory-search]`
         */
        $this->add( 'plestardirectory-search',
                    array( $this, 'sc_search' ),
                    array( 'directory-listing-search', 'plestardirectory_search', 'directory-listing_search' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-featuredlistings]
         * Used for:
         *  To show all of the featured listings within your directory on a single page.
         * Parameters:
         *  - number_of_listings  Maximum number of listings to display. (Allowed Values: Any positive integer or 0 for no limit)
         * Example:
         *  `[plestardirectory-featuredlistings]`
         */
        $this->add( 'plestardirectory-featuredlistings', array( $this, 'sc_featured_listings' ) );


        /*
         * WordPress Shortcode:
         *  [plestardirectory-listing]
         * Used for:
         *  Displaying a single listing from the directory (by slug or ID).
         * Parameters:
         *  - id   Post ID of the listing. (Allowed Values: Any valid listing ID.)
         *  - slug Slug for the listing. (Allowed Values: Any valid listing slug.)
         * Notes:
         *  At least one of the parameters `id` or `slug` must be provided.
         * Example:
         *  `[plestardirectory-listing slug="my-listing"]`
         * Since:
         *  3.6.10
         */
        $this->add( 'plestardirectory-listing', array( $this, 'sc_single_listing' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-categories]
         * Used for:
         *  Displaying the list of categories in a similar fashion as the main page.
         * Parameters:
         *  - parent    Parent directory category ID. (Allowed Values: A directory category term ID)
         *  - orderby What value to use for odering the categories. Default is taken from current PDL settings. (Allowed Values: "name", "slug", "id", "description", "count" (listing count).)
         *  - order   Whether to order in ascending or descending order. Default is taken from current PDL settings. (Allowed Values: "ASC" or "DESC")
         *  - show_count Whether to display the listing count next to each category or not. Default is taken from current PDL settings. (Allowed Values: 0 or 1)
         *  - hide_empty Whether to hide empty categories or not. Default is 0. (Allowed Values: 0 or 1)
         *  - parent_only Whether to only display direct childs of parent category or make a recursive list. Default is 0. (Allowed Values: 0 or 1)
         *  - no_items_msg Message to display when there are no categories found. (Allowed Values: Any non-blank string)
         * Example:
         *  - Display the list of categories starting at the one with ID 20 and ordering by slug.
         *    `[plestardirectory-categories parent=20 order="slug"]`
         */
        $this->add( 'plestardirectory-categories', array( $this, 'sc_categories' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-listing-count]
         * Used for:
         *  Outputs the listing count for a given category or region.
         * Parameters:
         *  - category  What category to use. (Allowed Values: A valid category ID, name or slug.)
         *  - region    What region to use. (Allowed Values: A valid region ID, name or slug.)
         * Notes:
         *  If both parameters are provided the result is the number of listings inside the given category located in the given region.
         * Example:
         *  - To count how many listings you have in the "Restaurants" category that are located in "New York"
         *
         *    `[plestardirectory-listing-count category="Restaurants" region="New York"]`
         */
        $this->add( 'plestardirectory-listing-count', array( $this, 'sc_count' ), array( 'pdl-listing-count', 'directory-listing-listing-count' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-quick-search], [directory-listing-quick-search]
         * Used for:
         *  Displaying the quick search box on any page.
         * Parameters:
         *  - buttons  Which menu buttons to show inside the box. Default is none. (Allowed Values: "all", "none", or a comma-separated list from the set "create", "directory" and "listings").
         * Example:
         *  `[plestardirectory-quick-search buttons="create,listings"]`
         * Since:
         *  4.1.13
         */
        $this->add( 'plestardirectory-quick-search', array( $this, 'sc_quick_search' ), array( 'directory-listing-quick-search' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-latest-listings]
         * Used for:
         *  Displaying all or a set of latest listings from the directory.
         * Parameters:
         *  - menu Whether to include the quick search and menu bar as part of the output. Defaults to 0. (Allowed Values: 0 or 1)
         *  - buttons  Which menu buttons to show inside the menu (applies only when `menu` is `1`). Default is none. (Allowed Values: "all", "none", or a comma-separated list from the set "create", "directory" and "listings").
         *  - items_per_page The number of listings to show per page. If not present value will be set to "Listings per page" setting (Allowed Values: A positive integer)
         *  - pagination Enable pagination for shortcode. Default to 1. (Allowed values to disable: 0, false, no. Allowed values to enable: 1, true, yes)
         * Examples:
         *  - Display the latest 5 listings submitted to the directory:
         *    `[plestardirectory-latest-listings items_per_page=5 pagination=0]`
         *  - Display all listings, started from most recent, submitted to the directory, 4 listings per page:
         *    `[plestardirectory-latest-listings items_per_page=4 pagination=1]`
         * Since:
         *  4.1.13
         */
        $this->add( 'plestardirectory-latest-listings', array( $this, 'sc_listings_latest' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-random-listings]
         * Used for:
         *  Displaying a set of random listings from the directory.
         * Parameters:
         *  - menu Whether to include the quick search and menu bar as part of the output. Defaults to 0. (Allowed Values: 0 or 1)
         *  - buttons  Which menu buttons to show inside the menu (applies only when `menu` is `1`). Default is none. (Allowed Values: "all", "none", or a comma-separated list from the set "create", "directory" and "listings").
         *  - items_per_page The number of listings to show per page. If not present value will be set to "Listings per page" setting (Allowed Values: A positive integer)
         * Example:
         *  - Display a set of 10 random listings, including the directory menu with only the "Create A Listing" button:
         *
         *    `[plestardirectory-random-listings items_per_page=10 menu=1 buttons="create"]`
         * Since:
         *  4.1.13
         */
        $this->add( 'plestardirectory-random-listings', array( $this, 'sc_listings_random' ) );

        /*
         * WordPress Shortcode:
         *  [plestardirectory-featured-listings]
         * Used for:
         *  Displaying all or a set of featured listings from the directory.
         * Parameters:
         *  - menu Whether to include the quick search and menu bar as part of the output. Defaults to 0. (Allowed Values: 0 or 1)
         *  - buttons  Which menu buttons to show inside the menu (applies only when `menu` is `1`). Default is none. (Allowed Values: "all", "none", or a comma-separated list from the set "create", "directory" and "listings").
         *  - items_per_page The number of listings to show per page. If not present value will be set to "Listings per page" setting (Allowed Values: A positive integer)
         *  - pagination Use pagination, if disabled a set of listings, determined by items_per_page, will be display. Default to 1. (Allowed values to disable: 0, false, no. Allowed values to enable: 1, true, yes)
         * Example:
         *  `[plestardirectory-featured-listings items_per_page=5]`
         * Since:
         *  4.1.13
         */
        $this->add( 'plestardirectory-featured-listings', array( $this, 'sc_listings_featured' ) );


        do_action_ref_array( 'pdl_shortcodes_register', array( &$this ) );

        $this->shortcodes = apply_filters( 'pdl_shortcodes', $this->shortcodes );

        foreach ( $this->shortcodes as $shortcode => &$handler )
            add_shortcode( $shortcode, $handler );
    }

    public function add( $shortcode, $callback, $aliases = array() ) {
        foreach ( array_merge( array( $shortcode ), $aliases ) as $alias )
            $this->shortcodes[ $alias ] = $callback;
    }

    //
    // {{ Built-in shortcodes.
    //

    public function sc_main( $atts ) {
        global $wp_query;

        // if ( empty( $wp_query->pdl_is_main_page ) )
        //     return '';

        return pdl_current_view_output();
    }

    public function sc_submit_listing() {
        if ( $content = pdl_current_view_output() ) {
            return $content;
        } else {
            // This shouldn't happen... but just in case.
            $v = pdl_load_view( 'submit_listing' );
            $v->enqueue_resources();
            return $v->dispatch();
        }
    }

    public function sc_listings( $atts ) {
        global $pdl;
        require_once ( PDL_PATH . 'includes/views/all_listings.php' );

        $atts = shortcode_atts( array( 'tag' => '',
                                       'tags' => '',
                                       'category' => '',
                                       'categories' => '',
                                       'title' => '',
                                       'operator' => 'OR',
                                       'author' => '',
                                       'menu' => null,
                                       'pagination' => 1,
                                       'items_per_page' => pdl_get_option( 'listings-per-page' ) > 0 ? pdl_get_option( 'listings-per-page' ) : -1 ),
                                $atts );

        if ( ! is_null( $atts['menu'] ) )
            $atts['menu'] = ( 1 === $atts['menu'] || 'true' === $atts['menu'] ) ? true : false;

        $this->validate_attributes( $atts );

        $query_args = array();
        $query_args['items_per_page'] = intval( $atts['items_per_page'] );

        if ( $atts['category'] || $atts['categories'] ) {
            $requested_categories = array();

            if ( $atts['category'] )
                $requested_categories = array_merge( $requested_categories, explode( ',', $atts['category'] ) );

            if ( $atts['categories'] )
                $requested_categories = array_merge( $requested_categories, explode( ',', $atts['categories'] ) );

            $categories = array();

            foreach ( $requested_categories as $cat ) {
                $term = null;
                if ( !is_numeric( $cat ) )
                    $term = get_term_by( 'slug', $cat, PDL_CATEGORY_TAX );

                if ( !$term && is_numeric( $cat ) )
                    $term = get_term_by( 'id', $cat, PDL_CATEGORY_TAX );

                if ( $term )
                    $categories[] = $term->term_id;
            }

            $query_args['tax_query'][] = array( array( 'taxonomy' => PDL_CATEGORY_TAX,
                                                     'field' => 'id',
                                                     'terms' => $categories ) );
        }

        if ( $atts['tag'] || $atts['tags'] ) {
            $requested_tags = array();

            if ( $atts['tag'] )
                $requested_tags = array_merge( $requested_tags, explode( ',', $atts['tag'] ) );

            if ( $atts['tags'] )
                $requested_tags = array_merge( $requested_tags, explode( ',', $atts['tags'] ) );

            $query_args['tax_query'][] = array( array( 'taxonomy' => PDL_TAGS_TAX,
                                                     'field' => 'slug',
                                                     'terms' => $requested_tags ) );
        }

        if ( ! empty( $atts['author'] ) ) {
            $u = false;
            $u = is_numeric( $atts['author'] ) ? get_user_by( 'id', absint( $atts['author'] ) ) : get_user_by( 'login', $atts['author'] );

            if ( $u )
                $query_args['author'] = $u->ID;
        }

        $v = new PDL__Views__All_Listings(
            array(
                'menu' => $atts['menu'],
                'query_args' => $query_args,
                'in_shortcode' => true,
                'pagination' => $atts['items_per_page'] > 0 && intval( $atts['pagination'] ),
            ) );
        return $v->dispatch();
    }

    public function sc_listings_latest( $atts ) {
        $atts = shortcode_atts(
            array(
                'menu'      => 0,
                'buttons'   => 'none',
                'limit'     => 0,
                'items_per_page'  => -1,
                'pagination'=> '1'
            ),
            $atts,
            'plestardirectory-latest-listings'
        );

        $this->validate_attributes( $atts );

        return $this->display_listings(
            array(
                'orderby' => 'date',
                'order' => 'DESC'
            ),
            $atts
        );
    }

    public function sc_listings_random( $atts ) {
        $atts = shortcode_atts(
            array(
                'menu'      => 0,
                'buttons'   => 'none',
                'limit'     => 0,
                'items_per_page'  => -1
            ),
            $atts,
            'plestardirectory-random-listings'
        );

        $atts['pagination'] = '0';

        $this->validate_attributes( $atts );

        return $this->display_listings(
            array(
                'orderby' => 'rand'
            ),
            $atts
        );
    }

    public function sc_listings_featured( $atts ) {
        $atts = shortcode_atts(
            array(
                'menu'      => 0,
                'buttons'   => 'none',
                'limit'     => 0,
                'items_per_page'  => -1,
                'pagination'=> '1'
            ),
            $atts,
            'plestardirectory-featured-listings'
        );

        $this->validate_attributes( $atts );

        global $wpdb;
        $q = $wpdb->prepare(
            "SELECT DISTINCT {$wpdb->posts}.ID FROM {$wpdb->posts}
             JOIN {$wpdb->prefix}pdl_listings lp ON lp.listing_id = {$wpdb->posts}.ID
             WHERE {$wpdb->posts}.post_status = %s AND {$wpdb->posts}.post_type = %s AND lp.is_sticky = 1
             ORDER BY RAND()",
            'publish',
            PDL_POST_TYPE
        );
        $featured = $wpdb->get_col( $q );

        return $this->display_listings(
            array(
                'post__in'  => ! empty( $featured ) ? $featured : array( 0 ),
                'orderby'   => 'post__in',
            ),
            $atts
        );
    }

    private function display_listings( $query_args, $args = array() ) {
        $query_args = array_merge(
            array(
                'post_type'   => PDL_POST_TYPE,
                'post_status' => 'publish'
            ),
            $query_args
        );
        $args = array_merge(
            array(
                'menu'    => 0,
                'buttons' => 'none',
                'items_per_page' => -1,
                'pagination' => true,
            ),
            $args
        );

        if ( $args['pagination'] ) {
            $paged = get_query_var( 'page' ) ? get_query_var( 'page' ) : ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );
            $query_args['paged'] = intval( $paged );
        }

        $query_args['posts_per_page'] = intval( $args['items_per_page'] );

        $query = new WP_Query( $query_args );

        // Try to trick pagination to remove it when processing a shortcode.
        if ( ! $args['pagination'] ) {
            $query->max_num_pages = 1;
        }

        pdl_push_query( $query );

        $html  = '';

        if ( $query->have_posts() ) {
            $vars = array();
            $vars['query'] = $query;

            if ( $args['menu'] ) {
                $vars['_wrapper']  = 'page';
                $vars['_bar']      =   true;
                $vars['_bar_args'] =  array( 'buttons' => $args['buttons'] );
            }

            $this->maybe_paginate_frontpage( $query );

            $html .= pdl_x_render( 'listings', $vars );
        }

        wp_reset_postdata();
        pdl_pop_query();

        return $html;
    }

    public function sc_featured_listings( $atts ) {
        global $pdl;
        global $wpdb;

        $atts = shortcode_atts( array( 'number_of_listings' => pdl_get_option( 'listings-per-page' ) ), $atts );
        $atts['number_of_listings'] = max( 0, intval( $atts['number_of_listings'] ) );

        $q = $wpdb->prepare(
            "SELECT DISTINCT {$wpdb->posts}.ID FROM {$wpdb->posts}
             JOIN {$wpdb->prefix}pdl_listings lp ON lp.listing_id = {$wpdb->posts}.ID
             WHERE {$wpdb->posts}.post_status = %s AND {$wpdb->posts}.post_type = %s AND lp.is_sticky = 1
             ORDER BY RAND() " . ( $atts['number_of_listings'] > 0 ? sprintf( "LIMIT %d", $atts['number_of_listings'] ) : '' ),
            'publish',
            PDL_POST_TYPE
        );
        $featured = $wpdb->get_col( $q );

        $args = array(
            'post_type' => PDL_POST_TYPE,
            'post_status' => 'publish',
            'post__in' => ! empty( $featured ) ? $featured : array( 0 )
        );
        $q = new WP_Query( $args );
        pdl_push_query( $q );

        $html = pdl_x_render( 'listings', array( 'query' => $q ) );

        pdl_pop_query();

        return $html;
    }

    /**
     * @since 3.6.10
     */
    function sc_single_listing( $atts ) {
        $atts = shortcode_atts( array( 'id' => null, 'slug' => null ), $atts );
        $listing_id = pdl_get_post_by_id_or_slug( $atts['id'] ? $atts['id'] : $atts['slug'], 'id', 'id' );

        if ( ! $listing_id )
            return '';

        return pdl_render_listing( $listing_id, 'single' );
    }

    /**
     * @since 4.0
     */
    function sc_categories( $atts ) {
        return pdl_list_categories( $atts );
    }

    /**
     * @since 4.0
     */
    public function sc_count( $atts ) {
        $atts = shortcode_atts( array( 'category' => false, 'region' => false ), $atts );
        extract( $atts );

        // All listings.
        if ( ! $category && ! $region ) {
            $count = wp_count_posts( PDL_POST_TYPE );
            return $count->publish;
        }

        if ( ! function_exists( 'pdl_regions_taxonomy' ) )
            $region = false;

        $term = false;
        $region_term = false;

        if ( $category ) {
            foreach ( array( 'id', 'name', 'slug' ) as $field ) {
                if ( $term = get_term_by( $field, $category, PDL_CATEGORY_TAX ) )
                    break;
            }
        }

        if ( $region ) {
            foreach ( array( 'id', 'name', 'slug' ) as $field ) {
                if ( $region_term = get_term_by( $field, $region, pdl_regions_taxonomy() ) )
                    break;
            }
        }

        if ( ( $region && ! $region_term ) || ( $category && ! $term ) )
            return '0';

        if ( $region ) {
            $regions_api = pdl_regions_api();
            return $regions_api->count_listings( (int) $region_term->term_id, $term ? (int) $term->term_id : 0 );
        } else {
            _pdl_padded_count( $term );
            return $term->count;
        }

        return '0';
    }

    public function sc_manage_listings( $atts, $content, $shortcode ) {
        $atts = shortcode_atts( array( 'showsearchbar' => true ), $atts, $shortcode );

        if ( in_array( $atts['showsearchbar'], array( 'no', 'false', '0' ), true ) ) {
            $atts['showsearchbar'] = false;
        } else {
            $atts['showsearchbar'] = true;
        }

        $v = pdl_load_view( 'manage_listings', array( 'show_search_bar' => $atts['showsearchbar'] ) );
        return $v->dispatch();
    }

    public function sc_search( $atts ) {
        $atts = shortcode_atts( array( 'return_url' => '' ), $atts, 'plestardirectory-search' );

        if ( 'auto' == $atts['return_url'] ) {
            $atts['return_url'] = home_url( $_SERVER['REQUEST_URI'] );
        }

        $v = pdl_load_view( 'search', $atts );
        return $v->dispatch();
    }

    public function sc_quick_search( $atts ) {
        $defaults = array(
            'buttons' => 'none'
        );
        $atts = shortcode_atts( $defaults, $atts, 'plestardirectory-quick-search' );

        switch ( $atts['buttons'] ) {
        case 'all':
            $buttons = array( 'directory', 'listings', 'create' );
            break;
        case 'none':
            $buttons = array();
            break;
        default:
            $buttons = array_filter( explode( ',', trim( $atts['buttons'] ) ) );
            break;
        }

        $box_args = array(
            'buttons' => $buttons
        );

        return pdl_main_box( $box_args );
    }

    public function validate_attributes( &$atts ) {

        switch ( strtolower( $atts['pagination'] ) ) {
            case '0':
            case 'false':
            case 'no':
                $atts['pagination'] = false;
                break;
            case '1':
            case 'true':
            case 'yes':
            default:
                $atts['pagination'] = true;
        }

        // Backward compatibility for `limit` parameter
        if ( ! empty( $atts['limit'] ) ) {
            $atts['items_per_page'] = intval( $atts['items_per_page'] ) > 0 ? intval( $atts['items_per_page'] ) : intval( $atts['limit'] );
        }

        if ( 0 >= intval( $atts['items_per_page'] ) ) {
            $atts['items_per_page'] = ! $atts['pagination'] ? ( pdl_get_option( 'listings-per-page' ) > 0 ? pdl_get_option( 'listings-per-page' ) : 10 ) : 10;
        }
    }

    private function maybe_paginate_frontpage( $query ) {
        if ( ! function_exists('wp_pagenavi' ) && is_front_page() ) {
            global $paged;
            $paged = $query->query['paged'];
        }
    }
}
