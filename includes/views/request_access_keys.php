<?php
require_once( PDL_PATH . 'includes/class-view.php' );


class PDL__Views__Request_Access_Keys extends PDL__View {

    public function dispatch() {
        if ( ! pdl_get_option( 'enable-key-access' ) ) {
            return pdl_render_msg(
                str_replace(
                    '<a>',
                    '<a href="' . esc_url( pdl_get_page_link( 'main' ) ) . '">',
                    _x( 'Did you mean to <a>access the Directory</a>?', 'request_access_keys', 'PDM' )
                ),
                'error'
            );
        }

        $nonce = ! empty( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
        $errors = array();

        if ( $nonce && wp_verify_nonce( $nonce, 'request_access_keys' ) )
            return $this->listings_and_access_keys();

        return $this->_render( 'send-access-keys', array( 'redirect_to' => ! empty( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : '' ) );
    }

    public function listings_and_access_keys() {
        $email = ! empty( $_POST['email'] ) ? sanitize_email(trim( $_POST['email'] )) : '';

        try {
            $message_sent = $this->get_access_keys_sender()->send_access_keys( $email );
        } catch ( Exception $e ) {
            return pdl_render_msg( $e->getMessage(), 'error' );
        }

        if ( $message_sent ) {
            $html  = '';
            $html .= pdl_render_msg( _x( 'Access keys have been sent to your e-mail address.', 'request_access_keys', 'PDM' ) );

            if ( ! empty( $_POST['redirect_to'] ) ) {
                $html .= '<p>';
                $html .= '<a href="' . esc_url( $_POST['redirect_to'] ) .'">';
                $html .= _x( 'â†� Return to previous page', 'request_access_keys', 'PDM' );
                $html .= '</a>';
                $html .= '<p>';
            }

            return $html;
        }
    }

    public function get_access_keys_sender() {
        return new PDL__Access_Keys_Sender();
    }
}
