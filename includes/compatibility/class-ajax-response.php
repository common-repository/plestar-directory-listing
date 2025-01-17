<?php

/**
 * @deprecated since fees-revamp. Use `wp_send_json()` family of functions.
 */
class PDL_AJAX_Response {
    public $success = true;
    public $error = '';
    public $message = '';
    public $data = array();

    public function add( $k, $v ) {
        $this->data[ $k ] = $v;
    }

    public function set_message( $s ) {
        $this->message = $s;
    }

    public function send_error( $error = null ) {
        if ( $error )
            $this->error = $error;

        $this->success = false;
        $this->message = '';
        $this->data = null;

        $this->send();
    }

    public function send() {
        $response = array();
        $response['success'] = $this->success;

        if ( ! $this->success ) {
            $response['error'] = $this->error ? $this->error : 'Unknown error';
        } else {
            $response['data'] = $this->data;
            $response['message'] = $this->message;
        }

        print json_encode( $response );
        die();
    }
}
