<?php if ( !empty($_POST) ): ?>
    
    <?php if ( !empty(trim($_POST['square_cof_save_card_text'])) /*&& !empty(trim($_POST['square_cof_non_logged_user_text']))*/ ): ?>
        
        <div id="after_update_dialog" class="updated below-h2">
            <p>
                <strong><?php _e('Card On File settings updated successfully.', 'gravity-forms-square') ?></strong>
            </p>
        </div>

    <?php else: ?>

        <div id="after_update_dialog" class="error below-h2">
            <p>
                <strong><?php _e('Please fill Card save text fields before saving.', 'gravity-forms-square') ?></strong>
            </p>
        </div>

    <?php endif; ?>

<?php endif; ?>


<h3><span><i class="fa fa-cogs"></i> <?php _e('Card On File Settings', 'gravity-forms-square') ?></span></h3>
<form method="post">
    <table class="gforms_form_settings" cellspacing="0" cellpadding="0">
        <tbody>
            
            <tr>
                <th>
                    <label> <?php _e('Save Card Option:', 'gravity-forms-square')?> </label>
                </th>
                <td>
                    <input type="radio" <?php if ($settings['square_cof_mode'] == 'enabled'): ?>checked="checked"<?php endif; ?> id="square_cof_mode_enable" value="enabled" name="square_cof_mode">
                    <label for="square_cof_mode_enable" class="inline"><?php _e('Enable', 'gravity-forms-sqaure'); ?></label>
                    &nbsp;&nbsp;&nbsp;
                    <input type="radio" <?php if ($settings['square_cof_mode'] == 'disabled'  || $settings['square_cof_mode'] == ''): ?>checked="checked"<?php endif; ?> id="square_cof_mode_disabled" value="disabled" name="square_cof_mode">
                    <label for="square_cof_mode_disabled" class="inline"><?php _e('Disable', 'gravity-forms-sqaure'); ?></label>
                </td>
            </tr>

            <tr>
                <th>
                    <?php _e('Non Logged User Text: ', 'gravity-forms-square') ?>
                    <button aria-label="<?php _e('This text will show on top of the form if user is not logged in with link to redirect to login page', 'gravity-forms-square');?>" class="gf_tooltip tooltip tooltip_form_title" onclick="return false;"><i class="fa fa-question-circle"></i></button>
                </th>
                <td>
                    <input type="text" value="<?php echo $settings['square_cof_non_logged_user_text']; ?>" class="fieldwidth-3" name="square_cof_non_logged_user_text">
                </td>
            </tr>

            <tr>
                <th>
                    <?php _e('Card Save Text: ', 'gravity-forms-square') ?>
                    <button aria-label="<?php _e('This text will show on checkbox for card save', 'gravity-forms-square'); ?>" class="gf_tooltip tooltip tooltip_form_title" onclick="return false;"><i class="fa fa-question-circle"></i></button>
                </th>
                <td>
                    <input type="text" value="<?php echo $settings['square_cof_save_card_text']; ?>" class="fieldwidth-3" name="square_cof_save_card_text">
                </td>
            </tr>

            <tr>
                <th>
                    <?php _e('Delete Card Option: ', 'gravity-forms-square') ?>
                    <button aria-label="<?php _e('If enable user can delete their save cards from square account.', 'gravity-forms-square'); ?>" class="gf_tooltip tooltip tooltip_form_title" onclick="return false;"><i class="fa fa-question-circle"></i></button>
                </th>
                <td>
                    <input type="radio" <?php if ($settings['square_cof_delete_card'] == 'enabled'): ?>checked="checked"<?php endif; ?> id="square_cof_delete_card_enable" value="enabled" name="square_cof_delete_card">
                    <label for="square_cof_delete_card_enable" class="inline"><?php _e('Enable', 'gravity-forms-sqaure'); ?></label>
                    &nbsp;&nbsp;&nbsp;
                    <input type="radio" <?php if ($settings['square_cof_delete_card'] == 'disabled'  || $settings['square_cof_delete_card'] == ''): ?>checked="checked"<?php endif; ?> id="square_cof_delete_card_disabled" value="disabled" name="square_cof_delete_card">
                    <label for="square_cof_delete_card_disabled" class="inline"><?php _e('Disable', 'gravity-forms-sqaure'); ?></label>
                </td>
            </tr>

            <?php // echo '<pre>'; print_r($settings); echo '</pre>'; ?>

        </tbody>
    </table>
    <input type="hidden" value="<?php echo $form_id; ?>" name="square_cof_form_id"/>  
    <input type="submit" class="button-primary gfbutton" value="Save Settings"/>
</form>