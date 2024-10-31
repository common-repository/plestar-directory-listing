<?php

class PDL_Admin_Debug_Page {

    function __construct() {
        add_action( 'admin_init', array($this, 'handle_download' ) );
        add_action( 'wp_ajax_pdl-debugging-ssltest', array( &$this, 'ajax_ssl_test' ) );
    }

    function dispatch( $plain = false ) {
        global $wpdb, $pdl;

        $debug_info = array();

        // basic PDL setup info & tests
        $debug_info['basic']['_title'] = _x( 'PDL Info', 'debug-info', 'PDM' );
        $debug_info['basic']['PDL version'] = PDL_VERSION;
        $debug_info['basic']['PDL database revision (current)'] = PDL_Installer::DB_VERSION;
        $debug_info['basic']['PDL database revision (installed)'] = get_option( 'pdl-db-version' );

        // Premium modules.
        $mod_versions = array();
        foreach ( $pdl->licensing->get_items() as $m ) {
            $mod_versions[] = str_replace( ' Module', '', $m['name'] ) . ' - ' . $m['version'];
        }
        if ( class_exists( 'PDL_CategoriesModule' ) ) {
            $mod_versions[] = 'Enhanced Categories - ' . PDL_CategoriesModule::VERSION;
        }

        $debug_info['basic']['Premium Modules'] = array(
            'value' => implode( "\n" . str_repeat( " ", 36 ), $mod_versions ),
            'html' => implode( '<br />', $mod_versions )
        );

        $tables = apply_filters( 'pdl_debug_info_tables_check', array( 'pdl_form_fields', 'pdl_plans', 'pdl_payments', 'pdl_listings' ) );
        $missing_tables = array();
        foreach ( $tables as &$t ) {
            if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $t) ) == '' )
                $missing_tables[] = $t;
        }
        $debug_info['basic']['Table check'] = $missing_tables
                                              ? sprintf( _( 'Missing tables: %s', 'debug-info', 'PDM' ), implode(',', $missing_tables) )
                                              : _x( 'OK', 'debug-info', 'PDM' );

        $debug_info['basic']['Main Page'] = sprintf( '%d (%s)', pdl_get_page_id( 'main' ), get_post_status( pdl_get_page_id( 'main' ) ) );
        $debug_info['basic'] = apply_filters( 'pdl_debug_info_section', $debug_info['basic'], 'basic' );


        // PDL options
        $blacklisted = array( 'authorize-net-transaction-key', 'authorize-net-login-id', 'googlecheckout-merchant', 'paypal-business-email', 'pdl-2checkout-seller', 'recaptcha-public-key', 'recaptcha-private-key' );
        $debug_info['options']['_title'] = _x( 'PDL Options', 'debug-info', 'PDM' );

        $settings_api = pdl_settings_api();
        $all_settings = $settings_api->get_registered_settings();
        foreach ( $all_settings as $s  ) {
            if ( in_array( $s['id'], $blacklisted ) )
                continue;

            $value = pdl_get_option( $s['id'] );

            if ( is_array( $value ) ) {
                if ( empty( $value ) ) {
                    $value = '';
                } else {
                    $value = print_r( $value, 1 );
                }
            }

            $debug_info['options'][ $s['id'] ] = $value;
        }
        $debug_info['options'] = apply_filters( 'pdl_debug_info_section', $debug_info['options'], 'options' );

        // environment info
        $debug_info['environment']['_title'] = _x( 'Environment', 'debug-info', 'PDM' );
        $debug_info['environment']['WordPress version'] = get_bloginfo( 'version', 'raw' );
        $debug_info['environment']['OS'] = php_uname( 's' ) . ' ' . php_uname( 'r' ) . ' ' . php_uname( 'm' );

        if ( function_exists( 'apache_get_version' ) ) {
            $apache_version = apache_get_version();
            $debug_info['environment']['Apache version'] = $apache_version;
        }

        $debug_info['environment']['PHP version'] = phpversion();

        $mysql_version = $wpdb->get_var( 'SELECT @@version' );
        if ( $sql_mode = $wpdb->get_var( 'SELECT @@sql_mode' ) )
            $mysql_version .= ' ( ' . $sql_mode . ' )';
        $debug_info['environment']['MySQL version'] = $mysql_version ? $mysql_version : 'N/A';

        if ( function_exists( 'curl_init' ) ) {
            $data = curl_version();

            $debug_info['environment']['cURL version'] = $data['version'];
            $debug_info['environment']['cURL SSL library'] = $data['ssl_version'];
            $debug_info['environment']['Test SSL setup'] = array( 'exclude' => true,
                                                                  'html' => '<a href="#" class="test-ssl-link">' . _x( 'Test SSL setup...', 'debug info', 'PDM' ) . '</a>' );
        } else { 
            $debug_info['environment']['cURL version'] = 'N/A';
            $debug_info['environment']['cURL SSL library'] = 'N/A';
        }

        $debug_info['environment'] = apply_filters( 'pdl_debug_info_section', $debug_info['environment'], 'environment' );

        $debug_info = apply_filters( 'pdl_debug_info', $debug_info );

        if ( $plain ) {
            foreach ( $debug_info as &$section ) {
                foreach ( $section as $k => $v ) {
                    if ( $k == '_title' ) {
                        printf( '== %s ==', $v );
                        print PHP_EOL;
                        continue;
                    }

                    if ( is_array( $v ) ) {
                        if ( isset( $v['exclude'] ) && $v['exclude'] )
                            continue;

                        if ( ! empty( $v['html'] ) && empty( $v['value'] ) )
                            continue;
                    }

                    printf( "%-33s = %s", $k, is_array( $v  ) ? $v['value'] : $v );
                    print PHP_EOL;
                }

                print str_repeat( PHP_EOL, 2 );
            }
            return;
        }

        echo pdl_render_page( PDL_PATH . 'templates/admin/debug-info.tpl.php', array( 'debug_info' => $debug_info ) );
    }

    function handle_download() {
        global $pagenow;

        if ( ! current_user_can( 'administrator' ) || 'admin.php' != $pagenow
             || ! isset( $_GET['page'] ) || 'pdl-debug-info' != $_GET['page'] )
            return;

        if ( isset( $_GET['download'] ) && 1 == $_GET['download'] ) {
                    header( 'Content-Description: File Transfer' );
                    header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ), true );
                    header( 'Content-Disposition: attachment; filename=' . 'pdl-debug-info.txt' );
                    header( 'Pragma: no-cache' );
                    $this->dispatch( true );
                    exit;
        }
    }

    function ajax_ssl_test() {        
        $response = wp_remote_get("https://www.howsmyssl.com/a/check");
        $res = wp_remote_retrieve_body($response);
        
        if (is_wp_error($response))
            die( 'No response from remote server.' );

        $json = json_decode( $res );

        echo "Cipher Suites:\n" . implode( ',', $json->given_cipher_suites ) . "\n\n";
        echo "TLS Version:\n" . $json->tls_version . "\n\n";
        echo "Rating:\n" . $json->rating;

        exit();
    }
}
