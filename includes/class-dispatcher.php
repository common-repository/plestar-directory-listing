<?php
require_once( PDL_PATH . 'includes/class-view.php' );

/**
 * @since 4.0
 */
class PDL__Dispatcher {

    private $current_view = '';
    private $current_view_obj = false;
    private $output = '';


    public function __construct() {
        add_action( 'wp', array( $this, '_lookup_current_view' ) );
        add_action( 'template_redirect', array( $this, '_execute_view' ), 11 );
        add_action( 'wp_enqueue_scripts', array( $this, '_enqueue_view_scripts' ) );

        add_action( 'wp_ajax_pdl_ajax', array( $this, '_ajax_dispatch' ) );
        add_action( 'wp_ajax_nopriv_pdl_ajax', array( $this, '_ajax_dispatch' ) );
    }

    public function _lookup_current_view( $wp ) {
        if ( is_admin() )
            return;

        global $wp_query;

        $this->current_view = '';

        if ( ! $wp_query->is_main_query() )
            return;

        if ( ! empty( $wp_query->pdl_view ) )
            $this->current_view = $wp_query->pdl_view;

        // if ( ! $wp_query->pdl_is_main_page ) {
        //     return;
        // }

        // If the page contains the submit listing shortcode, dispatch it as if it were the current view.
        // TODO: this is a very special case that we should probably generalize somehow for other shortcodes.
        if ( ! empty( $GLOBALS['post'] ) && 'page' == $GLOBALS['post']->post_type && ! empty( $GLOBALS['post']->post_content ) ) {
            $submit_shortcodes = array( 'plestardirectory-submit-listing', 'plestardirectory-submitlisting', 'directory-listing-submitlisting', 'directory-listing-submit-listing', 'PLSBUSMANADDLISTING' );

            foreach ( $submit_shortcodes as $test_shortcode ) {
                if ( has_shortcode( $GLOBALS['post']->post_content, $test_shortcode ) ) {
                    $this->current_view = 'submit_listing';
                    break;
                }
            }
        }

        $this->current_view = apply_filters( 'pdl_current_view', $this->current_view );
        $this->current_view_obj = $this->load_view( $this->current_view );

        // if ( ! $this->current_view_obj )
        //     $this->current_view = '';

        pdl_debug( '[Dispatching Details] view = ' . $this->current_view . ', is_main_page = ' . $wp_query->pdl_is_main_page );
    }

    public function _execute_view( $template ) {
        global $wp_query;

        if ( ! $this->current_view )
            return $template;

        if ( ! $this->current_view_obj ) {
            $wp_query->is_404 = true;
            return $template;
        }

        do_action( 'pdl_before_dispatch' );
        $res = $this->current_view_obj->dispatch();

        if ( is_string( $res ) )
            $this->output = $res;

        do_action( 'pdl_after_dispatch' );

        return $template;
    }

    public function _enqueue_view_scripts() {
        if ( ! $this->current_view_obj )
            return;

        $this->current_view_obj->enqueue_resources();
    }

    /**
     * @since 5.0
     */
    public function _ajax_dispatch() {
        if ( empty( $_REQUEST['handler'] ) )
            return;

        $handler = trim( $_REQUEST['handler'] );
        $handler = PDL__Utils::normalize( $handler );

        $parts = explode( '__', $handler );
        $view_name = $parts[0];
        $function = isset( $parts[1] ) ? $parts[1] : '';

        $view = $this->load_view( $view_name );
        if ( ! $view )
            return;

        if ( ! $function )
            $function = 'ajax_dispatch';
        else
            $function = 'ajax_' . $function;

        if ( ! method_exists( $view, $function ) )
            return;

        do_action( 'pdl_before_ajax_dispatch', $handler );

        return call_user_func( array( $view, $function ) );
    }

    public function get_view_locations() {
        $dirs = array();
        $dirs[] = PDL_PATH . 'includes/views/';
        $dirs[] = PDL_PATH . 'core/views/';

        return apply_filters( 'pdl_view_locations', $dirs );
    }

    public function load_view( $view_name, $args = null ) {
        // TODO: add some filters so plugins can override default view loading.
        $filenames = array( $view_name . '.php',
                            'views-' . $view_name . '.php' );

        foreach ( $this->get_view_locations() as $dir ) {
            foreach ( $filenames as $f ) {
                $path = wp_normalize_path( PDL_FS::join( $dir, $f ) );

                if ( ! file_exists( $path ) )
                    continue;

                $classname = 'PDL__Views__' . implode( '_', array_map( 'ucfirst', explode( '_', str_replace( '.php', '', $f ) ) ) );

                if ( ! class_exists( $classname ) )
                    include( $path );

                if ( ! class_exists( $classname ) )
                    continue;

                if ( is_null( $args ) ) {
                    return new $classname;
                } else {
                    // TODO: this is terrible. Maybe we can use an `init()` function for all views and use that instead.
                    // That way all views can be instantiated without arguments.
                    $class = new ReflectionClass( $classname );
                    return $class->newInstanceArgs( array( $args ) );
                }
            }
        }

        return false;
    }

    public function current_view() {
        return $this->current_view;
    }

    public function current_view_object() {
        return $this->current_view_obj;
    }

    public function current_view_output() {
        return $this->output;
    }


}


