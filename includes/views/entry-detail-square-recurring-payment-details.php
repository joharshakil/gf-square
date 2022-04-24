<div id="submitdiv" class="stuffbox">
    <h3 class="hndle">
        <span><?php _e('Square Recurring', 'gravity-forms-square'); ?></span>
    </h3>

    <div class="inside">
        <div id="submitcomment" class="submitbox">
            <div id="minor-publishing" style="padding:10px;">
                <?php
                if (!empty($entry['payment_status'])) {
                    $entry_id = $entry['id'];
                    $mode = gform_get_meta( $entry['id'], 'payment_mode' );
                    if ( "test"===$mode ) {
                        $square_url = "https://squareupsandbox.com";
                    } else {
                        $square_url = "https://squareup.com";
                    }
                    ?> 
                    
                    <?php if ( isset($mode) && $mode!='' ) { ?>
                        <p><strong><?php _e('Payment Mode', 'gravity-forms-square'); ?>: </strong><?php echo $mode; ?></p>                    
                    <?php } ?>
                    <p><strong><?php _e('Status', 'gravity-forms-square'); ?>: </strong><?php echo $subscription_status ? __('Active', 'gravity-forms-square') : __('Cancelled', 'gravity-forms-square'); ?></p>                    
                    <p><strong><?php _e('Next Payment', 'gravity-forms-square'); ?>: </strong><?php echo $next_payment; ?></p>
                    <p><strong><?php _e('Amount', 'gravity-forms-square'); ?>: </strong><?php echo GFCommon::to_money($entry['payment_amount'], $entry['currency']) ?></p>
                    <?php if ( isset($entry['transaction_id']) && $entry['transaction_id']!='' ) { ?>
                        <p><a class="button-secondary" target="_blank" href="<?php echo esc_html($square_url); ?>/dashboard/sales/transactions/<?php echo $entry['transaction_id']; ?>/"><?php _e('Click to view transaction in dashboard', 'gravity-forms-square' ); ?></a></p>
                    <?php } ?>
                    
                    <?php
                    if ($subscription_status):
                        ?>
                        <a onclick="return confirm('<?php _e('Are you sure you want to cancel this subscription?', 'gravity-forms-square'); ?>')" href="admin.php?page=gf_entries&view=entry&id=<?php echo $form_id; ?>&lid=<?php echo $entry_id; ?>&gfsr_cancel=true" class="button-secondary"><?php _e('Cancel', 'gravity-forms-square'); ?></a>
                        <?php
                    endif;
                }
                ?>
            </div>
        </div>
    </div>
</div>