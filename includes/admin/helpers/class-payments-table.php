<?php
if ( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * @since 5.0
 */
class PDL__Admin__Payments_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => _x( 'payment', 'payments admin', 'PDM' ),
            'plural' => _x( 'payments', 'payments admin', 'PDM' ),
            'ajax' => false
        ) );
    }

    public function no_items() {
        echo _x( 'No payments found.', 'payments admin', 'PDM' );
    }

    public function get_current_view() {
        return pdl_getv( $_GET, 'status', 'all' );
    }

    public function get_views() {
        global $wpdb;

        $views_ = array();

        $count = PDL_Payment::objects()->count();
        $views_['all'] = array( _x( 'All', 'payments admin', 'PDM' ), $count );

        foreach ( PDL_Payment::get_stati() as $status => $status_label ) {
            $count = PDL_Payment::objects()->filter( array( 'status' => $status ) )->count();
            $views_[ $status ] = array( $status_label, $count );
        }

        $views = array();
        foreach ( $views_ as $view_id => $view_data ) {
            $views[ $view_id ] = sprintf( '<a href="%s" class="%s">%s</a> <span class="count">(%s)</span></a>',
                                          esc_url( add_query_arg( 'status', $view_id ) ),
                                          $view_id == $this->get_current_view() ? 'current': '',
                                          $view_data[0],
                                          number_format_i18n( $view_data[1] ) );
        }

        return $views;
    }

    public function get_columns() {
        $cols = array(
            'listing' => _x( 'Listing', 'fees admin', 'PDM' ),
            'payment_id' => _x( 'ID', 'fees admin', 'PDM' ),
            'date' => _x( 'Date', 'fees admin', 'PDM' ),
            'details' => _x( 'Payment History', 'fees admin', 'PDM' ),
            'amount' => _x( 'Amount', 'fees admin', 'PDM' ),
            'status' => _x( 'Status', 'fees admin', 'PDM' )
        );

        return $cols;
    }

    public function prepare_items() {
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());

        $args = array();

        if ( 'all' != $this->get_current_view() )
            $args['status'] = $this->get_current_view();

        if ( ! empty( $_GET['listing'] ) )
            $args['listing_id'] = absint( $_GET['listing'] );

        $this->items = PDL_Payment::objects()->filter( $args )->order_by( '-id' );

        if ( ! empty( $_GET['s'] ) ) {
            $s = trim( $_GET['s'] );

            $this->items = $this->items->filter(
                array(
                    'payer_first_name__icontains' => $s,
                    'payer_last_name__icontains'  => $s,
                    'payer_email__icontains'      => $s,
                    'gateway_tx_id'               => $s
                ),
                false,
                'OR'
            );
                    
            // pdl_debug_e( $s, $this->items );
        }

        $this->items = $this->items;
    }

    public function has_items() {
        return $this->items->count() > 0;
    }

    public function column_payment_id( $payment ) {
        return sprintf( '<a href="%s">%d</a>', add_query_arg( array( 'pdl-view' => 'details', 'payment-id' => $payment->id ) ), $payment->id );
    }

    public function column_date( $payment ) {
        return date_i18n( get_option( 'date_format' ), strtotime( $payment->created_at ));
    }

    public function column_amount( $payment ) {
        return pdl_currency_format( $payment->amount );
    }

    public function column_status( $payment ) {
        return PDL_Payment::get_status_label( $payment->status );
    }

    public function column_details( $payment ) {
        return '<a href="' . esc_url( add_query_arg( array( 'pdl-view' => 'details', 'payment-id' => $payment->id ) ) ) . '">' . _x( 'View Payment History', 'payments admin', 'PDM' ) . '</a>';
    }

    public function column_listing( $payment ) {
        $listing = $payment->listing;

        if ( ! $listing )
            return '';

        return '<a href="' . esc_url( $listing->get_admin_edit_link() ) . '">' . esc_html( $listing->get_title() ) . '</a>';
    }

//     public function column_label($fee) {
//         $actions = array();
//         $actions['edit'] = sprintf('<a href="%s">%s</a>',
//                                    esc_url(add_query_arg(array('pdl-view' => 'edit-fee', 'id' => $fee->id))),
//                                    _x('Edit', 'fees admin', 'PDM'));
//
//         if ( 'free' == $fee->tag ) {
// //            $actions['delete'] = sprintf('<a href="%s">%s</a>',
// //                                       esc_url(add_query_arg(array('action' => 'deletefee', 'id' => $fee->id))),
// //                                       _x('Disable', 'fees admin', 'PDM'));
//         } else {
//             if ( $fee->enabled )
//                 $actions['disable'] = sprintf('<a href="%s">%s</a>',
//                                            esc_url(add_query_arg(array('pdl-view' => 'toggle-fee', 'id' => $fee->id))),
//                                            _x('Disable', 'fees admin', 'PDM'));
//             else
//                 $actions['enable'] = sprintf('<a href="%s">%s</a>',
//                                            esc_url(add_query_arg(array('pdl-view' => 'toggle-fee', 'id' => $fee->id))),
//                                            _x('Enable', 'fees admin', 'PDM'));
//
//             $actions['delete'] = sprintf('<a href="%s">%s</a>',
//                                        esc_url(add_query_arg(array('pdl-view' => 'delete-fee', 'id' => $fee->id))),
//                                        _x('Delete', 'fees admin', 'PDM'));
//         }
//
//         $html = '';
//         $html .= sprintf( '<span class="pdl-drag-handle" data-fee-id="%s"></span></a>',
//                         $fee->id );
//
//         $html .= sprintf('<strong><a href="%s">%s</a></strong>',
//                          esc_url(add_query_arg(array('pdl-view' => 'edit-fee', 'id' => $fee->id))),
//                          esc_attr($fee->label));
//         $html .= $this->row_actions($actions);
//
//         return $html;
//     }

}
