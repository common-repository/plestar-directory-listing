<?php

class PDL__PDLX_Payments_Compat {

    private $gateways;


    public function __construct() {
        $this->gateways = pdl()->payment_gateways;
    }

    public function dispatch() {
        $action = isset( $_GET['action'] ) ? trim( $_GET['action'] ) : '';
        $payment = isset( $_GET['payment_id'] ) ? PDL_Payment::get( intval( $_GET['payment_id'] ) ) : null;
        $gid = isset( $_GET['gid'] ) ? trim( $_GET['gid'] ) : '';

        if ( ! in_array( $action, array( 'postback', 'process', 'notify', 'return', 'cancel', 'ins' ) ) || ( ! $payment && ! $gid ) )
            return;

        unset( $_GET['action'] );

        if ( $gid )
            unset( $_GET['gid'] );

        $gateway_id = $payment ? $payment->gateway : $gid;
        $gateway = $this->gateways->get( $gateway_id );

        if ( ! $gateway )
            return;

        switch ( $gateway ) {
        case '2checkout':
            $_POST['pdl_payment_id']    = sanitize_text_field($_REQUEST['merchant_order_id']);
            $_GET['pdl_payment_id']     = sanitize_text_field($_REQUEST['merchant_order_id']);
            $_REQUEST['pdl_payment_id'] = sanitize_text_field($_REQUEST['merchant_order_id']);
            break;
        case 'paypal':
            break;
        case 'stripe':
            break;
        }

        $gateway->process_postback();
        exit;
    }

}
    //     // if ( ! $payment )
    //     //     $this->gateways[ $gateway_id ]->process_generic( $action );
    //     // else
    //     //     $this->gateways[ $gateway_id ]->process( $payment, $action );
    // }
