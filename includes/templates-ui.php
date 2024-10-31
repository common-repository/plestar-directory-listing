<?php
/*
 * UI Functions to be called from templates.
 */

/**
 * Returns a list of directory categories using the configured directory settings.
 * The list is actually produced by {@link pdl_list_categories()}.
 * @return string HTML output.
 * @uses pdl_list_categories().
 */
function pdl_directory_categories() {
    $html = pdl_list_categories( array(
                                        'hide_empty' => pdl_get_option( 'hide-empty-categories' ),
                                        'parent_only' => pdl_get_option( 'show-only-parent-categories' )
                                 ) );

    return apply_filters( 'pdl_main_categories', $html );
}

/**
 * Identical to {@link pdl_directory_categories()}, except the output is printed instead of returned.
 * @uses pdl_directory_categories().
 */
function pdl_the_directory_categories() {
    echo pdl_directory_categories();
}

/**
 * @since 2.3
 * @access private
 */
function _pdl_padded_count( &$term, $return = false ) {
    global $wpdb;

    $found = false;
    $count = intval( wp_cache_get( 'term-padded-count-' . $term->term_id ,'pdl', false, $found ) );

    if ( ! $count && ! $found ) {

        $count = 0;

        $tree_ids = array_merge( array( $term->term_id ), get_term_children( $term->term_id, PDL_CATEGORY_TAX ) );

        if ( $tree_ids ) {
            $tt_ids = $wpdb->get_col( $wpdb->prepare( "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id IN (" . implode( ',', $tree_ids ) . ") AND taxonomy = %s", PDL_CATEGORY_TAX ) );

            if ( $tt_ids ) {
                $query = $wpdb->prepare( "SELECT COUNT(DISTINCT r.object_id) FROM {$wpdb->term_relationships} r INNER JOIN {$wpdb->posts} p ON p.ID = r.object_id WHERE p.post_status = %s and p.post_type = %s AND term_taxonomy_id IN (" . implode( ',', $tt_ids ) . ")", 'publish', PDL_POST_TYPE );

                $count = intval( $wpdb->get_var( $query ) );
            }
        }

        $count = apply_filters( '_pdl_padded_count', $count, $term );
    }

    if ( $return )
        return $count;

    $term->count = $count;
}

/**
 * @since 2.3
 * @access private
 */
function _pdl_list_categories_walk( $parent=0, $depth=0, $args ) {
    $term_ids = get_terms( PDL_CATEGORY_TAX,
                        array( 'orderby' => $args['orderby'],
                               'order' => $args['order'],
                               'hide_empty' => false,
                               'pad_counts' => false,
                               'parent' => is_object( $args['parent'] ) ? $args['parent']->term_id : intval( $args['parent'] ),
                               'fields' => 'ids' )
                        );

    $terms = array();
    foreach ( $term_ids as $term_id ) {
        $t = get_term( $term_id, PDL_CATEGORY_TAX );
        // 'pad_counts' doesn't work because of WP bug #15626 (see http://core.trac.wordpress.org/ticket/15626).
        // we need a workaround until the bug is fixed.
        _pdl_padded_count( $t );

        $terms[] = $t;
    }

    // filter empty terms
    if ( $args['hide_empty'] ) {
        $terms = array_filter( $terms, create_function( '$x', 'return $x->count > 0;' ) );
    }

    $html = '';

    if ( !$terms && $depth == 0 ) {
        if ( $args['no_items_msg'] )
            $html .= '<p>' . $args['no_items_msg'] . '</p>';
        return $html;
    }

    if ( $depth > 0 ) {
        $html .= str_repeat( "\t", $depth );

        if ( apply_filters( 'pdl_categories_list_anidate_children', true ) && $terms ) {
            $html .= '<ul class="children">';
        }
    }
    foreach ( $terms as &$term ) {
        $html .= '<li class="cat-item cat-item-' . $term->term_id . ' ' . apply_filters( 'pdl_categories_list_item_css', '', $term ) . ' ' . ( $depth > 0 ? 'subcat' : '' ) . '">';

        $item_html = '';
        $item_html .= '<a href="' . esc_url( get_term_link( $term ) ) . '" ';
        $item_html .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $term->description, $term ) ) ) . '" class="category-label" >';
        $item_html .= esc_attr( $term->name );
        $item_html .= '</a>';

        if ( $args['show_count'] ) {
            $count_str = ' (' . intval( $term->count ) . ')';
            $count_str = apply_filters( 'pdl_categories_item_count_str', $count_str, $term );
            $item_html .= $count_str;
        }

        $item_html = apply_filters( 'pdl_categories_list_item', $item_html, $term );
        $html .= $item_html;

        if ( !$args['parent_only'] ) {
            $args['parent'] = $term->term_id;
            if ( $subcats = _pdl_list_categories_walk( $term->term_id, $depth + 1, $args ) )
                $html .= $subcats;
        }

        $html .= '</li>';
    }

    if ( $depth > 0 ) {
        if ( apply_filters( 'pdl_categories_list_anidate_children', true ) && $terms ) {
            $html .= '</ul>';
        }
    }

    return $html;
}

 /**
 * Produces a list of directory categories following some configuration settings that are overridable.
 *
 * The list of arguments is below:
 *      'parent' (int|object) - Parent directory category or category ID.
 *      'orderby' (string) default is taken from PDL settings - What column to use for ordering the categories.
 *      'order' (string) default is taken from PDL settings - What direction to order categories.
 *      'show_count' (boolean) default is taken from PDL settings - Whether to show how many listings are in the category.
 *      'hide_empty' (boolean) default is False - Whether to hide empty categories or not.
 *      'parent_only' (boolean) default is False - Whether to show only direct childs of 'parent' or make a recursive list.
 *      'echo' (boolean) default is False - If True, the list will be printed in addition to returned by this function.
 *      'no_items_msg' (string) default is "No listing categories found." - Message to display when no categories are found.
 *
 * @param string|array $args array of arguments to be used while creating the list.
 * @return string HTML output.
 * @since 2.3
 * @see pdl_directory_categories()
 */
function pdl_list_categories( $args=array() ) {
    $args = wp_parse_args( $args, array(
        'parent' => null,
        'echo' => false,
        'orderby' => pdl_get_option( 'categories-order-by' ),
        'order' => pdl_get_option( 'categories-sort' ),
        'show_count' => pdl_get_option('show-category-post-count'),
        'hide_empty' => false,
        'parent_only' => false,
        'parent' => 0,
        'no_items_msg' => _x( 'No listing categories found.', 'templates', 'PDM' )
    ) );

    $html  =  '';

    if ( $categories = _pdl_list_categories_walk( 0, 0, $args ) ) {
        $attributes = apply_filters( 'pdl_categories_list_attributes', array(
            'class' => 'pdl-categories cf ' . apply_filters( 'pdl_categories_list_css', '' ),
            'data-breakpoints' => esc_attr( '{"tiny": [0,360], "small": [360,560], "medium": [560,710], "large": [710,999999]}' ),
            'data-breakpoints-class-prefix' => 'pdl-categories',
        ) );

        $html .= '<ul ' . trim( pdl_html_attributes( $attributes ) ) . '>';
        $html .= $categories;
        $html .= '</ul>';
    }

    $html = apply_filters( 'pdl_categories_list', $html );

    if ( $args['echo'] )
        echo $html;

    return $html;
}

function pdl_main_links( $buttons = null ) {
    if ( is_string( $buttons ) ) {
        if ( 'none' == $buttons ) {
            $buttons = array();
        } else if ( 'all' == $buttons ) {
            $buttons = array( 'directory', 'listings', 'create' );
        } else {
            $buttons = explode( ',', $buttons );
        }
    } else if ( ! is_array( $buttons ) ) {
        // Use defaults.
        $buttons = array();

        if ( pdl_get_option( 'show-directory-button' ) ) {
            $buttons[] = 'directory';
        }

        if ( pdl_get_option( 'show-view-listings' ) ) {
            $buttons[] = 'listings';
        }

        if (  pdl_get_option( 'show-submit-listing' ) ) {
            $buttons[] = 'create';
        }
    }

    $buttons = array_filter( array_unique( $buttons ) );

    if ( ! $buttons ) {
        return '';
    }

    if ( pdl_get_option( 'disable-submit-listing' ) ) {
        $buttons = array_diff( $buttons, array( 'create' ) );
    }

    $html = '';
    $buttons_count = 0;

    if ( in_array( 'directory', $buttons ) ) {
        $html .= sprintf( '<input id="pdl-bar-show-directory-button" type="button" value="%s" onclick="window.location.href = \'%s\'" class="button pdl-button" />',
                          __('Directory', 'PDM'),
                          pdl_url( '/' ) );
        $buttons_count++;
    }

    if ( in_array( 'listings', $buttons ) ) {
        $html .= sprintf( '<input id="pdl-bar-view-listings-button" type="button" value="%s" onclick="window.location.href = \'%s\'" class="button pdl-button" />',
                          __('View All Listings', 'PDM'),
                          pdl_url( 'all_listings' ) );
        $buttons_count++;
    }

    if ( in_array( 'create', $buttons ) ) {
        $html .= sprintf( '<input id="pdl-bar-submit-listing-button" type="button" value="%s" onclick="window.location.href = \'%s\'" class="button pdl-button" />',
                          __( 'Create A Listing', 'PDM' ),
                          pdl_url( 'submit_listing' ) );
        $buttons_count++;
    }

    if ( ! $html ) {
        return '';
    }

    $content = '<div class="pdl-main-links-container" data-breakpoints=\'{"tiny": [0,360], "small": [360,560], "medium": [560,710], "large": [710,999999]}\' data-breakpoints-class-prefix="pdl-main-links">';
    $content.= '<div class="pdl-main-links pdl-main-links-' . $buttons_count . '-buttons">' . apply_filters( 'pdl_main_links', $html ) . '</div>';
    $content.= '</div>';

    return $content;
}

function pdl_the_main_links( $buttons = null ) {
    echo pdl_main_links( $buttons );
}

function pdl_search_form() {
    $html  = '';
    $html .= sprintf( '<form id="pdmsearchform" action="%s" method="GET" class="pdl-search-form">',
                      pdl_url( 'search' ) );
        $html .= '<input type="hidden" name="pdl_view" value="search" />';

    if ( ! pdl_rewrite_on() ) {
        $html .= sprintf( '<input type="hidden" name="page_id" value="%d" />', pdl_get_page_id( 'main' ) );
    }

    $html .= '<input type="hidden" name="dosrch" value="1" />';
    $html .= '<input id="intextbox" maxlength="150" name="q" size="20" type="text" value="" />';
    $html .= sprintf('<input id="pdmsearchsubmit" class="submit pdl-button pdl-submit" type="submit" value="%s" />',
                     _x('Search Listings', 'templates', 'PDM'));
    $html .= sprintf('<a href="%s" class="advanced-search-link">%s</a>',
                     esc_url( pdl_url( 'search' ) ),
                     _x('Advanced Search', 'templates', 'PDM'));
    $html .= '</form>';

    return $html;
}

function pdl_the_search_form() {
    if (pdl_get_option('show-search-listings'))
        echo pdl_search_form();
}

function pdl_the_listing_excerpt() {
    echo pdl_render_listing(null, 'excerpt');
}

function pdl_listing_sort_options() {
    if ( pdl_get_option( 'listings-sortbar-enabled' ) ) {
        $sort_options = apply_filters( 'pdl_listing_sort_options', array() );
    } else {
        $sort_options = array();
    }

    if (!$sort_options)
        return apply_filters( 'pdl_listing_sort_options_html', '' );

    $current_sort = pdl_get_current_sort_option();

    $html  = '';
    $html .= '<div class="pdl-listings-sort-options pdl-hide-on-mobile">';
    $html .= _x('Sort By:', 'templates sort', 'PDM') . ' ';

    foreach ($sort_options as $id => $option) {
        $default_order = isset( $option[2] ) && !empty( $option[2] ) ? strtoupper( $option[2] ) : 'ASC';

        $html .= sprintf('<span class="%s %s"><a href="%s" title="%s">%s</a> %s</span>',
                        $id,
                        ($current_sort && $current_sort->option == $id) ? 'current': '',
                        esc_url( ($current_sort && $current_sort->option == $id) ? add_query_arg('pdl_sort', ($current_sort->order == 'ASC' ? '-' : '') . $id) : add_query_arg('pdl_sort', ( $default_order == 'DESC' ? '-' : '' )  . $id ) ),
                        isset( $option[1] ) && !empty( $option[1] ) ? esc_attr( $option[1] ) : esc_attr( $option[0] ),
                        $option[0],
                        ($current_sort && $current_sort->option == $id) ? ($current_sort->order == 'ASC' ? '↑' : '↓') : ( $default_order == 'DESC' ? '↓' : '↑' )
                        );
        $html .= ' | ';
    }
    $html = substr($html, 0, -3);
    $html .= '<br />';

    if ($current_sort)
        $html .= sprintf( '(<a href="%s" class="reset">%s</a>)', remove_query_arg( 'pdl_sort' ), _x( 'Reset', 'sort', 'PDM' ) );
    $html .= '</div>';

    $html .= '<div class="pdl-listings-sort-options pdl-show-on-mobile">';

    $html .= '<select class="">';
    $html .= '<option value="0" class="header-option">' . _x('Sort By:', 'templates sort', 'PDM') . '</option>';

    foreach ( $sort_options as $id => $option ) {
        $default_order = isset( $option[2] ) && !empty( $option[2] ) ? strtoupper( $option[2] ) : 'ASC';

        $html .= sprintf( '<option value="%s" %s>%s%s %s</option>',
                          esc_url( ( $current_sort && $current_sort->option == $id ) ? add_query_arg( 'pdl_sort', ( $current_sort->order == 'ASC' ? '-' : '' ) . $id ) : add_query_arg('pdl_sort', ( $default_order == 'DESC' ? '-' : '' )  . $id ) ),
                          ( $current_sort && $current_sort->option == $id ) ? 'selected="selected"' : '',
                          str_repeat( '&nbsp;', 3 ),
                          $option[0],
                          ( $current_sort && $current_sort->option == $id ) ? ( $current_sort->order == 'ASC' ? '↑' : '↓' ) : ( $default_order == 'DESC' ? '↓' : '↑' ) );
    }

    if ( $current_sort )
        $html .= sprintf( '<option value="%s" class="header-option">%s</option>',
                          remove_query_arg( 'pdl_sort' ),
                          _x( '(Reset)', 'sort', 'PDM' ) );

    $html .= '</select>';
    $html .= '</div>';

    return apply_filters( 'pdl_listing_sort_options_html', $html );
}

function pdl_the_listing_sort_options() {
    echo pdl_listing_sort_options();
}

/**
 * @deprecated since 2.2.1
 */
function pdl_bar($parts=array()) {
    $parts = wp_parse_args($parts, array(
        'links' => true,
        'search' => false
    ));

    $html  = '<div class="pdl-bar cf">';
    $html .= apply_filters('pdl_bar_before', '', $parts);

    if ($parts['links'])
        $html .= pdl_main_links();
    if ($parts['search'])
        $html .= pdl_search_form();

    $html .= apply_filters('pdl_bar_after', '', $parts);
    $html .= '</div>';

    return $html;
}

/**
 * @deprecated since 2.2.1
 */
function pdl_the_bar($parts=array()) {
    echo pdl_bar($parts);
}

/**
 * Displays the listing main image.
 * @since 2.3
 */
function pdl_listing_thumbnail( $listing_id=null, $args=array() ) {
    if ( ! $listing_id ) {
        $listing_id = apply_filters( 'pdl_listing_images_listing_id', get_the_ID() );
    }

    $listing = PDL_Listing::get( $listing_id );

    $main_image = $listing->get_thumbnail();

    if ( $main_image ) {
        $thumbnail_id = $main_image->ID;
    } else {
        $thumbnail_id = 0;
    }

    $args = wp_parse_args( $args, array(
        'link' => 'picture',
        'class' => '',
        'echo' => false,
    ) );

    $image_img = '';
    $image_link = '';
    $image_title = '';
    $listing_link_in_new_tab = '';
    $image_classes = 'pdl-thumbnail attachment-pdl-thumb ' . $args['class'];

    if ( !$main_image && function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $listing_id ) ) {
        $image_img = get_the_post_thumbnail( $listing_id, 'pdl-thumb' );
    } elseif( !$main_image && pdl_get_option( 'use-default-picture' ) ) {
        $image_img = sprintf( '<img src="%s" alt="%s" title="%s" border="0" width="%d" class="%s" />',
                              PDL_URL . 'assets/images/default-image-big.gif',
                              get_the_title( $listing_id ),
                              get_the_title( $listing_id ),
                              pdl_get_option( 'thumbnail-width' ),
                              $image_classes
                            );
        $image_link = $args['link'] == 'picture' ? PDL_URL . 'assets/images/default-image-big.gif' : '';
    } elseif ( $main_image ) {
        _pdl_resize_image_if_needed( $main_image->ID );
        $image_img = wp_get_attachment_image( $main_image->ID,
                                              'pdl-thumb',
                                              false,
                                              array(
                                                'alt' => get_the_title( $listing_id ),
                                                'title' => get_the_title( $listing_id ),
                                                'class' => $image_classes
                                                )
                                             );

        if ( $args['link'] == 'picture' ) {
            $full_image_data = wp_get_attachment_image_src( $main_image->ID, 'pdl-large' );
            $image_link = $full_image_data[0];
        }

        $image_title = get_post_meta( $main_image->ID, '_pdl_image_caption', true );
    }

    if ( !$image_link && $args['link'] == 'listing' ){
        $image_link = get_permalink( $listing_id );
        $listing_link_in_new_tab = pdl_get_option( 'listing-link-in-new-tab' ) ? '"_blank" rel="noopener"' : '"_self"';
    }

    if ( $image_img ) {
        if ( !$image_link ) {
            return $image_img;
        } else {
            $image_link = apply_filters( 'pdl_listing_thumbnail_link', $image_link, $listing_id, $args );

            if ( ! $image_link ) {
                return sprintf( '<div class="listing-thumbnail">%s</div>',
                                $image_img );
            }

            return sprintf( '<div class="listing-thumbnail"><a href="%s" target=%s class="%s" title="%s" %s>%s</a></div>',
                            $image_link,
                            $listing_link_in_new_tab,
                            $args['link'] == 'picture' ? 'thickbox' : '',
                            $image_title,
                            $args['link'] == 'picture' ? 'data-lightbox="pdlgal" rel="pdlgal"' : '',
                            $image_img );
        }
    }

    return '' ;
}


class PDL_ListingFieldDisplayItem {
    private $listing_id = 0;
    private $display = '';

    private $html_ = '';
    private $html_value_ = '';
    private $value_ = null;

    public $id = 0;
    public $field;

    public function __construct( &$field, $listing_id = 0, $display ) {
        $this->field = $field;
        $this->id = $this->field->get_id();
        $this->listing_id = $listing_id;
        $this->display = $display;
    }

    public function __get( $key ) {
        switch ( $key ) {
            case 'html':
                if ( $this->html_ )
                    return $this->html_;

                $this->html_ = $this->field->display( $this->listing_id, $this->display );
                return $this->html_;
                break;

            case 'html_value':
                if ( $this->html_value_ )
                    return $this->html_value_;

                $this->html_value_ = $this->field->html_value( $this->listing_id );
                return $this->html_value_;
                break;

            case 'value':
                if ( $this->value_ )
                    return $this->value_;

                $this->value_ = $this->field->value( $this->listing_id );
                return $this->value_;
                break;

            default:
                break;
        }
    }

    public static function prepare_set( $listing_id, $display ) {
        $res = (object) array( 'fields' => array(), 'social' => array() );

        $form_fields = pdl_get_form_fields();
        $form_fields = apply_filters_ref_array( 'pdl_render_listing_fields', array( &$form_fields, $listing_id ) );

        foreach ( $form_fields as &$f ) {
            if ( ! $f->display_in( $display ) )
                continue;

            if ( $f->display_in( 'social' ) )
                $res->social[ $f->get_id() ] = new self( $f, $listing_id, 'social' );
            else
                $res->fields[ $f->get_id() ] = new self( $f, $listing_id, $display );
        }

        return $res;
    }

    public static function walk_set( $prop, $fields = array() ) {
        $res = array();

        foreach ( $fields as $k => &$f )
            $res[ $k ] = $f->{$prop};

        return $res;
    }
}

/**
 * @since 5.0
 */
function pdl_the_main_box( $args = array() ) {
    echo pdl_main_box( $args = array() );
}

/**
 * @since 5.0
 */
function pdl_main_box( $args = null ) {
    $defaults = array(
        'buttons' => null,
    );
    $args = wp_parse_args( $args, $defaults );

    $extra_fields = pdl_capture_action( 'pdl_main_box_extra_fields' );
    $search_url = esc_url( add_query_arg( 'pdl_view', 'search', pdl_get_page_link( 'main' ) ) );
    $no_cols = 1;

    if ( $extra_fields )
        $no_cols = 2;

    $template_vars = compact( 'extra_fields', 'search_url', 'no_cols' );
    $template_vars = array_merge( $template_vars, $args );

    $html = pdl_render( 'main-box', $template_vars );
    return $html;
}
