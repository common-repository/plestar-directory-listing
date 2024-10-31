<?php
/**
 * @since 5.0
 */
class PDL__Admin__Controller {

    protected $controller_id = '';
    protected $pdl;
    protected $current_view = '';


    function __construct() {
        $this->pdl = $GLOBALS['pdl'];
        $this->controller_id = str_replace( 'pdl__admin__', '', PDL_Utils::normalize( get_class( $this ) ) );
    }

    function _enqueue_scripts() {
        if ( file_exists( PDL_PATH . 'assets/js/admin-' . $this->controller_id . '.js' ) ) {
            wp_enqueue_script(
                'pdl-' . $this->controller_id . '-js',
                PDL_URL . 'assets/js/admin-' . $this->controller_id . '.js',
                array( 'pdl-admin-js' ),
                PDL_VERSION
            );
        }
    }

    function _ajax_dispatch() {
        $handler = ! empty( $_REQUEST['handler'] ) ? trim( $_REQUEST['handler'] ) : '';
        $parts = explode( '__', $handler );
        $controller_id = $parts[0];
        $function = isset( $parts[1] ) ? $parts[1] : '';

        if ( method_exists( $this, 'ajax_' . $function ) )
            return call_user_func( array( $this, 'ajax_' . $function ) );
    }

    function _dispatch() {
        if ( empty( $this->current_view ) )
            $this->current_view = isset( $_GET['pdl-view'] ) ? $_GET['pdl-view'] : 'index';

        $this->current_view = PDL_Utils::normalize( $this->current_view );

        $result = false;
        $output = '';

        $callback = ( false !== strpos( $this->current_view, '-' ) ? str_replace( '-', '_', $this->current_view ) : $this->current_view );

        // Remove query args.
        $orig_uri = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = remove_query_arg( array( 'pdl-view', 'id' ), $_SERVER['REQUEST_URI'] );

        if ( method_exists( $this, $callback ) )
            $result = call_user_func( array( $this, $callback ) );

        if ( is_array( $result ) ) {
            $template = PDL_PATH . 'templates/admin/' . $this->controller_id . '-' . $this->current_view . '.tpl.php';

            if ( ! file_exists( $template ) )
                $output = json_encode( $result );
            else
                $output = pdl_render_page( $template, $result );
        } else {
            $output = $result;
        }

        $_SERVER['REQUEST_URI'] = $orig_uri;

        echo $output;
    }

    function _redirect( $view_or_url ) {
        $this->current_view = $view_or_url;
        return $this->_dispatch();
    }

    function _confirm_action( $args = array() ) {
        $defaults = array(
            'title' => _x( 'Are you sure you want to do this?', 'admin confirm', 'PDM' ),
            'cancel_url' => '',
            'cancel_text' => _x( 'No, go back', 'admin confirm', 'PDM' ),
            'submit_text' => _x( 'Yes, I\'m sure', 'admin confirm', 'PDM' ),
            'explanation' => ''
        );
        $args = wp_parse_args( $args, $defaults );
        $nonce = ! empty( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';

        if ( $nonce && wp_verify_nonce( $nonce, 'confirm ' . md5( $args['title'] ) ) )
            return array( true, '' );

        return array( false, pdl_render_page( PDL_PATH . 'templates/admin/confirm-page.tpl.php', $args ) );
    }

}
