<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

if (!class_exists('Square_GF_Settings')) {

    class Square_GF_Settings {

        /**
         * Class Constructor
         */
        public function __construct() {

            //add square settings menu
            add_filter('gform_form_settings_menu', array($this, 'gform_form_settings_page_square'));
            add_action('gform_form_settings_page_square_settings_page', array($this, 'square_form_settings_page'));
            add_action('gform_form_settings_page_square_card_on_file_page', array($this, 'square_card_on_file_setting_page'));
            //save settings
            add_action('admin_init', array($this, 'square_form_settings_save'));
            add_action('admin_init', array($this, 'square_card_on_file_settings_save'));
            add_action('admin_footer', array( $this, 'add_domain_verification_js'));

            //add square recurring field custom fields
            global $gravity_forms_square;
            if ( gravity_forms_square()->is__premium_only() ) {

                if ( gravity_forms_square()->can_use_premium_code() ) {

                    add_action('gform_field_standard_settings', array($this, 'gfsr_recurring_fields'), 10);
                    add_action('gform_editor_js', array($this, 'gfsr_recurring_fields_script'));
                    add_action('admin_notices', array($this,'api_keys_not_found_for_form'));
                    add_action('admin_notices', array($this,'add_notice_for_order_sync'));    
                    add_action('wp_ajax_gfsqs_domain_verification', array( $this, 'gfsqs_domain_verification'));                
                    add_action('wp_ajax_nopriv_gfsqs_domain_verification', array( $this, 'gfsqs_domain_verification'));                
        
                }
            }

        }

        public function gfsqs_domain_verification () {
            
            if ( ! isset($_POST['nonce']) && ! wp_verify_nonce( sanitize_key($_POST['nonce']), 'nonce' ) && ! isset($_POST['form_id']) && empty($_POST['form_id']) ) {
				die('access denied! Nonce not verify.');
            }

            $gf_sqr_settings = get_option('gf_square_settings_' . $_POST['form_id']);
            $domain_verify_settings = get_option('gf_domain_verify_' . $_POST['form_id']);
            $api_client = null;
            $api_config = null;
            $token = null;

            if (isset($gf_sqr_settings['gf_squaree_mode']) && $gf_sqr_settings['gf_squaree_mode'] == 'test') {
                    
                $verify = ( isset($domain_verify_settings['sandbox']) && !empty($domain_verify_settings['sandbox']) ) ? $domain_verify_settings['sandbox'] : 0;
                $host = "https://connect.squareupsandbox.com";
                $token = $gf_sqr_settings['square_test_token'];

            } else {

                $verify = ( isset($domain_verify_settings['live']) && !empty($domain_verify_settings['live']) ) ? $domain_verify_settings['live'] : 0;
                $token = $gf_sqr_settings['square_token'];
            
                if (!is_object($gf_sqr_settings['square_auth_request'])) {
                    $gf_sqr_settings['square_auth_request'] = (object) $gf_sqr_settings['square_auth_request'];
                }

                if (!empty($gf_sqr_settings['square_auth_request']->access_token)) {
                    $new_obj = new Square_GF();
                    $square_auth_request = $new_obj->refresh_token($gf_sqr_settings['square_auth_request']);
                    $token = $square_auth_request->access_token;
                    if ($square_auth_request->save_db == true) {
                        if (!empty($token)) {
                            $gf_sqr_settings['square_token'] = $token;
                            $gf_sqr_settings['square_auth_request'] = $square_auth_request;
                            update_option('gf_square_settings_' . $_POST['form_id'], $gf_sqr_settings);
                        }
                    }
                }

                $host = "https://connect.squareup.com";

            }
            
            $site_url = preg_replace('#^https?://#', '', site_url());
            $source = GFSR_NEW_PLUGIN_PATH . 'verification/apple-developer-merchantid-domain-association';
            $path = $_SERVER['DOCUMENT_ROOT'] . '.well-known/';
            $destination = $_SERVER['DOCUMENT_ROOT'] . '.well-known/apple-developer-merchantid-domain-association';

            

            if ( ! is_dir ( $path ) ) {

                mkdir( $path, 0777, true);
                chmod( $path, 0777);

                $verify = 0;
                
            } else {

                chmod( $path, 0777);

            }


            if ( isset($_POST['reference']) && $_POST['reference'] == 'direct' ) {
                
                if ( $verify ) {
                    
                    $result['status'] = 'VERIFIED';
                    print_r(json_encode($result));

                } else {
                    
                    // update $verify in gf_square_settings_
                    if (isset($gf_sqr_settings['gf_squaree_mode']) && $gf_sqr_settings['gf_squaree_mode'] == 'test') {
                        
                        $domain_verify_settings['sandbox'] = $verify;

                    } else {

                        $domain_verify_settings['live'] = $verify;                

                    }

                    update_option('gf_domain_verify_' . $_POST['form_id'], $domain_verify_settings);
                    $result['status'] = 'NOT_VERIFIED';
                    print_r(json_encode($result));
                }

                //terminate immediately
                wp_die();
            }

            //get file directory
            $file_directory = dirname ( $path );

            if ( ! $verify ) {

                if ( is_writable($file_directory) ) {

                    if ( ! file_exists( $destination ) ) {

                        // moving file from plugin to .well-known folder
                        if ( ! copy ( $source, $destination ) ) {
                            
                            $result['status'] = 'not_moved';
                            $result['code']   = 'file does not copy to well-known folder. Please try another method.';
                            $result['detail'] = 'Please <a href="' . GFSR_NEW_PLUGIN_URL . 'verification/apple-developer-merchantid-domain-association' . '" target="_blank" download >Click here</a> to download file <br/> and upload to your .well-known folder and click verify button again.';
                            print_r(json_encode($result));
                            // for proper response
                            wp_die();
                        } 
                    } 
                
                } else {

                    $result['status'] = 'not_permission';
                    $result['code']   = '.well-known folder does not have write permission. Please try another method.';
                    $result['detail'] = 'Please <a href="' . GFSR_NEW_PLUGIN_URL . 'verification/apple-developer-merchantid-domain-association' . '" target="_blank" download >Click here</a> to download file <br/> and upload to your .well-known folder and click verify button again.';
                    print_r(json_encode($result));
                    // for proper response
                    wp_die();

                }

                $api_config = new \SquareConnect\Configuration();
                $api_config->setHost($host);
                $api_config->setAccessToken($token);
                $api_client = new \SquareConnect\ApiClient($api_config);        
                $apple_api = new SquareConnect\Api\ApplePayApi($api_client);

                try {
                    $body = array(
                        'domain_name' => $site_url,
                    );
                    $result = $apple_api->registerDomain($body);
                    $result = json_decode($result, true);
                    $verify = 1;
                    print_r(json_encode($result));
                    

                } catch ( Exception $ex ) {
                    
                    $verify = 0;
                    $errors = $ex->getResponseBody()->errors;
                    $errors['status'] = 'error';
                    echo json_encode($errors);

                }

            } else {
                $result['status'] = 'VERIFIED';
                print_r(json_encode($result));
            }
            
            // update $verify in gf_square_settings_
            if (isset($gf_sqr_settings['gf_squaree_mode']) && $gf_sqr_settings['gf_squaree_mode'] == 'test') {
                
                $domain_verify_settings['sandbox'] = $verify;

            } else {

                $domain_verify_settings['live'] = $verify;                

            }

            update_option('gf_domain_verify_' . $_POST['form_id'], $domain_verify_settings);
            
            // for proper response
            wp_die();
            

        }

        public function add_domain_verification_js () {
            wp_register_script('gfsq-domain-verification-js', SQGF_PLUGIN_URL . 'assets/js/domain-verification.js', array(), '1.0.0', false);
            wp_localize_script('gfsq-domain-verification-js', 'gfsqsadmin', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('custom_nonce'),
                'form_id' => isset($_REQUEST['id']) ? $_REQUEST['id'] : '',
            ));

            wp_enqueue_script('gfsq-domain-verification-js');
        }

        public function gfsr_recurring_fields($position) {
            if ($position == 50) {
                ?>      
                <li class="cardholder_name_setting field_setting">
                    
                    <label class="section_label">
                        <?php _e('Sub Labels', 'gravity-forms-square'); ?>
                    </label>

                    
                    <label for="card_num">
                        <?php _e('Card Number', 'gravity-forms-square'); ?><br>                        
                        <input type="text" class="fieldwidth-3" id="card_num" value="" onkeyup="SetFieldProperty( 'card_num', this.value );" />
                    </label>

                    <label for="card_exp">
                        <?php _e('Card Expiry', 'gravity-forms-square'); ?><br>                        
                        <input type="text" class="fieldwidth-3" id="card_exp" value="" onkeyup="SetFieldProperty( 'card_exp', this.value );" />
                    </label>

                    <label for="card_cvv">
                        <?php _e('Card CVV', 'gravity-forms-square'); ?><br>                        
                        <input type="text" class="fieldwidth-3" id="card_cvv" value="" onkeyup="SetFieldProperty( 'card_cvv', this.value );" />
                    </label>

                    <label for="card_zip">
                        <?php _e('Zipcode', 'gravity-forms-square'); ?><br>                        
                        <input type="text" class="fieldwidth-3" id="card_zip" value="" onkeyup="SetFieldProperty( 'card_zip', this.value );" />
                    </label>

                    <label for="card_name">
                        <?php _e('Cardholder Labels', 'gravity-forms-square'); ?><br>                        
                        <input type="text" class="fieldwidth-3" id="card_name" value="" onkeyup="SetFieldProperty( 'card_name', this.value );" />
                    </label>



                </li>  

                <li class="gfsqs_other_payment_type field_setting" style="display:none">
                    <label for="field_admin_label" class="section_label">
                        <?php _e('More Payment Options', 'gravity-forms-square'); ?> 
                        <?php echo '<small>('. __('this will not work with recurring payment', 'gravity-forms-square') .')</small>'; ?>                        
                    </label>

                    <ul>
                        <?php if(in_array(get_option('rg_gforms_currency') , array('USD','GBR','CAD','EUR', 'usd','gbr','cad','eur'))): ?>
                        <li>
                            <input type="checkbox" name="enable_applepay" id="enable_applepay" value="enabled" onclick="SetFieldProperty('enable_applepay', this.checked);" />
                            <label for="enable_applepay" style="margin:0!important">                                                        
                                <?php _e('Enable Apple Pay', 'gravity-forms-square'); ?>
                            </label>
                            
                            <div class="verify-btn-wrapper" style="display:none">
                                <div stlye="clear:both;height:15px;">&nbsp;</div>
                                <button id="domain-verification" class="verify-domain-for-apple-pay button button-large button-primary update-form">Verify Domain</button>
                            </div>
                        </li>
                            
                        <li>
                            <input type="checkbox" name="enable_googlepay" id="enable_googlepay" value="enabled" onclick="SetFieldProperty('enable_googlepay', this.checked);" />
                            <label for="enable_googlepay" style="margin:0!important">                                                        
                                <?php _e('Enable Google Pay', 'gravity-forms-square'); ?>
                            </label>
                        </li>

                         <?php endif; ?>
                        
                        <!-- <li>
                            <label for="enable_masterpass" style="margin:0!important">                        
                                <input type="checkbox" name="enable_masterpass" id="enable_masterpass" value="enabled" onclick="SetFieldProperty('enable_masterpass', this.checked);" />
                                <?php _e('Enable Master Pass', 'gravity-forms-square'); ?>
                            </label>
                        </li> -->
                        
                        <li>
                            <input type="checkbox" name="enable_giftcard" id="enable_giftcard" value="enabled" onclick="SetFieldProperty('enable_giftcard', this.checked);" />
                            <label for="enable_giftcard" style="margin:0!important">                                                        
                                <?php _e('Enable Square Gift Card', 'gravity-forms-square'); ?>
                            </label>
                        </li>
                    </ul>

                </li>

                <li class="recurring_cycle_setting field_setting">                    

                    <label for="field_admin_label" class="section_label">
                        <?php _e('Square Recurring', 'gravity-forms-square'); ?>                        
                    </label>
                    
                    <input type="checkbox" name="is_recurring" id="recurring_check" value="enabled" onclick="SetFieldProperty('isrecurring', this.checked);" />
                    <label for="recurring_check" style="margin:0!important">                        
                        <?php _e('Recurring Payment', 'gravity-forms-square'); ?>
                    </label> 

                    <div style="height:15px;clear:both">&nbsp;</div>

                    <select name="gfsr_subscription_cycle_interval" id="gfsr_subscription_cycle_interval" onchange="SetFieldProperty('gfsrcycleinterval', this.value);">
                        <option value=""><?php _e('select subscription interval', 'gravity-forms-square'); ?></option>
                        <option value="1"><?php _e('every 1', 'gravity-forms-square'); ?></option>
                        <option value="2"><?php _e('every 2nd', 'gravity-forms-square'); ?></option>
                        <option value="3"><?php _e('every 3rd', 'gravity-forms-square'); ?></option>
                        <option value="4"><?php _e('every 4th', 'gravity-forms-square'); ?></option>
                        <option value="5"><?php _e('every 5th', 'gravity-forms-square'); ?></option>
                        <option value="6"><?php _e('every 6th', 'gravity-forms-square'); ?></option>
                    </select>

                    <div style="height:15px;clear:both">&nbsp;</div>

                    <select name="gfsr_subscription_cycle" id="gfsr_subscription_cycle" onchange="SetFieldProperty('gfsrcycle', this.value);">
                        <option value=""><?php _e('select subscription cycle', 'gravity-forms-square'); ?></option>
                        <option value="day"><?php _e('day', 'gravity-forms-square'); ?></option>
                        <option value="week"><?php _e('week', 'gravity-forms-square'); ?></option>
                        <option value="month"><?php _e('month', 'gravity-forms-square'); ?></option>
                        <option value="year"><?php _e('year', 'gravity-forms-square'); ?></option>
                    </select>

                    <div style="height:15px;clear:both">&nbsp;</div>
                    
                    <select  name="gfsr_subscription_length" id="gfsr_subscription_length" onchange="SetFieldProperty('gfsrcyclelength', this.value);">
                        <option value=""><?php _e('Select Cycle Length', 'gravity-forms-square'); ?></option>
                        <option value="0"><?php _e('Never expire', 'gravity-forms-square'); ?></option>
                        <?php for ($index = 1; $index <= 24; $index++): ?>
                            <option   value="<?php echo $index; ?>"><?php _e('every', 'gravity-forms-square'); ?> <?php echo $index; ?> <?php echo $index == 1 ? __('cycle', 'gravity-forms-square') : __('cycles', 'gravity-forms-square'); ?></option>
                        <?php endfor; ?>
                    </select>

                </li>
                <?php
            }
        }

        public function gfsr_recurring_fields_script() {
            //echo 'test'; print_r(fieldSettings);
            ?>
            <script type='text/javascript'>
                //console.log(fieldSettings.square += ', .recurring_cycle_setting, .recurring_length_setting, .recurring_check');
                //fieldSettings.square += ', .recurring_cycle_setting';

                fieldSettings.square = '.conditional_logic_field_setting, .prepopulate_field_setting, .error_message_setting, .label_setting, .label_placement_setting, .admin_label_setting, .size_setting, .visibility_setting, .duplicate_setting, .default_value_setting, .placeholder_setting, .description_setting, .css_class_setting, .recurring_cycle_setting, .recurring_cycle_setting, .cardholder_name_setting, .gfsqs_other_payment_type';

                //console.log(fieldSettings.square);

                //jQuery('.rules_setting field_setting').remove();                

                jQuery(document).bind("gform_load_field_settings", function (event, field, form) {
                    console.log(field);
                    console.log(form);

                    if(field.type=='square') {

                        jQuery('#enable_applepay').on('click', function(){
                            if (jQuery(this).is(":checked")) {
                                jQuery(".verify-btn-wrapper").show();
                            } else {
                                jQuery(".verify-btn-wrapper").hide();
                            }
                        });

                        jQuery('#recurring_check').on('click', function(){

                            if(jQuery(this).is(":checked")) {
                                jQuery('li.gfsqs_other_payment_type').hide();
                                jQuery("#gfsr_subscription_cycle_interval").removeAttr("disabled");
                                jQuery("#gfsr_subscription_cycle").removeAttr("disabled");
                                jQuery("#gfsr_subscription_length").removeAttr("disabled");
                            } else {
                                jQuery('li.gfsqs_other_payment_type').show();
                                jQuery("#gfsr_subscription_cycle_interval").attr("disabled", true);
                                jQuery("#gfsr_subscription_cycle").attr("disabled", true);
                                jQuery("#gfsr_subscription_length").attr("disabled", true);
                            }
                        });

                        //cardholder field value
                        if (typeof field["card_num"] !== "undefined") {
                            //alert(field["cardholder_name_label"]);
                            jQuery("#card_num").attr("value", field["card_num"]);
                        } else {
                            jQuery("#card_num").attr("value", '');
                        }

                        if (typeof field["card_exp"] !== "undefined") {
                            //alert(field["cardholder_name_label"]);
                            jQuery("#card_exp").attr("value", field["card_exp"]);
                        } else {
                            jQuery("#card_exp").attr("value", '');
                        }

                        if (typeof field["card_cvv"] !== "undefined") {
                            //alert(field["cardholder_name_label"]);
                            jQuery("#card_cvv").attr("value", field["card_cvv"]);
                        } else {
                            jQuery("#card_cvv").attr("value", '');
                        }

                        if (typeof field["card_zip"] !== "undefined") {
                            //alert(field["cardholder_name_label"]);
                            jQuery("#card_zip").attr("value", field["card_zip"]);
                        } else {
                            jQuery("#card_zip").attr("value", '');
                        }

                        if (typeof field["card_name"] !== "undefined") {
                            //alert(field["cardholder_name_label"]);
                            jQuery("#card_name").attr("value", field["card_name"]);
                        } else {
                            jQuery("#card_name").attr("value", '');
                        }

                        if (field["enable_googlepay"]) {
                            jQuery("#enable_googlepay").attr("checked", "checked");
                        }
                        if (field["enable_applepay"]) {
                            jQuery("#enable_applepay").attr("checked", "checked");
                            jQuery(".verify-btn-wrapper").show();
                        } else {
                            jQuery(".verify-btn-wrapper").hide();
                        }
                        /*if (field["enable_masterpass"]) {
                            jQuery("#enable_masterpass").attr("checked", "checked");
                        }*/
                        if (field["enable_giftcard"]) {
                            jQuery("#enable_giftcard").attr("checked", "checked");
                        }

                        if (field["isrecurring"]){
                            jQuery("#recurring_check").attr("checked", "checked");

                            jQuery('li.gfsqs_other_payment_type').hide();

                            if (field["gfsrcycleinterval"])
                                jQuery("#gfsr_subscription_cycle_interval").val(field["gfsrcycleinterval"]);

                            if (field["gfsrcycle"])
                                jQuery("#gfsr_subscription_cycle").val(field["gfsrcycle"]);

                            if (field["gfsrcyclelength"])
                                jQuery("#gfsr_subscription_length").val(field["gfsrcyclelength"]);

                        } else {

                            jQuery('li.gfsqs_other_payment_type').show();

                            //jQuery("#gfsr_subscription_cycle_interval").val('');
                            jQuery("#gfsr_subscription_cycle_interval").attr("disabled", true);
                            //jQuery("#gfsr_subscription_cycle").val('');
                            jQuery("#gfsr_subscription_cycle").attr("disabled", true);
                            //jQuery("#gfsr_subscription_length").val('');
                            jQuery("#gfsr_subscription_length").attr("disabled", true);

                        }
                    }
                });
            </script>
            <?php
        }

        public function check_form_api_keys($form_id){
            $settings = get_option('gf_square_settings_' . $form_id);
            $check=false;
            if(isset($settings['gf_squaree_mode']) && $settings['gf_squaree_mode']=='test' && !empty($settings['square_test_appid']) && !empty($settings['square_test_locationid']) && !empty($settings['square_test_token']))
                $check=true;
            elseif(isset($settings['gf_squaree_mode']) && $settings['gf_squaree_mode']=='live' && !empty($settings['square_appid']) && !empty($settings['square_locationid']) && !empty($settings['square_token']))
                $check=true;
            else
                $check=false;  
            return $check;
        }

        public function api_keys_not_found_for_form() {
            if(isset($_GET['page']) && $_GET['page']=='gf_entries' && isset($_GET['id']) && !empty($_GET['id'])
           || isset($_GET['page']) && $_GET['page']=='gf_edit_forms' && isset($_GET['id']) && !empty($_GET['id'] )
            ) {
                if($this->check_form_api_keys($_GET['id'])==false) {
                    $class = 'notice notice-error';
                    $message=  __( 'Please add Square API keys', 'gravity-forms-square' );
                    printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
                }
           }
        }

        public function gform_form_settings_page_square($menu_items) {
            $menu_items[] = array(
                'name' => 'square_settings_page',
                'label' => __('Square')
            );

            $menu_items[] = array(
                'name' => 'square_card_on_file_page',
                'label' => __('Card On File')
            );

            return $menu_items;
        }

        public function square_form_settings_page() {
            $form_id = RGForms::get('id');
            $settings = get_option('gf_square_settings_' . $form_id);
            $locations = get_option('gf_square_settings_location_' . $form_id);
            if (!$settings)
                $settings = array(
                    'square_test_appid' => '',
                    'square_test_locationid' => '',
                    'square_test_token' => '',
                    'square_appid' => '',
                    'square_locationid' => '',
                    'square_token' => '',
                    'gf_squaree_mode' => '',
                    'create_sqr_order' => '',
                    'domain_verify_sandbox' => '',
                    'domain_verify_live' => '',
                    'authorize_only' => '',
                );

            GFFormSettings::page_header();
            require_once( SQGF_PLUGIN_PATH . 'includes/views/square-gf-settings.php' );
            GFFormSettings::page_footer();
        }

        public function square_card_on_file_setting_page() {
            $form_id = RGForms::get('id');
            $settings = get_option('gf_square_cof_settings_' . $form_id);
            
            if (!$settings) {
                $settings = array(
                    'square_cof_mode' => '',
                    'square_cof_non_logged_user_text' => '',
                    'square_cof_save_card_text' => '',
                    'square_cof_delete_card' => '',
                );
            }
            GFFormSettings::page_header();

            require_once( SQGF_PLUGIN_PATH . 'includes/views/square-card-on-file-settings.php' );
            
            GFFormSettings::page_footer();            
        }

        public function square_card_on_file_settings_save() {
            
            if(!rgpost('square_cof_form_id')){
				return;
            }

            if ( !empty(trim(rgpost('square_cof_save_card_text'))) /*&& !empty(trim(rgpost('square_cof_non_logged_user_text')))*/ ) {
                $settings = array(
                    'square_cof_mode' => trim(rgpost('square_cof_mode')),
                    'square_cof_non_logged_user_text' => trim(rgpost('square_cof_non_logged_user_text')),
                    'square_cof_save_card_text' => trim(rgpost('square_cof_save_card_text')),
                    'square_cof_delete_card' => trim(rgpost('square_cof_delete_card')),                    
                );
                update_option('gf_square_cof_settings_'. rgpost('square_cof_form_id'), $settings);

            }
            
        }

        public function square_form_settings_save() {
			
			if(!rgpost('square_form_id')){
				return;
			}
			
            $labels='';
            if(isset($_POST['square_labels'][0]) && $_POST['square_labels'][0]!='none'){
                $labels=implode(',',$_POST['square_labels']);
            }
            $settings = array(
                'square_test_appid' => trim(rgpost('square_test_appid')),
                'square_test_locationid' => trim(rgpost('square_test_locationid')),
                'square_test_token' => trim(rgpost('square_test_token')),
                'square_appid' => trim(rgpost('square_appid')),
                'square_locationid' => trim(rgpost('square_locationid')),
                'square_token' => trim(rgpost('square_token')),
                'gf_squaree_mode' => rgpost('gf_squaree_mode'),
                'gf_square_inputs' => $labels,
                'send_form_id_square'=> isset($_POST['send_form_id_square']) ? 1 : 0,
                'send_email_notification'=> isset($_POST['send_email_notification']) ? 1 : 0,
                'create_sqr_order'=> isset($_POST['create_sqr_order']) ? 1 : 0,
                'authorize_only' => isset($_POST['authorize_only']) ? 1 : 0,
            );

            if ( get_option('check_sqr_order', 'false') === 'false' ) {
                $settings['create_sqr_order'] = 0;
            }

			if(function_exists('mps_gfs_setting_form')){
				$setting_addon_mps = array(
					'square_mps_locationid' => trim(rgpost('square_mps_locationid')),
					'commission_amount' => trim(rgpost('commission_amount')),
					'commission_description' => trim(rgpost('commission_description')),
					'commission_type' => trim(rgpost('commission_type')),
					'application_secret' => trim(rgpost('application_secret')),
-					'vendor_return_page' => trim(rgpost('vendor_return_page')),
				);
				$settings = array_merge($settings,$setting_addon_mps);
			}
            $gf_square_settings = get_option('gf_square_settings_' . rgpost('square_form_id'));
			if(!empty($gf_square_settings['square_auth_request'])){
				$settings['square_auth_request'] = $gf_square_settings['square_auth_request'];
            }

            update_option('gf_square_settings_' . rgpost('square_form_id'), $settings);
        }

        public function add_notice_for_order_sync () {
            
            if ( isset($_POST['square_form_id']) && get_option('check_sqr_order', 'false') === 'false' ) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <?php echo sprintf('<p>%s </p>', GFSR_NEW_PLUGIN_NAME . ' - ' . __('"Create Order in Square" option will not enable untill you will re auth the application with square.', 'gravity-forms-square')); ?>
                </div>
                <?php
            }
        }

    }

}