<?php

add_action( 'init', 'pbl_add_domain_action' );
function pbl_add_domain_action() {
	//$_POST = $_GET;
	if(isset($_POST["action"])){
		if($_POST["action"] == "pdl_add_domain"){
			global $wpdb;
	        $data = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->prefix}pdl_domains WHERE domain = %s", sanitize_text_field($_POST["domain"])));
	        if (empty($data)){
		        $obj = array();
		        $obj["domain"] = sanitize_text_field($_POST["domain"]);
		        $obj["ajax"] = sanitize_text_field($_POST["ajax"]);
		        $obj["created"] = $obj["updated"] = date("Y-m-d H:i:s");
		        $obj["flg"] = 1;
		    	$wpdb->insert("{$wpdb->prefix}pdl_domains", $obj);
	        }else{
	        	$wpdb->update("{$wpdb->prefix}pdl_domains", array("flg" => 1), array('domain' => sanitize_text_field($_POST["domain"])));
	        }
	        exit;
		}else if ($_POST["action"] == "pdl_remove_domain"){
			global $wpdb;
	        $wpdb->update("{$wpdb->prefix}pdl_domains", array("flg" => 0), array('domain' => sanitize_text_field($_POST["domain"])));
	        exit;
		}
	}
}

function pdl_get_version() {
    return PDL_VERSION;
}

function _pdl_page_lookup_query( $page_id, $count = false ) {
    global $wpdb;

    static $shortcodes = array(
        'main' => array('plestardirectory', 'directory-listing', 'PLSBUSMANUI'),
        'add-listing' => array('plestardirectory-submitlisting', 'PLSBUSMANADDLISTING'),
        'manage-listings' => array('plestardirectory-managelistings', 'PLSBUSMANMANAGELISTING'),
        'view-listings' => array('plestardirectory-viewlistings', 'plestardirectory-listings', 'PLSBUSMANMVIEWLISTINGS')
    );

    if ( ! array_key_exists( $page_id, $shortcodes ) )
        return false;

    if ( $count ) {
        $query  = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND ( 1=0";
    } else {
        $query  = "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND ( 1=0";
    }

    foreach ( $shortcodes[ $page_id ] as $s ) {
        $query .= sprintf( " OR post_content LIKE '%%[%s]%%' ", $s );
    }
    $query .= ')';

    return $query;
}

function pdl_get_page_ids( $page_id = 'main' ) {
    static $request_cached = array();

    if ( isset( $request_cached[ $page_id ] ) ) {
        $page_ids = $request_cached[ $page_id ];
    } else {
        $page_ids = null;
    }

    $cached_ids = get_transient( 'pdl-page-ids' );

    if ( is_null( $page_ids ) ) {
        $page_ids = pdl_get_page_ids_from_cache( $cached_ids, $page_id );
    }

    if ( is_null( $page_ids ) ) {
        $page_ids = pdl_get_page_ids_with_query( $page_id );
    }

    if ( is_array( $cached_ids ) ) {
        $cached_ids[ $page_id ] = $page_ids;
    } else {
        $cached_ids = array( $page_id => $page_ids );
    }

    set_transient( 'pdl-page-ids', $cached_ids, 60 * 60 * 24 * 30 );

    $request_cached[ $page_id ] = $page_ids;

    return apply_filters( 'pdl_get_page_ids', $page_ids, $page_id );
}

function pdl_get_page_ids_from_cache( $cache, $page_id ) {
    global $wpdb;

    if ( ! is_array( $cache ) || empty( $cache[ $page_id ] ) ) {
        return null;
    }

    // Validate the cached IDs.
    $query  = _pdl_page_lookup_query( $page_id, true );
    $query .= ' AND ID IN ( ' . implode( ',', array_map( 'intval', $cache[ $page_id ] ) ) . ' ) ';

    $count = intval( $wpdb->get_var( $query ) );

    if ( $count != count( $cache[ $page_id ] ) ) {
        pdl_debug( 'Page cache is invalid.' );
        return null;
    }

    return $cache[ $page_id ];
}

function pdl_get_page_ids_with_query( $page_id ) {
    global $wpdb;

    // Look up for pages.
    $q = _pdl_page_lookup_query( $page_id );

    if ( ! $q ) {
        return null;
    }

    $q .= ' ORDER BY ID ASC ';

    return $wpdb->get_col( $q );
}

function pdl_get_page_id( $name = 'main' ) {
    $page_ids = pdl_get_page_ids( $name );

    if ( ! $page_ids ) {
        $page_id = false;
    } else {
        $page_id = $page_ids[0];
    }

    return apply_filters( 'pdl_get_page_id', $page_id, $name );
}

/**
 * @deprecated since 4.0. Use `pdl_url()` instead.
 * @see pdl_url()
 */
function pdl_get_page_link($name='main', $arg0=null) {
    $page_id = pdl_get_page_id( $name );

    if ( $page_id ) {
        $link = _get_page_link( $page_id );
        $link = apply_filters( 'pdl__get_page_link', $link, $page_id, $name, $arg0 );
    } else {
        switch ( $name ) {
            case 'view':
            case 'viewlisting':
            case 'show-listing':
            case 'showlisting':
                $link = get_permalink( intval( $arg0 ) );
                break;
            case 'edit':
            case 'editlisting':
            case 'edit-listing':
            case 'delete':
            case 'deletelisting':
            case 'delete-listing':
                break;
            case 'viewlistings':
            case 'view-listings':
                $link = pdl_url( 'all_listings' );
                break;
            case 'add':
            case 'addlisting':
            case 'add-listing':
            case 'submit':
            case 'submitlisting':
            case 'submit-listing':
                $link = pdl_url( 'submit_listing' );
                break;
            case 'search':
                $link = pdl_url( 'search' );
                break;
            default:
                if ( ! pdl_get_page_id( 'main' ) )
                    return '';

                $link = pdl_get_page_link( 'main' );
                break;
        }
    }

    return apply_filters( 'pdl_get_page_link', $link, $name, $arg0 );
}

/* Admin API */
function pdl_admin() {
    return pdl()->admin;
}

function pdl_admin_notices() {
    global $pdl;
    return $pdl->admin->admin_notices();
}

/* Settings API */
function pdl_settings_api() {
    global $pdl;
    return $pdl->settings;
}

function pdl_get_option( $key, $default = false ) {
    $args_ = func_get_args();
    return call_user_func_array( array( pdl()->settings, 'get_option' ), $args_ );
}

function pdl_set_option( $key, $value ) {
    $args_ = func_get_args();
    return call_user_func_array( array( pdl()->settings, 'set_option' ), $args_ );
}

/**
 * @since 5.0
 */
function pdl_delete_option( $key ) {
    $args_ = func_get_args();
    return call_user_func_array( array( pdl()->settings, 'delete_option' ), $args_ );
}

/**
 * @since 5.0
 */
function pdl_register_settings_group( $args ) {
    $args_ = func_get_args();
    return call_user_func_array( array( pdl()->settings, 'register_group' ), $args_ );
}

/**
 * @since 5.0
 */
function pdl_register_setting( $args ) {
    $args_ = func_get_args();
    return call_user_func_array( array( pdl()->settings, 'register_setting' ), $args_ );
}

/* Form Fields API */
function pdl_formfields_api() {
    global $pdl;
    return $pdl->formfields;
}

function pdl_get_formfield($id) {
    if (is_numeric($id) && is_string($id))
        return pdl_get_formfield(intval($id));

    if (is_string($id))
        return pdl_formfields_api()->getFieldsByAssociation($id, true);

    return pdl_formfields_api()->get_field($id);
}

/* Fees/Payment API */
function pdl_payments_possible() {
    if ( ! pdl_get_option( 'payments-on' ) ) {
        return false;
    }

    return pdl()->payment_gateways->can_pay();
}

function pdl_fees_api() {
    return pdl()->fees;
}

function pdl_payments_api() {
    return pdl()->payments;
}

/* Listings API */
function pdl_listings_api() {
    return pdl()->listings;
}

/* Misc. */
function pdl_get_parent_categories($catid) {
    $category = get_term(intval($catid), PDL_CATEGORY_TAX);

    if ($category->parent) {
        return array_merge(array($category), pdl_get_parent_categories($category->parent));
    }

    return array($category);
}

function pdl_get_parent_catids($catid) {
    $parent_categories = pdl_get_parent_categories($catid);
    array_walk($parent_categories, create_function('&$x', '$x = intval($x->term_id);'));

    return $parent_categories;
}

/**
 * Checks if permalinks are enabled.
 * @return boolean
 * @since 2.1
 */
function pdl_rewrite_on() {
    global $wp_rewrite;
    return $wp_rewrite->permalink_structure ? true : false;
}

/**
 * Checks if a given user can perform some action to a listing.
 * @param string $action the action to be checked. available actions are 'view', 'edit', 'delete' and 'upgrade-to-sticky'
 * @param (object|int) $listing_id the listing ID. if null, the current post ID will be used
 * @param int $user_id the user ID. if null, the current user will be used
 * @return boolean
 * @since 2.1
 */
function pdl_user_can($action, $listing_id=null, $user_id=null) {
    $listing_id = $listing_id ? ( is_object($listing_id) ? $listing_id->ID : intval($listing_id) ) : get_the_ID();
    $user_id = $user_id ? $user_id : wp_get_current_user()->ID;
    $post = get_post($listing_id);

    if ( ! $post )
        return false;

    if ($post->post_type != PDL_POST_TYPE)
        return false;

    if ( isset( $_GET['preview'] ) && ( $action != 'view' ) )
        return false;

    $res = false;

    switch ($action) {
        case 'view':
            if ( isset( $_GET['preview'] ) ) {
                $res = user_can( $user_id, 'edit_others_posts' ) || ( $post->post_author && $post->post_author == $user_id );
            } else {
                $res = true;
            }
            // return apply_filters( 'pdl_user_can_view', true, $action, $listing_id );
            break;
        case 'flagging':
            if ( pdl_get_option( 'listing-flagging-register-users' ) ) {
                $res = is_user_logged_in() && false === PDL__Listing_Flagging::user_has_flagged( $listing_id, get_current_user_id() );
            } else {
                $res = true;
            }

            break;
        case 'edit':
        case 'delete':
            $res = user_can( $user_id, 'administrator' );
            $res = $res || ( $user_id && $post->post_author && $post->post_author == $user_id );
            $res = $res || ( ! $user_id && pdl_get_option( 'enable-key-access' ) );
            break;
        default:
            break;
    }

    $res = apply_filters( 'pdl_user_can', $res, $action, $listing_id, $user_id );
    $res = apply_filters( 'pdl_user_can_' . $action, $res, $listing_id, $user_id );

    return $res;
}

function pdl_get_post_by_slug($slug, $post_type=null) {
    $post_type = $post_type ? $post_type : PDL_POST_TYPE;

    $posts = get_posts(array(
        'name' => $slug,
        'post_type' => $post_type,
        'post_status' => 'publish',
        'numberposts' => 1,
        'suppress_filters' => false,
    ));

    if ($posts)
        return $posts[0];
    else
        return 0;
}

function pdl_get_current_sort_option() {
    if ($sort = trim(pdl_getv($_GET, 'pdl_sort', null))) {
        $order = substr($sort, 0, 1) == '-' ? 'DESC' : 'ASC';
        $sort = ltrim($sort, '-');

        $obj = new StdClass();
        $obj->option = $sort;
        $obj->order = $order;

        return $obj;
    }

    return null;
}

/*
 * @since 2.1.6
 */
function _pdl_resize_image_if_needed($id) {
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $metadata = wp_get_attachment_metadata( $id );

    if ( ! $metadata )
        return;

    $crop = (bool) pdl_get_option( 'thumbnail-crop' );
    $def_width = absint( pdl_get_option( 'thumbnail-width' ) );

    $width = absint( isset( $metadata['width'] ) ? $metadata['width'] : 0 );

    if ( $width < $def_width )
        return;

    $thumb_info = isset( $metadata['sizes']['pdl-thumb'] ) ? $metadata['sizes']['pdl-thumb'] : false;

    if ( ! $width )
        return;

    if ( $thumb_info ) {
        $thumb_width = absint( $thumb_info['width'] );
        $def_width = absint( pdl_get_option( 'thumbnail-width' ) );

        // 10px of tolerance.
        if ( abs( $thumb_width - $def_width ) < 10 )
            return;
    }

    $filename = get_attached_file( $id, true );
    $attach_data = wp_generate_attachment_metadata( $id, $filename );
    wp_update_attachment_metadata( $id, $attach_data );

    pdl_log( sprintf( 'Resized image "%s" [ID: %d] to match updated size constraints.', $filename, $id ) );
}

/*
 * @since 2.1.7
 * @deprecated since 3.6.10. See {@link pdl_currency_format()}.
 */
function pdl_format_currency($amount, $decimals = 2, $currency = null) {
    if ( $amount == 0.0 )
        return '—';

    return ( ! $currency ? pdl_get_option( 'currency-symbol' ) : $currency ) . ' ' . number_format( $amount, $decimals );
}

/**
 * @since 3.6.10
 */
function pdl_currency_format( $amount, $args = array() ) {
    // We don't actually allow modification of the "format" string for now, but it could be useful in the future.
    switch ( pdl_get_option( 'currency-symbol-position' ) ) {
        case 'none':
            $def_format = '[amount]';
            break;
       case 'right':
            $def_format = '[amount] [symbol]';
            break;
        case 'left':
        default:
            $def_format = '[symbol] [amount]';
            break;
    }

    $defaults = array( 'decimals' => 2,
                       'force_numeric' => false,
                       'currency' => pdl_get_option( 'currency' ),
                       'symbol' => pdl_get_option( 'currency-symbol' ),
                       'format' => $def_format );
    $args = wp_parse_args( $args, $defaults );
    extract( $args );

    if ( ! $force_numeric && $amount == '0' ) {
        return __( 'Free', 'PDM' );
    }

    if ( ! $symbol )
        $symbol = strtoupper( $currency );

    $number = ( 'placeholder' != $amount ? number_format_i18n( $amount, $decimals ) : '[amount]' );
    $format = strtolower( $format );

    if ( false === strpos( $format, '[amount]' ) )
        $format .= ' [amount]';

    $replacements = array( '[currency]' => strtoupper( $currency ),
                           '[symbol]' => $symbol,
                           '[amount]' => $number );

    return str_replace( array_keys( $replacements ), array_values( $replacements ), $format );
}

/**
 * @since 5.1.9
 */
function pdl_date_full_format( $timestamp ) {
    return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
}

/**
 * @since 5.1.9
 */
function pdl_date( $timestamp ) {
    return date_i18n( get_option( 'date_format' ), $timestamp );
}


/**
 * @since 3.5.3
 */
function pdl_get_post_by_id_or_slug( $id_or_slug = false, $try_first = 'id', $result = 'post' ) {
    if ( 'slug' == $try_first )
        $strategies = array( 'slug', 'id' );
    else
        $strategies = is_numeric( $id_or_slug ) ? array( 'id', 'slug' ) : array( 'slug' );

    global $wpdb;
    $listing_id = 0;

    foreach ( $strategies as $s ) {
        switch ( $s ) {
            case 'id':
                $listing_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_type = %s", $id_or_slug, PDL_POST_TYPE ) ) );
                break;
            case 'slug':
                $listing_id = intval( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s", $id_or_slug, PDL_POST_TYPE ) ) );
                break;
        }

        if ( $listing_id )
            break;
    }

    if ( ! $listing_id )
        return null;

    if ( 'id' == $result )
        return $listing_id;

    return get_post( $listing_id );
}

/**
 * @since 3.5.8
 */
function pdl_push_query( &$q ) {
    global $pdl;

    $pdl->_query_stack[] = $q;
}

/**
 * @since 3.5.8
 */
function pdl_pop_query() {
    global $pdl;
    return array_pop( $pdl->_query_stack );
}

/**
 * @since 3.5.8
 */
function pdl_current_query() {
    global $pdl;

    $len = count( $pdl->_query_stack );

    if ( $len == 0 )
        return null;

    return $pdl->_query_stack[ $len - 1 ];
}

/**
 * @since 3.6.10
 */
function pdl_experimental( $feature ) {
    static $file_overrides = false;
    global $pdl_development;

    if ( file_exists( PDL_PATH . 'experimental' ) )
        $file_overrides = explode( ',', trim( file_get_contents( PDL_PATH . 'experimental' ) ) );

    $res = false;
    if ( isset( $pdl_development ) )
        $res = $pdl_development->option_get( $feature );

    if ( $file_overrides && in_array( $feature, $file_overrides, true ) )
        $res = true;

    return $res;
}

/**
 * @since 4.0
 */
function pdl_current_view_output() {
    global $pdl;
    return $pdl->dispatcher->current_view_output();
}

/**
 * @since 4.0
 */
function pdl_url( $pathorview = '/', $args = array() ) {
    $base_id = pdl_get_page_id( 'main' );
    $base_url = _get_page_link( $base_id );
    $base_url = apply_filters( 'pdl_url_base_url', $base_url, $base_id, $pathorview, $args );
    $url = '';

    switch ( $pathorview ) {
        case 'submit_listing':
        case 'all_listings':
        case 'view_listings':
        case 'search':
        case 'login':
        case 'request_access_keys':
            $url = add_query_arg( 'pdl_view', $pathorview, $base_url );
            break;
        case 'flag_listing':
        case 'delete_listing':
        case 'edit_listing':
        case 'listing_contact':
            $url = add_query_arg( array( 'pdl_view' => $pathorview, 'listing_id' => $args ), $base_url );
            break;
        case 'renew_listing':
            $url = add_query_arg( array( 'pdl_view' => $pathorview, 'renewal_id' => $args ), $base_url );
            break;
        case 'main':
        case '/':
            $url = $base_url;
            break;
        case 'checkout':
            $url = $base_url;
            $url = add_query_arg( array( 'pdl_view' => 'checkout', 'payment' => $args ), $base_url );
            break;
        default:
            if ( pdl_starts_with( $pathorview, '/' ) )
                $url = rtrim( pdl_url( '/' ), '/' ) . '/' . substr( $pathorview, 1 );

            break;
    }

    $url = apply_filters( 'pdl_url', $url, $pathorview, $args );
    return $url;
}

/**
 * Generates Ajax URL and allows plugins to alter it through a filter.
 *
 * @since 5.0.3
 */
function pdl_ajax_url() {
    return apply_filters( 'pdl_ajax_url', admin_url( 'admin-ajax.php' ) );
}

// TODO: update before themes-release
// TODO: Sometimes this functions is called from
//       PDL_WPML_Compat->language_switcher even though no category
//       is available thorugh get_queried_object(), triggering a
//       "Trying to get property of non-object" notice.
//
//       The is_object() if-statement that is commented out below can prevent
//       the notice, but the real issue is the fact that the plugin thinks
//       we are showing a category while the main query has no queried object.
//
//       If the rewrite rule for a cateagry matches, but we can't retrieve
//       a term from the database, we should mark the query as not-found
//       from the beginning.
function pdl_current_category_id() {
    global $wp_query;

    if ( empty( $wp_query->pdl_is_category ) )
        return false;

    $term = $wp_query->get_queried_object();

    // if ( ! is_object( $term ) ) {
    //     return false;
    // }

    return $term->term_id;
}

/**
 * @since 4.1.12
 */
function _pdl_current_category_id() {
    $term = _pdl_current_category();

    if ( ! $term ) {
        return null;
    }

    return $term->term_id;
}

/**
 * @since 4.1.12
 */
function _pdl_current_category() {
    global $wp_query;

    if ( $wp_query->pdl_is_category ) {
        $term = $wp_query->get_queried_object();
    } else {
        $term = null;
    }

    if ( ! $term ) {
        $category_id = get_query_var( '_' . pdl_get_option( 'permalinks-category-slug' ) );

        if ( $category_id ) {
            $term = get_term_by( 'slug', $category_id, PDL_CATEGORY_TAX );
        }
    }

    if ( ! $term ) {
        $category_id = get_query_var( 'category_id' );

        if ( $category_id ) {
            $term = get_term_by( 'id', $category_id, PDL_CATEGORY_TAX );
        }
    }

    return $term;
}

function pdl_current_tag_id() {
    global $wp_query;

    if ( empty( $wp_query->pdl_is_tag ) )
        return false;

    $term = $wp_query->get_queried_object();
    return $term->term_id;
}

function pdl_current_action() {
    return pdl_current_view();
}

// TODO: how to implement now with CPT? (themes-release)
function pdl_current_listing_id() {
    return 0;
}

/**
 * @since 4.0
 */
function pdl_current_view() {
    global $pdl;

    if ( ! isset( $pdl->dispatcher ) || ! is_object( $pdl->dispatcher ) ) {
        return '';
    }

    return $pdl->dispatcher->current_view();
}

/**
 * @since 4.0
 */
function pdl_load_view( $view, $arg0 = null ) {
    global $pdl;
    return $pdl->dispatcher->load_view( $view, $arg0 );
}

function pdl_get_payment( $id ) {
    return PDL_Payment::objects()->get( $id );
}

/**
 * @since 5.0
 */
function pdl_get_fee_plans( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'enabled'         => 1,
        'include_free'    => pdl_payments_possible() ? false : true,
        'tag'             => pdl_payments_possible() ? '' : 'free',
        'orderby'         => 'label',
        'order'           => 'ASC',
        'categories'      => array()
    );
    if ( $order = pdl_get_option( 'fee-order' ) ) {
        $defaults['orderby'] = ( 'custom' == $order['method'] ) ? 'weight' : $order['method'];
        $defaults['order']   = ( 'custom' == $order['method'] ) ? 'DESC' : $order['order'];
    }

    $args = wp_parse_args( $args, $defaults );
    $args = apply_filters( 'pdl_get_fee_plans_args', $args );

    $where = '1=1';
    if ( 'all' !== $args['enabled'] ) {
        $where .= $wpdb->prepare( ' AND p.enabled = %d ', (bool) $args['enabled'] );
    }

    if ( $args['tag'] ) {
        $where .= $wpdb->prepare( ' AND p.tag = %s', $args['tag'] );
    }

    if ( ! $args['include_free'] && 'free' != $args['tag'] ) {
        $where .= $wpdb->prepare( ' AND p.tag != %s', 'free' );
    }

    $categories = $args['categories'];
    if ( ! empty( $categories ) ) {
        if ( ! is_array( $categories ) ) {
            $categories = array( $categories );
        }

        $categories = array_map( 'absint', $categories );
    }

    $order = strtoupper( $args['order'] );
    $orderby = $args['orderby'];
    $query = "SELECT p.id FROM {$wpdb->prefix}pdl_plans p WHERE {$where} ORDER BY {$orderby} {$order}";

    $plan_ids = $wpdb->get_col( $query );
    $plan_ids = apply_filters( 'pdl_pre_get_fee_plans', $plan_ids );

    $plans = array();
    foreach ( $plan_ids as $plan_id ) {
        if ( $plan = pdl_get_fee_plan( $plan_id ) ) {
            if ( $categories && ! $plan->supports_category_selection( $categories ) ) {
                continue;
            }

            $plans[] = $plan;
        }
    }

    $plans = apply_filters( 'pdl_get_fee_plans', $plans );

    return $plans;
}

/**
 * @since 5.0
 */
function pdl_get_fee_plan( $plan_id ) {
    global $wpdb;

    if ( 0 === $plan_id || 'free' === $plan_id ) {
        $plan_id = absint( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}pdl_plans WHERE tag = %s", 'free' ) ) );
    }

    $plan_id = absint( $plan_id );

    return PDL__Fee_Plan::get_instance( $plan_id );
}

/**
 * @since 4.1.8
 */
function pdl_is_taxonomy() {
    $current_view = pdl_current_view();
    $is_taxonomy = in_array( $current_view, array( 'show_category', 'show_tag' ), true );

    return apply_filters( 'pdl_is_taxonomy', $is_taxonomy, $current_view );
}

function pdl_render_page($template, $vars=array(), $echo_output=false) {
    if ($vars) {
        extract($vars);
    }

    ob_start();
    include($template);
    $html = ob_get_contents();
    ob_end_clean();

    if ($echo_output)
        echo $html;

    return $html;
}

function pdl_locate_template($template, $allow_override=true, $try_defaults=true) {
    $template_file = '';

    if (!is_array($template))
        $template = array($template);

    if ( pdl_get_option( 'disable-cpt' ) ) {
        if ($allow_override) {
            $search_for = array();

            foreach ($template as $t) {
                $search_for[] = $t . '.tpl.php';
                $search_for[] = $t . '.php';
                $search_for[] = 'single/' . $t . '.tpl.php';
                $search_for[] = 'single/' . $t . '.php';
            }

            $template_file = locate_template($search_for);
        }
    }

    if (!$template_file && $try_defaults) {
        foreach ($template as $t) {
            $template_path = PDL_TEMPLATES_PATH . '/' . $t . '.tpl.php';

            if (file_exists($template_path)) {
                $template_file = $template_path;
                break;
            }
        }
    }

    return $template_file;
}

function pdl_render($template, $vars=array(), $allow_override=true) {
    $vars = wp_parse_args($vars, array(
        '__page__' => array(
            'class' => array(),
            'content_class' => array(),
            'before_content' => '')));
    $template_name = is_array( $template ) ? $template[0] : $template;
    $vars = apply_filters('pdl_template_vars', $vars, $template_name);
    return apply_filters( "pdl_render_{$template_name}", pdl_render_page(pdl_locate_template($template, $allow_override), $vars, false) );
}

function pdl_render_msg($msg, $type='status') {
    $html = '';
    $html .= sprintf('<div class="pdl-msg %s">%s</div>', $type, $msg);
    return $html;
}

function _pdl_template_mode($template) {
    if ( pdl_locate_template(array('plestardirectory-' . $template, 'wpdirlist-' . $template), true, false) )
        return 'template';
    return 'page';
}

require_once ( PDL_PATH . 'includes/helpers/class-listing-display-helper.php' );


/**
 * Displays a single listing view taking into account all of the theme overrides.
 * @param mixed $listing_id listing object or listing id to display.
 * @param string $view 'single' for single view or 'excerpt' for summary view.
 * @return string HTML output.
 */
function pdl_render_listing($listing_id=null, $view='single', $echo=false) {
    $listing_id = $listing_id ? ( is_object( $listing_id ) ? $listing_id->ID : absint( $listing_id ) ) : get_the_ID();

    $args = array( 'post_type' => PDL_POST_TYPE, 'p' => $listing_id );
    if ( ! current_user_can( 'edit_posts' ) )
        $args['post_status'] = 'publish';

    $q = new WP_Query( $args );
    if ( ! $q->have_posts() )
        return '';

    $q->the_post();

    // TODO: review filters/actions before next-release (previously _pdl_render_excerpt() and _pdl_render_single().
    if ( 'excerpt' == $view )
        $html = PDL_Listing_Display_Helper::excerpt();
    else
        $html = PDL_Listing_Display_Helper::single();

    if ( $echo )
        echo $html;

    wp_reset_postdata();

    return $html;
}

function pdl_latest_listings($n=10, $before='<ul>', $after='</ul>', $before_item='<li>', $after_item = '</li>') {
    $n = max(intval($n), 0);

    $posts = get_posts(array(
        'post_type' => PDL_POST_TYPE,
        'post_status' => 'publish',
        'numberposts' => $n,
        'orderby' => 'date',
        'suppress_filters' => false,
    ));

    $html = '';

    $html .= $before;

    foreach ($posts as $post) {
        $html .= $before_item;
        $html .= sprintf('<a href="%s">%s</a>', get_permalink($post->ID), get_the_title($post->ID));
        $html .= $after_item;
    }

    $html .= $after;

    return $html;
}

/**
 * @since 4.0
 */
function pdl_the_listing_actions( $args = array() ) {
    echo pdl_listing_actions();
}

/**
 * @since 4.0
 */
function pdl_listing_actions( $args = array() ) {
    return pdl_render( 'parts/listing-buttons',
                         array( 'listing_id' => get_the_ID(),
                         'view' => 'excerpt' ),
                         false );
}

require_once( PDL_INC . 'logging.php' );
require_once( PDL_PATH . 'includes/class-listings-api.php' );
require_once( PDL_INC . 'listings.php' );

function pdl_sortbar_get_field_options() {
    $options = array();

    foreach( pdl_get_form_fields() as $field ) {
        if ( in_array( $field->get_field_type_id(), array( 'textarea', 'select', 'checkbox', 'url' ) ) || in_array( $field->get_association(), array( 'category', 'tags' ) ) ) {
            continue;
        }

        $options[ $field->get_id() ] = apply_filters( 'pdl_render_field_label', $field->get_label(), $field );
    }

    $options['user_login'] = _x( 'User', 'admin settings', 'PDM' );
    $options['user_registered'] = _x( 'User registration date', 'admin settings', 'PDM' );
    $options['date'] = _x( 'Date posted', 'admin settings', 'PDM' );
    $options['modified'] = _x( 'Date last modified', 'admin settings', 'PDM' );

    return $options;
}

/**
 * Returns the admin edit link for the listing.
 * @param int $listing_id the listing ID
 * @return string The admin edit link for the listing (if available).
 * @since 5.1.3
 */
function pdl_get_edit_post_link( $listing_id ){
    if ( ! $post = get_post( $listing_id ) )
        return '';

    $post_type_object = get_post_type_object( $post->post_type );
    if ( !$post_type_object )
        return '';

    if ( $post_type_object->_edit_link ) {
        $link = admin_url( sprintf( $post_type_object->_edit_link . '&action=edit', $post->ID ) );
    } else {
        $link = '';
    }

    return $link;
}

/**
 * @since 5.1.6
 */
function pdl_get_client_ip_address() {
    $ip = '0.0.0.0';

    $check_vars = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');

    foreach ($check_vars as $varname) {
        if (isset($_SERVER[$varname]) && !empty($_SERVER[$varname]))
            return $_SERVER[$varname];
    }

    return $ip;
 }
