<?php

if (!class_exists('WP_List_Table'))
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class PDL__Admin__Fees_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct(array(
            'singular' => _x('fee', 'fees admin', 'PDM'),
            'plural' => _x('fees', 'fees admin', 'PDM'),
            'ajax' => false
        ));
    }

    public function no_items() {
        if ( 'all' == $this->get_current_view() ) {
            echo str_replace( '<a>',
                              '<a href="' . admin_url( 'admin.php?page=pdl-admin-fees&pdl-view=add-fee' ) . '">',
                              _x( 'There are no fees right now. You can <a>create one</a>, if you want.', 'fees admin', 'PDM' ) );
            return;
        }

        switch ( $this->get_current_view() ) {
            case 'active':
                $view_name = _x( 'Active', 'fees admin', 'PDM' );
                break;
            case 'unavailable':
                $view_name = _x( 'Not Available', 'fees admin', 'PDM' );
                break;
            case 'disabled':
                $view_name = _x( 'Disabled', 'fees admin', 'PDM' );
                break;
            default:
                $view_name = '';
                break;
        }
        printf( str_replace( '<a>',
                             '<a href="' . admin_url( 'admin.php?page=pdl-admin-fees&pdl-view=add-fee' ) . '">',
                             _x( 'There are no "%s" fees right now. You can <a>create one</a>, if you want.', 'fees admin', 'PDM' ) ),
                $view_name );
    }

    public function get_current_view() {
        return pdl_getv( $_GET, 'fee_status', 'active' );
    }

    public function get_views() {
        global $wpdb;

        $views = array();

        $all = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}pdl_plans" ) );
        $non_free = absint( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}pdl_plans WHERE enabled = %d AND tag != %s", 1, 'free' ) ) );
        $disabled = absint( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}pdl_plans WHERE enabled = %d", 0 ) ) );

        $views['all'] = sprintf( '<a href="%s" class="%s">%s</a> <span class="count">(%s)</span></a>',
                                 esc_url( add_query_arg( 'fee_status', 'all' ) ),
                                 'all' == $this->get_current_view() ? 'current' : '',
                                 _x( 'All', 'admin fees table', 'PDM' ),
                                 number_format_i18n( $all ) );


        if ( ! pdl_payments_possible() ) {
            $active = $all - $non_free - $disabled;
        } else {
            $active = $non_free;
        }

        $views['active'] = sprintf( '<a href="%s" class="%s">%s</a> <span class="count">(%s)</span></a>',
                                    esc_url( add_query_arg( 'fee_status', 'active' ) ),
                                    'active' == $this->get_current_view() ? 'current' : '',
                                    _x( 'Active', 'admin fees table', 'PDM' ),
                                    number_format_i18n( $active ) );


        $unavailable = $all - $active - $disabled;

        $views['unavailable'] = sprintf( '<a href="%s" class="%s">%s</a> <span class="count">(%s)</span></a>',
                                      esc_url( add_query_arg( 'fee_status', 'unavailable' ) ),
                                      'unavailable' == $this->get_current_view() ? 'current' : '',
                                      _x( 'Not Available', 'admin fees table', 'PDM' ),
                                      number_format_i18n( $unavailable ) );


        $views['disabled'] = sprintf( '<a href="%s" class="%s">%s</a> <span class="count">(%s)</span></a>',
                                      esc_url( add_query_arg( 'fee_status', 'disabled' ) ),
                                      'disabled' == $this->get_current_view() ? 'current' : '',
                                      _x( 'Disabled', 'admin fees table', 'PDM' ),
                                      number_format_i18n( $disabled ) );


        return $views;
    }

    public function get_columns() {
        $cols = array(
            'label' => _x('Label', 'fees admin', 'PDM'),
            'amount' => _x('Amount', 'fees admin', 'PDM'),
            'duration' => _x('Duration', 'fees admin', 'PDM'),
            'images' => _x('Images', 'fees admin', 'PDM'),
            'attributes' => _x( 'Attributes', 'fees admin', 'PDM' )
        );

        return $cols;
    }

    public function prepare_items() {
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());

        $args = array();

        switch ( $this->get_current_view() ) {
        case 'active':
            $args['enabled'] = 1;
            $args['include_free'] = ! pdl_payments_possible();
            break;
        case 'disabled':
            $args['enabled'] = 0;
            $args['tag'] = ''; // FIXME: Without tag = '', you only get disabled free fees

            break;
        case 'unavailable':
            if ( pdl_payments_possible() ) {
                $args['enabled'] = 'all';
                $args['tag'] = 'free';
            } else {
                $args['enabled'] = 1;
                $args['include_free'] = false;
                $args['tag'] = ''; // FIXME: Without tag = '', include_free is ignored
            }

            break;
        case 'all':
        default:
            $args['enabled'] = 'all';
            $args['include_free'] = true;
            $args['tag'] = ''; // FIXME: Without tag = '', you get only free fees
            break;
        }

        $this->items = pdl_get_fee_plans( $args );
    }

    /* Rows */
    public function single_row( $item ) {
        $free_mode = ( ! pdl_payments_possible() );
        $classes = '';

        if ( $free_mode && $item->amount > 0.0 )
            $classes .= 'disabled-fee';
        elseif ( 'free' == $item->tag )
            $classes .= 'free-fee';

        echo '<tr class="' . $classes . '">';
        $this->single_row_columns( $item );
        echo '</tr>';

        if ( 'free' == $item->tag ) {
            echo '<tr class="free-fee-related-tr"></tr>';
            echo '<tr class="pdl-item-message-tr free-fee-related-tr">';
            echo '<td colspan="' . count( $this->get_columns() ) . '">';
            echo '<div>';
            _ex( 'This is the default free plan for your directory.  You can\'t delete it and it\'s always free, but you can edit the name and other settings. It\'s only available when the directory is in Free mode.  You can always create other fee plans, including ones for 0.00 (free) if you wish.',
                 'fees admin',
                 'PDM' );
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }

//        if ( $free_mode && $item->amount > 0.0 ) {
//            echo '<tr></tr>';
//            echo '<tr class="pdl-item-message-tr">';
//            echo '<td colspan="' . count( $this->get_columns() ) . '">';
//            echo '<div>';
//            _ex( 'Fee plan disabled because directory is in free mode.', 'fees admin', 'PDM' );
//            echo '</div>';
//            echo '</td>';
//            echo '</tr>';
//        }
    }

    public function column_order( $fee ) {
        return sprintf( '<span class="pdl-drag-handle" data-fee-id="%s"></span> <a href="%s"><strong>↑</strong></a> | <a href="%s"><strong>↓</strong></a>',
                        $fee->id, 
                        esc_url( add_query_arg( array('action' => 'feeup', 'id' => $fee->id ) ) ),
                        esc_url( add_query_arg( array('action' => 'feedown', 'id' => $fee->id ) ) )
                       );
    }

    public function column_label($fee) {
        $actions = array();
        $actions['edit'] = sprintf('<a href="%s">%s</a>',
                                   esc_url(add_query_arg(array('pdl-view' => 'edit-fee', 'id' => $fee->id))),
                                   _x('Edit', 'fees admin', 'PDM'));

        if ( 'free' == $fee->tag ) {
//            $actions['delete'] = sprintf('<a href="%s">%s</a>',
//                                       esc_url(add_query_arg(array('action' => 'deletefee', 'id' => $fee->id))),
//                                       _x('Disable', 'fees admin', 'PDM'));
        } else {
            if ( $fee->enabled )
                $actions['disable'] = sprintf('<a href="%s">%s</a>',
                                           esc_url(add_query_arg(array('pdl-view' => 'toggle-fee', 'id' => $fee->id))),
                                           _x('Disable', 'fees admin', 'PDM'));
            else
                $actions['enable'] = sprintf('<a href="%s">%s</a>',
                                           esc_url(add_query_arg(array('pdl-view' => 'toggle-fee', 'id' => $fee->id))),
                                           _x('Enable', 'fees admin', 'PDM'));

            $actions['delete'] = sprintf('<a href="%s">%s</a>',
                                       esc_url(add_query_arg(array('pdl-view' => 'delete-fee', 'id' => $fee->id))),
                                       _x('Delete', 'fees admin', 'PDM'));
        }

        $html = '';
        $html .= sprintf( '<span class="pdl-drag-handle" data-fee-id="%s"></span></a>',
                        $fee->id );

        $fee_id_string = _x( '<strong>Fee ID:</strong> <fee-id>', 'fees admin', 'PDM' );
        $fee_id_string = str_replace( '<fee-id>', $fee->id, $fee_id_string );

        $html .= sprintf(
            '<strong><a href="%s">%s</a></strong><br/>%s',
             esc_url( add_query_arg( array( 'pdl-view' => 'edit-fee', 'id' => $fee->id ) ) ),
             esc_attr( $fee->label ),
             $fee_id_string
        );
        $html .= $this->row_actions($actions);

        return $html;
    }

    public function column_amount($fee) {
        if ( 'variable' == $fee->pricing_model ) {
            return _x( 'Variable', 'fees admin', 'PDM' );
        } else if ( 'extra' == $fee->pricing_model ) {
            $amount = pdl_currency_format( $fee->amount );
            $extra = pdl_currency_format( $fee->pricing_details['extra'] );

            return sprintf( _x( '%s + %s per category', 'fees admin', 'PDM' ), $amount, $extra );
        }

        return pdl_currency_format( $fee->amount );
    }

    public function column_duration($fee) {
        if ($fee->days == 0)
            return _x('Forever', 'fees admin', 'PDM');
        return sprintf(_nx('%d day', '%d days', $fee->days, 'fees admin', 'PDM'), $fee->days);
    }

    public function column_images($fee) {
        return sprintf(_nx('%d image', '%d images', $fee->images, 'fees admin', 'PDM'), $fee->images);
    }

    public function column_categories($fee) {
        if ($fee->categories['all'])
            return _x('All categories', 'fees admin', 'PDM');

        $names = array();

        foreach ($fee->categories['categories'] as $category_id) {
            if ($category = get_term($category_id, pdl()->get_post_type_category())) {
                $names[] = $category->name;
            }
        }

        return $names ? join($names, ', ') : '--';
    }

    public function column_attributes( $fee ) {
        $html = '';

        if ( 'all' == $this->get_current_view() ) {
            $html .= '<span class="pdl-tag">';

            if ( ! $fee->enabled ) {
                $html .= _x( 'Disabled', 'fees admin', 'PDM' );
            } elseif ( ( ! pdl_payments_possible() && 'free' != $fee->tag ) || ( pdl_payments_possible() && 'free' == $fee->tag ) ) {
                $html .= _x( 'Unavailable', 'fees admin', 'PDM' );
            } else {
                $html .= _x( 'Active', 'fees admin', 'PDM' );
            }

            $html .= '</span>';
        }

        if ( $fee->sticky )
            $html .= '<span class="pdl-tag">' . _x( 'Sticky', 'fees admin', 'PDM' ) . '</span>';

        if ( $fee->recurring )
            $html .= '<span class="pdl-tag">' . _x( 'Recurring', 'fees admin', 'PDM' ) . '</span>';

        return $html;
    }

}
