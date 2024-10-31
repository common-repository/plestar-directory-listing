<?php
require_once ( PDL_PATH . 'includes/helpers/class-field-display-list.php' );

/**
 * @since 4.0
 */
class PDL_Listing_Display_Helper {


    public static function excerpt() {
        static $n = 0;

        global $post;

        $vars = array();
        $vars = array_merge( $vars, array( 'even_or_odd' => ( ( $n & 1 ) ? 'odd' : 'even' ) ) );
        $vars = array_merge( $vars, self::basic_vars( $post->ID ) );
        $vars = array_merge( $vars, self::fields_vars( $post->ID, 'excerpt' ) );
        $vars = array_merge( $vars, self::images_vars( $post->ID, 'excerpt' ) );
        $vars = array_merge( $vars, self::css_classes( $post->ID, 'excerpt' ) );
        $vars['listing_css_class'] .= ' ' . $vars['even_or_odd'];

        $vars = apply_filters( 'pdl_listing_template_vars', $vars, $post->ID );
        $vars = apply_filters( 'pdl_excerpt_template_vars', $vars, $post->ID );

        $n++;

        // TODO: what do we do with 'pdl_excerpt_listing_fields' ?
        return pdl_x_render( 'excerpt', $vars );
    }

    public static function single() {
        global $post;

        $vars = array();
        $vars = array_merge( $vars, self::basic_vars( $post->ID ) );
        $vars = array_merge( $vars, self::fields_vars( $post->ID, 'listing' ) );
        $vars = array_merge( $vars, self::images_vars( $post->ID, 'listing' ) );
        $vars = array_merge( $vars, self::css_classes( $post->ID, 'single' ) );

        if ( ! empty( $vars['images'] ) && $vars['images']->main )
            $vars['listing_css_class'] .= ' with-image';

        $vars = apply_filters( 'pdl_listing_template_vars', $vars, $post->ID );
        $vars = apply_filters( 'pdl_single_template_vars', $vars, $post->ID );

        // TODO: is this really used? can it be changed to something else?
        // 'listing_fields' => apply_filters('pdl_single_listing_fields', $listing_fields, $post->ID), This is 
        // complete HTML
        $html  = '';
        $html .= pdl_x_render( 'single', $vars );
        $html .= '<script type="application/ld+json">';
        $html .= json_encode( self::schema_org( $vars ) );
        $html .= '</script>';

        return $html;
    }

    private static function basic_vars( $listing_id ) {
        $listing = PDL_Listing::get( $listing_id );

        $vars = array();
        $vars['listing_id'] = $listing_id;
        $vars['listing'] = $listing;
        $vars['is_sticky'] = ( 'normal' != $listing->get_sticky_status() );
        $vars['sticky_tag'] = '';
        $vars['title'] = the_title( null, null, false );

        if ( $vars['is_sticky'] )
            $vars['sticky_tag'] = pdl_x_render( 'listing sticky tag', array( 'listing' => $listing ) );

        return $vars;
    }

    private static function css_classes( $listing_id, $display ) {
        $vars = array();
        $vars['listing_css_id'] = 'pdl-listing-' . $listing_id;

        $classes   = array();
        $classes[] = 'pdl-listing-' . $listing_id;
        $classes[] = 'pdl-listing';
        $classes[] = $display;
        $classes[] = 'pdl-' . $display;
        $classes[] = 'pdl-listing-' . $display;

        // Fee-related classes.
        if ( $fee = PDL_Listing::get( $listing_id )->get_fee_plan() ) {
            $classes[] = 'pdl-listing-plan-id-' . $fee->fee_id;
            $classes[] = 'pdl-listing-plan-' . PDL_Utils::normalize( $fee->fee_label );

            if ( $fee->is_sticky ) {
                $classes[] = 'sticky';
                $classes[] = 'pdl-listing-is-sticky';
            }
        }

        foreach ( PDL_Listing::get( $listing_id )->get_categories( 'ids' ) as $category_id ) {
                $classes[] = 'pdl-listing-category-id-' . $category_id;
        }

        $vars['listing_css_class']  = implode( ' ', $classes );
        $vars['listing_css_class'] .= apply_filters( 'pdl_' . $display . '_view_css', '', $listing_id );

        return $vars;
    }

    private static function fields_vars( $listing_id, $display ) {
        $all_fields = pdl_get_form_fields();
        $fields = apply_filters_ref_array( 'pdl_render_listing_fields', array( &$all_fields, $listing_id ) );

        $list = new PDL_Field_Display_List( $listing_id, $display, $fields );
        $list->freeze();

        return array( 'fields' => $list );
    }

    private static function images_vars( $listing_id, $display ) {
        $vars = array();
        $vars['images'] = (object) array( 'main' => false,
                                          'extra' => array(),
                                          'thumbnail' => false );

        if ( ! pdl_get_option( 'allow-images' ) )
            return $vars;

        $listing_id = apply_filters( 'pdl_listing_images_listing_id', $listing_id );
        $listing = PDL_Listing::get( $listing_id );

        // Thumbnail.
        if ( pdl_get_option( 'show-thumbnail' ) ) {
            $thumb = new StdClass();
            $thumb->html = pdl_listing_thumbnail( null, 'link=listing&class=pdmthumbs pdl-excerpt-thumbnail' );

            $vars['images']->thumbnail = $thumb;
        }

        // Main image.
        $thumbnail_id = $listing->get_thumbnail_id();
        $data_main = wp_get_attachment_image_src( $thumbnail_id, 'pdl-large', false );

        if ( $thumbnail_id ) {
            $main_image = new StdClass();
            $main_image->id = $thumbnail_id;
            $main_image->html = pdl_listing_thumbnail( $listing_id, 'link=picture&class=pdl-single-thumbnail' );
            $main_image->url = $data_main[0];
            $main_image->width = $data_main[1];
            $main_image->height = $data_main[2];
        } else {
            $main_image = false;
        }

        $vars['images']->main = $main_image;

        // Other images.
        $listing_images = $listing->get_images( 'ids' );

        foreach ( $listing_images as $img_id ) {
            // Correct size of thumbnail if needed.
            _pdl_resize_image_if_needed( $img_id );

            if ( $img_id == $thumbnail_id )
                continue;

            $data = wp_get_attachment_image_src( $img_id, 'pdl-large', false );

            $image = new StdClass();
            $image->id = $img_id;
            $image->url = $data[0];
            $image->width = $data[1];
            $image->height = $data[2];
            $image->html = sprintf( '<a href="%s" class="thickbox" data-lightbox="pdlgal" rel="pdlgal" target="_blank" rel="noopener" title="%s">%s</a>',
                                    $image->url,
                                    get_post_meta( $img_id, '_pdl_image_caption', true ),
                                    wp_get_attachment_image( $image->id, 'pdl-thumb', false, array(
                                            'class' => 'pdl-thumbnail size-thumbnail',
                                            'alt' => the_title( null, null, false ),
                                            'title' => the_title( null, null, false )
                                        ) ) );

            $vars['images']->extra[] = $image;
        }

        return $vars;
    }

    private static function schema_org( $vars ) {
        $schema = array();
        $schema['@context'] = 'http://schema.org';
        $schema['@type'] = 'LocalBusiness';
        $schema['name'] = $vars['title'];
        $schema['url'] = get_permalink( $vars['listing_id'] );
        $schema['image'] = ! empty( $vars['images']->main ) ? $vars['images']->main->url : '';
        $schema['priceRange'] = '$$';

        $fields = $vars['fields'];
        $fsx = array();
        foreach ( $fields as $f ) {
            $field_schema = $f->field->get_schema_org( $vars['listing_id'] );

            if ( ! $field_schema )
                continue;

            foreach ( $field_schema as $key => $value ) {
                if ( ! $value )
                    continue;

                if ( is_array( $value ) )
                    $schema[ $key ] = array_merge( isset( $schema[ $key ] ) ? $schema[ $key ] : array(), $value );
                else
                    $schema[ $key ] = $value;
            }
        }

        $schema = apply_filters( 'pdl_listing_schema_org', $schema );

        return $schema;
    }

}

/**
 * @since 4.0
 */
class PDL_Listing_Display_Image {
}
