<div id="submitdiv" class="stuffbox">
    <h3 class="hndle">
        <span><?php _e('Payments History', 'gravity-forms-square'); ?></span>
    </h3>

    <div class="inside">
        <div id="submitcomment" class="submitbox">

            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'gravity-forms-square'); ?></th>
                        <th><?php _e('Transaction ID', 'gravity-forms-square'); ?></th>
                        <th><?php _e('Transaction Status', 'gravity-forms-square'); ?></th>
                        <th><?php _e('Action', 'gravity-forms-square'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <?php $payment_status_returned_from_square = !empty( gform_get_meta( $entry_id, 'payment_status_returned') ) ? gform_get_meta( $entry_id, 'payment_status_returned') : gform_get_meta( $entry_id, 'payment_status_returned_' . $transaction->transaction_id ); ?>
                        <tr>
                            <td><?php echo $transaction->created_at; ?></td>
                            <td><?php echo $transaction->transaction_id; ?></td>
                            <td class="trans_status"><?php echo ( 'PENDING' === $payment_status_returned_from_square ) ? 'Refunded' : $payment_status_returned_from_square; ?></td>
                            <td>
                                <?php do_action('gfsr_after_recurring_payment_details', $entry, $transaction->transaction_id); ?>
                            </td>
                            
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>