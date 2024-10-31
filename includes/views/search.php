<?php
require_once( PDL_PATH . 'includes/helpers/class-listing-search.php' );


class PDL__Views__Search extends PDL__View {

    public function get_title() {
        return _x( 'Find A Listing', 'views', 'PDM' );
    }

    public function dispatch() {
        $searching = ( ! empty( $_GET ) && ( isset( $_GET['kw'] ) || ! empty( $_GET['dosrch'] ) ) );
        $search = null;

        $form_fields = pdl_get_form_fields( array( 'display_flags' => 'search', 'validators' => '-email' ) );

        if ( $searching ) {
            $_GET = stripslashes_deep( $_GET );

            $validation_errors = array();
            if ( ! empty( $_GET['dosrch'] ) ) {
                // Validate fields that are required.
                foreach ( $form_fields as $field ) {
                    if ( $field->has_validator( 'required-in-search' ) ) {
                        $value = $field->value_from_GET();

                        if ( ! $value || $field->is_empty_value( $value ) ) {
                            $validation_errors[] = sprintf( _x( '"%s" is required.', 'search', 'PDM' ), $field->get_label() );
                        }
                    }
                }
            }

            if ( ! $validation_errors ) {
                $search = PDL__Listing_Search::from_request( $_GET );
                $search->execute();
            } else {
                $searching = false;
            }
        }

        $search_form = '';
        $fields = '';
        foreach ( $form_fields as &$field ) {
            $field_value = null;

            if ( $search ) {
                $terms = $search->get_original_search_terms_for_field( $field );

                if ( $terms )
                    $field_value = array_pop( $terms );
            }

            $fields .= $field->render( $field->convert_input( $field_value ), 'search' );
        }

        if ( $searching ) {
            $args = array(
                'post_type' => PDL_POST_TYPE,
                'posts_per_page' => pdl_get_option( 'listings-per-page' ) > 0 ? pdl_get_option( 'listings-per-page' ) : -1,
                'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
                'post__in' => $search->get_results() ? $search->get_results() : array( 0 ),
                'orderby' => pdl_get_option( 'listings-order-by', 'date' ),
                'order' => pdl_get_option( 'listings-sort', 'ASC' ),
                'pdl_main_query' => true
            );
            $args = apply_filters( 'pdl_search_query_posts_args', $args, $search );

            query_posts( $args );
            pdl_push_query( $GLOBALS['wp_query'] );
        }

        if ( ( $searching && 'none' != pdl_get_option( 'search-form-in-results' ) ) || ! $searching ) {
            $search_form = pdl_render_page(
                PDL_PATH . 'templates/search-form.tpl.php',
                array(
                    'fields' => $fields,
                    'validation_errors' => ! empty( $validation_errors ) ? $validation_errors : array(),
                    'return_url' => ( ! empty( $this->return_url ) ? $this->return_url : '' )
                )
            );
        }

        if ( $searching && have_posts() ) {
            $results  = '';
            $results .= pdl_capture_action( 'pdl_before_search_results' );
            $results .= pdl_x_render( 'listings', array( '_parent' => 'search',
                                                           'query' => pdl_current_query() ) );
            $results .= pdl_capture_action( 'pdl_after_search_results' );
        } else {
            $results = '';
        }

        $html = pdl_x_render( 'search',
                                array( 'search_form' => $search_form,
                                       'search_form_position' => pdl_get_option( 'search-form-in-results' ),
                                       'fields' => $fields,
                                       'searching' => $searching,
                                       'results' => $results
                                   ) );

        if ( $searching ) {
            wp_reset_query();
            pdl_pop_query();
        }

        return $html;
    }

}
