<?php
/*
 * Deprecated functionality.
 */

$wpdirlistconfigoptionsprefix = "wpdirlist";

$pdmposttype = "pdl_listing";
$pdmposttypecategory = "pdl_category";
$pdmposttypetags = "pdl_tag";

define('PLSBUSMAN_TEMPLATES_PATH', PDL_PATH . '/includes/compatibility/templates');


/* template-related */
function wpdirlist_single_listing_details() {
    echo wpdirlist_post_single_listing_details();
}

function wpdirlist_post_single_listing_details() {
    return pdl_render_listing( null, 'single' );
}

function wpdirlist_the_listing_title() {
    if ( $field = pdl_get_form_fields( array( 'association' => 'title', 'unique' => true ) ) )
        return $field->display( get_the_ID() );
}

function wpdirlist_the_listing_excerpt() {
    if ( $field = pdl_get_form_fields( array( 'association' => 'excerpt', 'unique' => true ) ) )
        return $field->display( get_the_ID() );
}

function wpdirlist_the_listing_content() {
    if ( $field = pdl_get_form_fields( array( 'association' => 'content', 'unique' => true ) ) )
        return $field->display( get_the_ID() );
}

function wpdirlist_the_listing_category() {
    if ( $field = pdl_get_form_fields( array( 'association' => 'category', 'unique' => true ) ) )
        return $field->display( get_the_ID() );
}

function wpdirlist_the_listing_tags() {
    if ( $field = pdl_get_form_fields( array( 'association' => 'tags', 'unique' => true ) ) )
        return $field->display( get_the_ID() );
}

function wpdirlist_the_listing_meta($excerptorsingle) {
    $html = '';
    $fields = pdl_get_form_fields( array( 'association' => 'meta' ) );

    foreach ( $fields as &$f ) {
        if ( $excerptorsingle == 'excerpt' && !$field->display_in( 'excerpt' ) )
            continue;

        $html .= $f->display( get_the_ID() );
    }

    return $html;
}

function wpdirlist_display_excerpt($deprecated=null) {
    echo wpdirlist_post_excerpt($deprecated);
}

function wpdirlist_post_excerpt($deprecated=null) {
    return pdl_render_listing( null, 'excerpt' );
}


/**
 * @deprecated since 2.3
 */
function wpdirlist_display_main_image() {
    echo wpdirlist_post_main_image();
}

/**
 * @deprecated since 2.3
 */
function wpdirlist_post_main_image() {
    return pdl_listing_thumbnail();
}

function wpdirlist_display_extra_thumbnails() {
    echo wpdirlist_post_extra_thumbnails();
}

function wpdirlist_post_extra_thumbnails() {
    $html = '';

    $listing = PDL_Listing::get( get_the_ID() );
    $thumbnail_id = $listing->get_thumbnail_id();
    $images = $listing->get_images();

    if ($images) {
        $html .= '<div class="extrathumbnails">';

        foreach ($images as $img) {
            if ($img->ID == $thumbnail_id)
                continue;

            $html .= sprintf('<a class="thickbox" href="%s"><img class="pdmthumbs" src="%s" alt="%s" title="%s" border="0" /></a>',
                             wp_get_attachment_url($img->ID),
                             wp_get_attachment_thumb_url($img->ID),
                             the_title(null, null, false),
                             the_title(null, null, false)
                             );
        }

        $html .= '</div>';      
    }

    return $html;
}

// Display the listing fields in excerpt view
function wpdirlist_display_the_listing_fields() {
    global $post;

    $html = '';

    foreach ( pdl_formfields_api()->get_fields() as $field ) {
        if ( !$field->display_in( 'excerpt' ) )
            continue;

        $html .= $field->display( $post->ID, 'excerpt' );
    }

    return $html;
}

//Display the listing thumbnail
function wpdirlist_display_the_thumbnail() {
    return pdl_listing_thumbnail();
}

function wpdirlist_sticky_loop() { return; }

function wpdirlist_latest_listings($numlistings) {
    return pdl_latest_listings($numlistings);
}

function wpdirlist_post_catpage_title() {
    $categories = PDL_CATEGORY_TAX;

    if ( get_query_var($categories) ) {
        $term = get_term_by('slug', get_query_var($categories), $categories);
    } else if ( get_query_var('taxonomy') == $categories ) {
        $term = get_term_by('slug', get_query_var('term'), $categories);
    } elseif ( get_query_var('taxonomy') == PDL_TAGS_TAX ) {
        $term = get_term_by('slug', get_query_var('term'), PDL_TAGS_TAX);
    }

    return esc_attr($term->name);
}

function wpdirlist_list_categories() {
    echo wpdirlist_post_list_categories();
}

function wpdirlist_post_list_categories() {
    return pdl_directory_categories();
}

/* deprecated since 2.1.4 */
function pdl_sticky_loop($category_id=null, $taxonomy=null) { return ''; }

/**
 * Small compatibility layer with old forms API. To be removed in later releases.
 * @deprecated
 * @since 2.3
 */
function pdl_get_formfields() {
    global $pdl;
    $res = array();

    foreach ( $pdl->formfields->get_fields() as $new_field ) {
        $field = new StdClass();
        $field->id = $new_field->get_id();
        $field->label = $new_field->get_label();
        $field->association = $new_field->get_association();
        $field->type = $new_field->get_field_type()->get_id();

        $res[] = $field;
    }

    return $res;
}


/**
 * TODO: There doesn't seem to be a replacement for this deprecated function.
 *
 * @deprecated
 * @since 2.3
 */
function wpdirlist_get_the_business_email($post_id) {
    $email_mode = pdl_get_option( 'listing-email-mode' );
    
    $email_field_value = '';
    if ( $email_field = pdl_get_form_fields( 'validators=email&unique=1' ) ) {
        $email_field_value = trim( $email_field->plain_value( $post_id ) );
    }

    if ( $email_mode == 'field' && !empty( $email_field_value ) )
        return $email_field_value;

    $author_email = '';
    $post = get_post( $post_id );
    $author_email = trim( get_the_author_meta( 'user_email', $post->post_author ) );

    if ( empty( $author_email ) && !empty( $email_field_value ) )
        return $email_field_value;
    
    return $author_email ? $author_email : '';
}

/**
 * @deprecated since 2.3
 */
function pdl_post_type() {
    return PDL_POST_TYPE;
}

/**
 * @deprecated since 2.3
 */
function pdl_categories_taxonomy() {
    return PDL_CATEGORY_TAX;
}

/**
 * Finds a fee by its ID. The special ID of 0 is reserved for the "free fee".
 * @param int $fee_id fee ID
 * @return object a fee object or NULL if nothing is found
 * @since 3.0.3
 * @deprecated since 3.7. Use {@link pdl_get_fee_plan()} instead.
 */
function pdl_get_fee( $fee_id ) {
    return pdl_get_fee_plan( $fee_id );
}

/**
 * Finds fees available for one or more directory categories.
 * @param int|array $categories term ID or array of term IDs
 * @return object|
 * @since 3.0.3
 * @deprecated since 3.7. Use {@link pdl_get_fee_plans()} instead.
 */
function pdl_get_fees_for_category( $categories=null ) {
    return pdl_get_fee_plans( array( 'categories' => $categories ) );
}

/**
 * @deprecated since next-release
 */
function pdl_categories_list($parent=0, $hierarchical=true) {
    $terms = get_categories(array(
        'taxonomy' => PDL_CATEGORY_TAX,
        'parent' => $parent,
        'orderby' => 'name',
        'hide_empty' => 0,
        'hierarchical' => 0
    ));

    if ($hierarchical) {
        foreach ($terms as &$term) {
            $term->subcategories = pdl_categories_list($term->term_id, true);
        }
    }

    return $terms;
}

/**
 * @since 2.3
 * @deprecated since fees-revamp
 */
function pdl_has_module( $module ) {
    return pdl()->modules->is_loaded( $module );
}

function pdl_listing_upgrades_api() {
    return new PDL_NoopObject();
}
