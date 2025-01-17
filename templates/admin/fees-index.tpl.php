<?php
    echo pdl_admin_header(null, 'admin-fees', array(
        array(_x('Add New Listing Fee', 'fees admin', 'PDM'), esc_url(add_query_arg('pdl-view', 'add-fee'))),
    ) );
?>
    <?php pdl_admin_notices(); ?>

    <?php if (!pdl_get_option('payments-on')): ?>
    <div class="pdl-note"><p>
    <?php _ex('Payments are currently turned off.', 'fees admin', 'PDM' ); ?><br />
    <?php echo str_replace( '<a>',
                            '<a href="' . admin_url( 'admin.php?page=pdl_settings&tab=payment' ) . '">',
                            _x( 'To manage fees you need to go to the <a>Manage Options - Payment</a> page and check the box next to \'Turn On Payments\' under \'Payment Settings\'.',
                                'fees admin',
                                'PDM' ) ); ?></p>
    </div>
    <?php endif; ?>

    <?php if ( 'active' == $table->get_current_view() || 'all' == $table->get_current_view() ): ?>
        <div class="fees-order">
            <form>
            <input type="hidden" name="action" value="pdl-admin-fees-set-order" />
            <?php wp_nonce_field( 'change fees order' ); ?>
            <b><?php _ex( 'Order fees on the frontend by:', 'fees admin', 'PDM' ); ?></b><br />
            <select name="fee_order[method]">
            <?php foreach ( $order_options as $k => $l ): ?>
            <option value="<?php echo $k; ?>" <?php echo ( $k == $current_order['method'] ) ? 'selected="selected"' : ''; ?> ><?php echo $l; ?></option>
            <?php endforeach; ?>
            </select>

            <select name="fee_order[order]" style="<?php echo ( 'custom' == $current_order['method'] ) ? 'display: none;' : ''; ?>">
            <?php foreach ( array( 'asc' => _x( '↑ Ascending', 'fees admin', 'PDM' ), 'desc' => _x( '↓ Descending', 'fees admin', 'PDM' ) ) as $o => $l ): ?>
                <option value="<?php echo $o; ?>" <?php echo ( $o == $current_order['order'] ) ? 'selected="selected"' : ''; ?> ><?php echo $l; ?></option>
            <?php endforeach; ?>
            </select>

            <?php if ( 'custom' == $current_order['method'] ): ?>
            <span><?php _ex( 'Drag and drop to re-order fees.', 'fees admin', 'PDM' ); ?></span>
            <?php endif; ?>

            </form>
        </div>

        <br class="clear" />
        <?php endif; ?>


        <div class="pdl-note"><p>
        <?php switch ( $table->get_current_view() ):
                case 'active':
        ?>
                <?php printf( str_replace( '<a>',
                                           '<a href="' . add_query_arg( 'fee_status', 'unavailable' ) . '">',
                                           _x( 'These are all of the fee plans displayed to the user when they place a listing. Your current mode of "%s" restricts what you see here. Those on the <a>Not Available</a> filter will become active when you change the payment mode.',
                                               'fees admin',
                                               'PDM' ) ),
                              pdl_payments_possible() ? _x( 'Paid', 'fees admin', 'PDM' ) : _x( 'Free', 'fees admin', 'PDM' ) ); ?>
            <?php break; ?>
            <?php case 'unavailable': ?>
                <?php printf( _x( 'These are all of the fee plans that aren\'t available because you\'re in "%s" mode. Those on the Active filter will become Not Available when you change the payment mode.',
                                  'fees admin',
                                  'PDM' ),
                              pdl_payments_possible() ? _x( 'Paid', 'fees admin', 'PDM' ) : _x( 'Free', 'fees admin', 'PDM' ) ); ?>
            <?php break; ?>
            <?php case 'disabled': ?>
                <?php _ex( 'These fee plans were disabled by the admin and will not show to the end user regardless of mode until you enable them.',
                           'fees admin',
                           'PDM' ); ?>
            <?php break; ?>
            <?php case 'all': ?>
            <?php default: ?>
                <?php printf( str_replace( '<a>',
                                           '<a href="' . add_query_arg( 'fee_status', 'active' ) . '">',
                                           _x( 'These are all of the fee plans you have configured. Not all of them are available for the current mode (currently set to "%s"). To see the fee plans for this mode click <a>Active</a>.',
                                               'fees admin',
                                               'PDM' ) ),
                              pdl_payments_possible() ? _x( 'Paid', 'fees admin', 'PDM' ) : _x( 'Free', 'fees admin', 'PDM' ) ); ?>
            <?php break; ?>
        <?php endswitch; ?>
        </p></div>


        <?php $table->views(); ?>
        <?php $table->display(); ?>

        <hr />
        <?php
        $modules = array(
            array( 'paypal', 'paypal-gateway-module', _x( 'PayPal Gateway Module', 'admin sidebar', 'PDM' ), 'PayPal' ),
            array( '2checkout', '2checkout-gateway-module', _x( '2Checkout Gateway Module', 'admin sidebar', 'PDM' ), '2Checkout' ),
            array( 'payfast', 'payfast-payment-module', _x( 'PayFast Payment Module', 'admin sidebar', 'PDM' ), 'PayFast' ),
            array( 'stripe', 'stripe-payment-module', _x( 'Stripe Payment Module', 'admin sidebar', 'PDM' ), 'Stripe' )
        );

        global $pdl;
        ?>
        <?php if ( ! pdl_payments_possible() ): ?>
        <p>
        <?php
        echo str_replace( '<a>',
                          '<a href="' . admin_url( 'admin.php' ) . '?page=pdl_settings&tab=payment">',
                          sprintf ( _x( 'It does not appear you have any of the payment gateway modules enabled. Either <a>enable the default Authorize.net gateway</a> with your account info, or purchase a different payment gateway module in order to charge a fee for listings. To purchase additional payment gateways use the buttons below or visit %s.','admin templates', 'PDM' ),
                                    '<a href="http://plestar.net/premium-modules/" target="_blank" rel="noopener">http://plestar.net/premium-modules/</a>' ) );
        ?></p>
        <?php endif; ?>

        <div class="purchase-gateways cf">
        <?php
        foreach ( $modules as $mod_info ):
        ?>
        <div class="gateway <?php echo $mod_info[0]; ?> <?php echo pdl_has_module( $mod_info[0] ) ? 'installed' : ''; ?>">
            <a href="http://plestar.net/downloads/<?php echo $mod_info[1]; ?>/?ref=wp" target="_blank" rel="noopener">
                <img src="<?php echo PDL_URL; ?>assets/images/<?php echo $mod_info[1]; ?>.png" class="gateway-logo"><br />
                <a href="http://">
            </a>
            <?php if ( pdl_has_module( $mod_info[0] ) ): ?>
                <a href="http://plestar.net/downloads/<?php echo $mod_info[1]; ?>/?ref=wp"><?php echo $mod_info[2]; ?></a><br />
                <span class="check-mark">✓</span> <?php _ex( 'Already installed.', 'admin templates', 'PDM' ); ?>
            <?php else: ?>
            <?php echo str_replace(
                '<a>',
                '<a href="http://plestar.net/downloads/' . $mod_info[1] . '/?ref=wp" target="_blank" rel="noopener">',
                sprintf( _x( 'You can buy the <a>%s</a> to add <a>%s</a> as a payment option for your users.',
                             'admin templates',
                             'PDM' ), $mod_info[2], $mod_info[3] )
            ); ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>

<?php echo pdl_admin_footer(); ?>
