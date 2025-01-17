<?php
/**
 * @since 5.0
 */

class PDL__Listing_Timeline {

    private $listing = null;


    public function __construct( $listing_id ) {
        $this->listing = pdl_get_listing( $listing_id );
    }

    public function get_items() {
        $items = pdl_get_logs( array( 'object_type' => 'listing', 'object_id' => $this->listing->get_id(), 'order' => 'DESC' ) );

        if ( ! $items ) {
            $this->recreate_logs();
            return $this->get_items();
        }

        return $items;
    }

    public function render() {
        $items = $this->get_items();
        $timeline = array();

        foreach ( $items as $item ) {
            $obj = clone $item;
            $obj->html = '';
            $obj->timestamp = strtotime( $obj->created_at );
            $obj->extra = '';
            $obj->actions = array();

            $callback = 'process_' . str_replace( '.', '_', $obj->log_type );
            if ( method_exists( $this, $callback ) )
                $obj = call_user_func( array( $this, $callback ), $obj );

            if ( ! $obj->html )
                $obj->html = $obj->message ? $obj->message : $obj->log_type;

            $timeline[] = $obj;
        }

        return pdl_render_page( PDL_PATH . 'templates/admin/metaboxes-listing-timeline.tpl.php', array( 'timeline' => $timeline ) );
    }

    private function recreate_logs() {
        $post = get_post( $this->listing->get_id() );
        $post_date = $post->post_date;

        pdl_insert_log( array( 'log_type' => 'listing.created', 'object_id' => $post->ID, 'created_at' => $post_date ) );

        // Insert logs for payments.
        $payments = PDL_Payment::objects()->filter( array( 'listing_id' => $post->ID ) );
        foreach ( $payments as $p ) {
            pdl_insert_log( array( 'log_type' => 'listing.payment', 'object_id' => $post->ID, 'rel_object_id' => $p->id ) );
        }
    }

    private function process_listing_created( $item ) {
        $item->html = _x( 'Listing created', 'listing timeline', 'PDM' );
        return $item;
    }

    private function process_listing_expired( $item ) {
        $item->html = _x( 'Listing expired', 'listing timeline', 'PDM' );
        return $item;
    }

    private function process_listing_renewal( $item ) {
        $item->html = _x( 'Listing renewed', 'listing timeline', 'PDM' );
        return $item;
    }

    private function process_listing_payment( $item ) {
        $payment = PDL_Payment::objects()->get( $item->rel_object_id );

        if ( ! $payment ) {
            return $item;
        }

        // switch ( $payment->payment_type ) {
        // case 'initial':
        //     $item->html .= 'Initial Payment';
        //     break;
        // default:
        //     $item->html .= 'Payment #' . $payment->id;
        //     break;
        // }

        $title = $payment->summary;

        if ( 'initial' == $payment->payment_type ) {
            if ( 'admin-submit' == $payment->context ) {
                $title = _x( 'Paid as admin', 'listing timeline', 'PDM' );
            } elseif ( 'csv-import' == $payment->context ) {
                $title = _x( 'Listing imported', 'listing timeline', 'PDM' );
            } else {
                $title = _x( 'Initial Payment', 'listing timeline', 'PDM' );
            }
        }

        $item->html  = '';
        $item->html .= '<a href="' . esc_url( admin_url( 'admin.php?page=pdl_admin_payments&pdl-view=details&payment-id=' . $payment->id ) ) . '">';
        $item->html .= $title;
        $item->html .= '</a>';

        if ( 'completed' != $payment->status )
            $item->html .= '<span class="payment-status tag ' . $payment->status . '">' . $payment->status . '</span>';

        $item->extra .= '<span class="payment-id">Payment #' . $payment->id . '</span>';
        $item->extra .= '<span class="payment-amount">Amount: ' . pdl_currency_format( $payment->amount, 'force_numeric=1' ) . '</span>';

        $item->actions = array(
            'details' => '<a href="' . esc_url( admin_url( 'admin.php?page=pdl_admin_payments&pdl-view=details&payment-id=' . $payment->id ) ) . '">Go to payment</a>'
        );

        return $item;
    }

}
