<?php

require_once( PDL_INC . 'admin/class-csv-exporter.php' );

/**
 * CSV Export admin pages.
 * @since 3.2
 */
class PDL_Admin_CSVExport {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_pdl-csv-export', array( &$this, 'ajax_csv_export' ) );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'pdl-admin-export-js',
            PDL_URL . 'assets/js/admin-export.min.js',
            array( 'pdl-admin-js' ),
            PDL_VERSION
        );

        wp_enqueue_style(
            'pdl-admin-export-css',
            PDL_URL . 'assets/css/admin-export.min.css',
            array(),
            PDL_VERSION
        );
    }

    public function dispatch() {
        echo pdl_render_page( PDL_PATH . 'templates/admin/csv-export.tpl.php' );
    }

    public function ajax_csv_export() {
        if ( ! current_user_can( 'administrator' ) ) {
            exit();
        }

        $error = '';

        try {
            if ( ! isset( $_REQUEST['state'] ) ) {
                $export = new PDL_CSVExporter( array_merge( $_REQUEST['settings'], array() ) );
            } else {
                $state  = json_decode( base64_decode( $_REQUEST['state'] ), true );
                if ( ! $state || ! is_array( $state ) || empty( $state['workingdir'] ) ) {
                    $error = _x( 'Could not decode export state information.', 'admin csv-export', 'PDM' );
                }

                $export = PDL_CSVExporter::from_state( $state );

                if ( isset( $_REQUEST['cleanup'] ) && $_REQUEST['cleanup'] == 1 ) {
                    $export->cleanup();
                } else {
                    $export->advance();
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $state = ! $error ? $export->get_state() : null;

        $response = array();
        $response['error'] = $error;
        $response['state'] = $state ? base64_encode( json_encode( $state ) ) : null;
        $response['count'] = $state ? count( $state['listings'] ) : 0;
        $response['exported'] = $state ? $state['exported'] : 0;
        $response['filesize'] = $state ? size_format( $state['filesize'] ) : 0;
        $response['isDone'] = $state ? $state['done'] : false;
        $response['fileurl'] = $state ? ( $state['done'] ? $export->get_file_url() : '' ) : '';
        $response['filename'] = $state ? ( $state['done'] ? basename( $export->get_file_url() ) : '' ) : '';

        echo json_encode( $response );

        die();
    }

}

