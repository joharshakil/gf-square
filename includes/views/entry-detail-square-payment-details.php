<?php
/**
 *
 */
?>
<div id="submitdiv" class="stuffbox">
    <h3 class="hndle">
        <span><?php _e('Square', 'gravity-forms-square'); ?></span>
    </h3>

    <div class="inside">
        <div id="submitcomment" class="submitbox">
            <div id="minor-publishing" style="padding:10px;">
                <?php
                if (!empty($entry['payment_status'])) {
                    $entry_id = $entry['id'];
                    $payment_status_returned_from_square = !empty( gform_get_meta( $entry_id, 'payment_status_returned') ) ? gform_get_meta( $entry_id, 'payment_status_returned') : gform_get_meta( $entry_id, 'payment_status_returned_' . $entry['transaction_id'] );
                    $mode = gform_get_meta( $entry['id'], 'payment_mode' );
                    if ( "test"===$mode ) {
                        $square_url = "https://squareupsandbox.com";
                    } else {
                        $square_url = "https://squareup.com";
                    }
                    ?> 
                    <p><strong><?php _e('Payment Mode', 'gravity-forms-square'); ?>: </strong><?php echo $mode; ?></p>                    
                    <p><strong><?php _e('Transaction Status', 'gravity-forms-square'); ?>: </strong><?php echo ( 'PENDING' === $payment_status_returned_from_square ) ? 'Refunded' : $payment_status_returned_from_square; ?></p>
                    <p><strong><?php _e('Transaction ID', 'gravity-forms-square'); ?>: </strong><?php echo $entry['transaction_id']; ?></p>
                    <p><strong><?php _e('Amount', 'gravity-forms-square'); ?>: </strong><?php echo GFCommon::to_money($entry['payment_amount'], $entry['currency']) ?></p>                                        
                    <?php
                    do_action('gfsr_after_simple_payment_details', $entry, $entry['transaction_id']);
                    ?>
                    <p><a class="button-secondary" target="_blank" href="<?php echo esc_html($square_url); ?>/dashboard/sales/transactions/<?php echo $entry['transaction_id']; ?>/"><?php _e('Click to view transaction in dashboard', 'gravity-forms-square' ); ?></a></p>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>