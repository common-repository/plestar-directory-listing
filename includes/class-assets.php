<?php
/**
 * @since 5.0
 */
class PDL__Assets {

    public function __construct() {
    	
        add_action( 'wp_enqueue_scripts', array( $this, 'register_common_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_common_scripts' ) );

        // Scripts & styles.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css_override' ), 9999, 0 );

        $this->register_image_sizes();
    }

    /**
     * Registers scripts and styles that can be used either by frontend or backend code.
     * The scripts are just registered, not enqueued.
     *
     * @since 3.4
     */
    public function register_common_scripts() {
        wp_register_script(
            'jquery-file-upload-iframe-transport',
            PDL_URL . 'vendors/jQuery-File-Upload-9.5.7/js/jquery.iframe-transport.min.js',
            array(),
            '9.5.7'
        );

        wp_register_script(
            'jquery-file-upload',
            PDL_URL . 'vendors/jQuery-File-Upload-9.5.7/js/jquery.fileupload.min.js',
            array( 'jquery', 'jquery-ui-widget', 'jquery-file-upload-iframe-transport' ),
            '9.5.7'
        );

        $this->maybe_register_script(
            'breakpoints.js',
            PDL_URL . 'vendors/jquery-breakpoints.min.js',
            array( 'jquery' ),
            '0.0.11',
            true
        );

        // Views
        wp_register_script(
            'pdl-checkout',
            PDL_URL . 'assets/js/checkout.js',
            array( 'pdl-js' ),
            PDL_VERSION
        );

        // Drag & Drop.
        wp_register_style( 'pdl-dnd-upload', PDL_URL . 'assets/css/dnd-upload.min.css', array(), PDL_VERSION );
        wp_register_script( 'pdl-dnd-upload', PDL_URL . 'assets/js/dnd-upload.min.js', array( 'jquery-file-upload' ), PDL_VERSION );

        // Select2.
        wp_register_style(
            'pdl-js-select2-css',
            PDL_URL . 'vendors/select2-4.0.3/css/select2.min.css',
            array(),
            '4.0.3'
        );

        wp_register_script(
            'pdl-js-select2',
            PDL_URL . 'vendors/select2-4.0.3/js/select2.full.min.js',
            array( 'jquery' ),
            '4.0.3'
        );
    }

    private function maybe_register_script( $handle, $src, $deps, $ver, $in_footer = false ) {
        $scripts = wp_scripts();

        if ( isset( $scripts->registered[ $handle ] ) ) {
            $registered_script = $scripts->registered[ $handle ];
        } else {
            $registered_script = null;
        }

        if ( $registered_script && version_compare( $registered_script->ver, $ver, '>=' ) ) {
            return;
        }

        if ( $registered_script ) {
            wp_deregister_script( $handle );
        }

        wp_register_script( $handle, $src, $deps, $ver, $in_footer );
    }

    public function enqueue_scripts() {
        $only_in_plugin_pages = true;
        $enqueue_scripts_and_styles = apply_filters( 'pdl_should_enqueue_scripts_and_styles', pdl()->is_plugin_page() );

        wp_enqueue_style(
            'pdl-widgets',
            PDL_URL . 'assets/css/widgets.min.css',
            array(),
            PDL_VERSION
        );

        if ( $only_in_plugin_pages && ! $enqueue_scripts_and_styles )
            return;

        wp_register_style( 'pdl-base-css', PDL_URL . 'assets/css/pdl.min.css', array( 'pdl-js-select2-css' ), PDL_VERSION );

        // TODO: Is it possible (and worth it) to figure out if we need the
        // jquery-ui-datepicker script based on which fields are available?
        wp_register_script(
            'pdl-js',
            PDL_URL . 'assets/js/pdl.min.js',
            array(
                'jquery',
                'breakpoints.js',
                'pdl-js-select2',
                'jquery-ui-sortable',
                'jquery-ui-datepicker',
            ),
            PDL_VERSION
        );
        wp_localize_script( 'pdl-js', 'pdl_global', array(
            'ajaxurl' => pdl_ajaxurl()
        ) );

        wp_enqueue_style( 'pdl-dnd-upload' );
        wp_enqueue_script( 'pdl-dnd-upload' );

        if ( pdl_get_option( 'use-thickbox' ) ) {
            add_thickbox();
        }

        wp_enqueue_style( 'pdl-base-css' );
        wp_enqueue_script( 'pdl-js' );

        do_action( 'pdl_enqueue_scripts' );

        // enable legacy css (should be removed in a future release) XXX
        if ( _pdl_template_mode( 'single' ) == 'template' || _pdl_template_mode( 'category' ) == 'template' ) {
            wp_enqueue_style(
                'pdl-legacy-css',
                PDL_URL . 'assets/css/pdl-legacy.min.css',
                array(),
                PDL_VERSION
            );
        }
    }

    /**
     * @since 3.5.3
     */
    public function enqueue_css_override() {
        $stylesheet_dir = trailingslashit( get_stylesheet_directory() );
        $stylesheet_dir_uri = trailingslashit( get_stylesheet_directory_uri() );
        $template_dir = trailingslashit( get_template_directory() );
        $template_dir_uri = trailingslashit( get_template_directory_uri() );

        $folders_uris = array(
            array( trailingslashit( WP_PLUGIN_DIR ), trailingslashit( WP_PLUGIN_URL ) ),
            array( $stylesheet_dir, $stylesheet_dir_uri ),
            array( $stylesheet_dir . 'css/', $stylesheet_dir_uri . 'css/' )
        );

        if ( $template_dir != $stylesheet_dir ) {
            $folders_uris[] = array( $template_dir, $template_dir_uri );
            $folders_uris[] = array( $template_dir . 'css/', $template_dir_uri . 'css/' );
        }

        $filenames = array( 'pdl.css',
                            'wpdirlist.css',
                            'pdl_custom_style.css',
                            'pdl_custom_styles.css',
                            'pdm_custom_style.css',
                            'pdm_custom_styles.css' );

        $n = 0;
        foreach ( $folders_uris as $folder_uri ) {
            list( $dir, $uri ) = $folder_uri;

            foreach ( $filenames as $f ) {
                if ( file_exists( $dir . $f ) ) {
                    wp_enqueue_style(
                        'pdl-custom-' . $n,
                        $uri . $f,
                        array(),
                        PDL_VERSION
                    );
                    $n++;
                }
            }
        }
    }

    public function register_image_sizes() {
        $thumbnail_width = absint( pdl_get_option( 'thumbnail-width' ) );
        $thumbnail_height = absint( pdl_get_option( 'thumbnail-height' ) );

        $max_width = absint( pdl_get_option('image-max-width') );
        $max_height = absint( pdl_get_option('image-max-height') );

        $crop = (bool) pdl_get_option( 'thumbnail-crop' );

        add_image_size( 'pdl-mini', 50, 50, true ); // Used for the submit process.
        add_image_size( 'pdl-thumb', $thumbnail_width, $crop ? $thumbnail_height : 9999, $crop ); // Thumbnail size.
        add_image_size( 'pdl-large', $max_width, $max_height, false ); // Large size.
    }
}
