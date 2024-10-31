
<table class="pdl-payment-items-table" id="pdl-payment-items-<?php echo $payment->id; ?>">
    <thead>
        <tr>
            <th><?php _ex( 'Item', 'payment_items', 'PDM' ); ?></th>
            <th><?php _ex( 'Amount', 'payment_items', 'PDM' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $payment->payment_items as $item ): ?>
        <tr class="item <?php echo $item['type']; ?>">
            <td><?php print esc_html( $item['description'] ); ?></td>
            <td><?php echo pdl_currency_format( $item['amount'] ); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th><?php _ex( 'Total', 'payment_items', 'PDM' ); ?></th>
            <td class="total"><?php echo pdl_currency_format( $payment->amount ); ?>
        </tr>
    </tfoot>
</table>
