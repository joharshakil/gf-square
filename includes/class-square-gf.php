<?php


class Square_GF extends GF_Field
{

    public $type = 'square';
    private static $transaction_response = '';
    private static $sent_email_notifications = array();
    
    public function __construct($data = array())
    {
        global $gravity_forms_square;
        if (gravity_forms_square()->is__premium_only()) {
            if (gravity_forms_square()->can_use_premium_code()) {
                parent::__construct($data);
                
                //add script
                add_action('wp_enqueue_scripts', array($this, 'gform_enque_custom_scripts'));
                add_action('admin_enqueue_scripts', array($this, 'admin_style_gfsqu'));
                
                //square payment proccess
                add_filter('gform_validation', array($this, 'payment_proccess'));
                
                //save transaction details
                add_filter('gform_entry_post_save', array($this, 'save_transaction_data'), 10, 2);
                
                //show transaction details
                add_action('gform_entry_detail_sidebar_middle', array($this, 'gform_entry_square_details'), 10, 2);

                //show transaction history
                add_action('gform_entry_detail_content_after', array($this, 'gform_entry_square_transaction_history'), 10, 2);
                add_action( 'gform_delete_entry', array($this, 'gform_before_entry_delete'), 10, 1 );
                
                //current screen
                add_action('admin_notices', array($this,'api_keys_not_found_for_form'));

                //add notes in entry for payment
                add_filter( 'gform_notification_note', array( $this, 'add_payment_note_in_entry'), 10, 3 );
                //add card details in form entry
                add_filter( 'gform_entry_field_value', array( $this, 'add_card_details_in_entry'), 10, 4 );
                
                add_filter('gform_pre_send_email', array($this,'add_transaction_info_to_email'), 10, 3);
                add_filter('gform_before_resend_notifications', array($this,'add_transaction_info_in_resend_notifications'), 10, 3);
                
            }
        }
    }

    public function gform_before_entry_delete ( $entry_id ) {
        //delete transaction
        global $wpdb;
        $gfsr_transactions_table = $wpdb->prefix . 'gfsr_transactions';
        $wpdb->delete( $gfsr_transactions_table, array( 'entry_id' => $entry_id ) );
    }

    public function add_payment_note_in_entry ( $note_args, $entry_id, $result ) {
        
        $currency = gform_get_meta( $entry_id, 'currency' );
        $amount = gform_get_meta( $entry_id, 'amount' );
        $transaction_id = gform_get_meta( $entry_id, 'transaction_id' );

        if ( $result === true && $transaction_id != '' && !empty(self::$transaction_response) ){  
            $note_args['type'] = __('notification', 'gravity-forms-square');
            $note_args['subtype'] = __('success', 'gravity-forms-square');  
            $note_args['text'] = sprintf(__( 'Payment has been %s. Amount %s %d, Transaction id: %s', 'gravity-forms-square' ), ucfirst( self::$transaction_response['status'] ), $currency, $amount, $transaction_id);
        }
        return $note_args;
    }

    public function add_card_details_in_entry ( $value, $field, $entry, $form ) {

        $card_type =  gform_get_meta( $entry['id'], 'payment_card_brand' );
        $card_num  =  '**** **** **** '. gform_get_meta( $entry['id'], 'payment_last_4' );
        $card_exp  =  gform_get_meta( $entry['id'], 'payment_card_exp' );
        $card_name =  gform_get_meta( $entry['id'], 'payment_card_name' );

        if ( $field->get_input_type() == 'square' && $card_type != '' ) { // Single file upload field
            
            $value = '<ul>';

            if ($card_type != '') {
                $value .= '<li>' . __( 'Card Type:', 'gravity-forms-square' ) . ' ' . $card_type . '</li>';
            }
            
            if ($card_num != '') {
                $value .= '<li>' . __( 'Card Number:', 'gravity-forms-square' ) . ' ' . $card_num . '</li>';
            }            
            
            if ($card_exp != '') {
                $value .= '<li>' . __( 'Card Exp:', 'gravity-forms-square' ) . ' ' . $card_exp . '</li>';
            }
            
            if ($card_name != '') {
                $value .= '<li>' . __( 'Card Name:', 'gravity-forms-square' ) . ' ' . $card_name . '</li>';
            }

            $value .= '</ul>';

        }
      
        return $value;

    }

    private function get_rnd_iv($iv_len)
    {
    
        $iv = '';
        while ($iv_len-- > 0) {
             $iv .= chr(mt_rand() & 0xff);
        }
    
         return $iv;
    }

    private function md5_encrypt($plain_text, $password, $iv_len = 16)
    {
    
        $plain_text .= "\x13";
        $n = strlen($plain_text);
        if ($n % 16) {
            $plain_text .= str_repeat("\0", 16 - ( $n % 16 ));
        }
    
        $i = 0;
        $enc_text = $this->get_rnd_iv($iv_len);
        $iv = substr($password ^ $enc_text, 0, 512);
        while ($i < $n) {
             $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
             $enc_text .= $block;
             $iv = substr($block . $iv, 0, 512) ^ $password;
             $i += 16;
        }
    
       // return base64_encode( $enc_text );
        return strtr(base64_encode($enc_text), '+/=', '-_,');
    }
    
    
    private function md5_decrypt($enc_text, $password, $iv_len = 16)
    {
    
        //$enc_text = base64_decode( $enc_text );
        $enc_text = base64_decode(strtr($enc_text, '-_,', '+/='));
        $n = strlen($enc_text);
        $i = $iv_len;
        $plain_text = '';
        $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
        while ($i < $n) {
             $block = substr($enc_text, $i, 16);
             $plain_text .= $block ^ pack('H*', md5($iv));
             $iv = substr($block . $iv, 0, 512) ^ $password;
             $i += 16;
        }
    
        return preg_replace('/\\x13\\x00*$/', '', $plain_text);
    }

    public function gform_enque_custom_scripts()
    {
        add_action('gform_register_init_scripts', array($this, 'payment_scripts'));
    }
    public function add_transaction_info_in_resend_notifications($form, $lead_ids)
    {
        $entry = GFAPI::get_entry($lead_ids[0]);
        
        foreach ($form['notifications'] as $notification_id => $notifications) {
            if (strpos($form['notifications'][$notification_id]['message'], '{square_payment_details}') !== false) {
                $entry = GFAPI::get_entry($lead_ids[0]);
                
                if (isset($entry['transaction_id']) && !empty($entry['transaction_id']) && isset($entry['payment_amount']) && !empty($entry['payment_amount'])) {
                    $amount=$entry['payment_amount'].' '.$entry['currency'];

                    $td_css='style="padding: 5px;border: 1px solid #dfdfdf; width: 99%;"';

                    $payment_status_returned_from_square = !empty( gform_get_meta($lead_ids[0], 'payment_status_returned' ) ) ? gform_get_meta($lead_ids[0], 'payment_status_returned' ) : gform_get_meta($lead_ids[0], 'payment_status_returned_' . $entry['transaction_id'] );

                    $payment_details_email='<table width="99%" cellspacing="0" cellpadding="1" style="border: 1px solid #dfdfdf;" ><tr bgcolor="#EAF2FA"><td colspan="2" '.$td_css.' >
                        <font style="font-family: sans-serif; font-size:12px;"><strong>' . esc_html__( 'Payment Details', 'gravity-forms-square' ) . '</strong></font>
                    </td></tr>';
                    $payment_details_email.='<tr><td><table width="100%" cellspacing="0" cellpadding="1" style="width: 94%;margin: 0 auto;">';
                    $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Transaction ID', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.$entry['transaction_id'].'</td></tr>';
                    $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Payment Amount', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.$amount.'</td></tr>';
                    $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Payment Status', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'. $payment_status_returned_from_square .'</td></tr>';
                    $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Card Brand', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.gform_get_meta($lead_ids[0], 'payment_card_brand').'</td></tr>';
                    $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Card Last 4', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.gform_get_meta($lead_ids[0], 'payment_last_4').'</td></tr>';
                    $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Created at', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.$entry['payment_date'].'</td></tr>';
                    $payment_details_email.='</table></td></tr></table>';

                    $form['notifications'][$notification_id]['message'] =str_replace("{square_payment_details}", $payment_details_email, $form['notifications'][$notification_id]['message']);
                }
            }
        }

        return $form;
    }


    public function add_transaction_info_to_email($email, $message_format, $notification) {
        
        $payment_details=self::$transaction_response;
        
        if (empty($payment_details) || ( !empty(self::$sent_email_notifications) && in_array($notification['id'], self::$sent_email_notifications))) {
            return $email;
        }

        if (strpos($email['message'], '{square_payment_details}') !== false) {
            $amount=$payment_details['amount'].' '.$payment_details['currency'];

            $td_css='style="padding: 5px;border: 1px solid #dfdfdf; width: 99%;"';


            $payment_details_email='<table width="99%" cellspacing="0" cellpadding="1" style="border: 1px solid #dfdfdf;" ><tr bgcolor="#EAF2FA"><td colspan="2" '.$td_css.' >
                <font style="font-family: sans-serif; font-size:12px;"><strong>' . esc_html__( 'Payment Details', 'gravity-forms-square' ) . '</strong></font>
            </td></tr>';
            $payment_details_email.='<tr><td><table width="100%" cellspacing="0" cellpadding="1" style="width: 94%;margin: 0 auto;">';
            $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Transaction ID', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.$payment_details['transaction_id'].'</td></tr>';
            $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Payment Amount', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.$amount.'</td></tr>';
            $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Payment Status', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.$payment_details['status'].'</td></tr>';
            $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Card Brand', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.$payment_details['card_brand'].'</td></tr>';
            $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Card Last 4', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.$payment_details['last_4'].'</td></tr>';
            $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Created at', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'.gmdate('Y-m-d h:i:sa').'</td></tr>';
            if ( 'enabled'===self::$transaction_response['is_recurring'] ) {     

                $next_payment = self::$transaction_response['next_payment'];
                //$subscription_interval = self::$transaction_response['subscription_interval'];
                $subscription_cycle = self::$transaction_response['subscription_cycle'];
                $subscription_length = self::$transaction_response['subscription_length'];

                $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Next Payment', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'. date('d-m-Y', $next_payment) .'</td></tr>';
                $payment_details_email.='<tr><td '.$td_css.' >' . esc_html__( 'Subscription For', 'gravity-forms-square' ) . '</td><td '.$td_css.' >'. $subscription_length .' '. $subscription_cycle .'</td></tr>';
    
            }
            $payment_details_email.='</table></td></tr></table>';


            $email['message'] =str_replace("{square_payment_details}", $payment_details_email, $email['message']);

            self::$sent_email_notifications[]=$notification['id'];
            return $email;
        }
        
        return $email;
    }

    /**
     * show transaction details
     * @param type $form
     * @param type $entry
     */
    public function gform_entry_square_details($form, $entry) {
        //wp_die('test');
        /*echo '<pre>';
            print_r($entry);
        echo '<pre>';*/
        $entry_id = $entry['id'];
        $form_id = $entry['form_id'];
        
        if(isset($_GET['gfsr_cancel']) && $_GET['gfsr_cancel'] == 'true'){
            gform_update_meta($entry_id, 'subscription_status', 0);
        }
        
        if ( isset($entry['payment_status']) && gform_get_meta($entry_id, 'payment_gateway') == 'squarerecurring' ) {
            
            $next_payment = gform_get_meta($entry_id, 'next_payment');
            if ($next_payment) {
                $next_payment = date('Y-m-d', $next_payment);
            }
            $subscription_status = gform_get_meta($entry_id, 'subscription_status');
            require_once( GFSR_NEW_PLUGIN_PATH . 'includes/views/entry-detail-square-recurring-payment-details.php' );
            
        } else if (isset($entry['payment_status']) && gform_get_meta($entry_id, 'payment_gateway') == 'square' && gform_get_meta($entry_id, 'is_recurring') == 'enabled' ) {
           // wp_die('test');
            $next_payment = gform_get_meta($entry_id, 'next_payment');
            if ($next_payment) {
                $next_payment = date('Y-m-d', $next_payment);
            }
            $subscription_status = gform_get_meta($entry_id, 'subscription_status');
            require_once( GFSR_NEW_PLUGIN_PATH . 'includes/views/entry-detail-square-recurring-payment-details.php' );
            
        } elseif (isset($entry['payment_status']) && gform_get_meta($entry_id, 'payment_gateway') == 'square' ) {
            //wp_die('test_1');
            require_once(SQGF_PLUGIN_PATH . 'includes/views/entry-detail-square-payment-details.php');
        } 
    }

    public function gform_entry_square_transaction_history($form, $entry) {
        //wp_die('I am here '. $entry['id']);
        //print_r($entry);
        $entry_id = $entry['id'];
        
        if (isset($entry['payment_status']) && gform_get_meta($entry_id, 'payment_gateway') == 'squarerecurring') {
            
            //insert transaction
            global $wpdb;
            $gfsr_transactions_table = $wpdb->prefix . 'gfsr_transactions';

            //check for log only once
            $transactions = $wpdb->get_results("select * from $gfsr_transactions_table where entry_id = $entry_id order by created_at desc");

            require_once( GFSR_NEW_PLUGIN_PATH . 'includes/views/entry-detail-square-payment-history.php' );
            
        } else if (isset($entry['payment_status']) && gform_get_meta($entry_id, 'payment_gateway') == 'square' && gform_get_meta($entry_id, 'is_recurring') == 'enabled' ) {
            
            //insert transaction
            global $wpdb;
            $gfsr_transactions_table = $wpdb->prefix . 'gfsr_transactions';

            //check for log only once
            $transactions = $wpdb->get_results("select * from $gfsr_transactions_table where entry_id = $entry_id order by created_at desc");

            require_once( GFSR_NEW_PLUGIN_PATH . 'includes/views/entry-detail-square-payment-history.php' );
        }
    }

    /**
     * save transaction details
     * @param type $entry
     * @param type $form
     * @return type
     */
    public function save_transaction_data($entry, $form) {

        $entry_id = rgar($entry, 'id');

        if (!empty(self::$transaction_response)) {

            $transaction_id = self::$transaction_response['transaction_id'];
            $amount = self::$transaction_response['amount'];
            $customer_card_id = self::$transaction_response['customer_card_id'];
            $customer_id = self::$transaction_response['customer_id'];

            if ( 'enabled'===self::$transaction_response['is_recurring'] ) {     
                
                $next_payment = self::$transaction_response['next_payment'];
                $subscription_interval = self::$transaction_response['subscription_interval'];
                $subscription_cycle = self::$transaction_response['subscription_cycle'];
                $subscription_length = self::$transaction_response['subscription_length'];
            }


            $payment_date = gmdate('Y-m-d H:i:s');
            $entry['currency'] = get_option('rg_gforms_currency');
            $entry['payment_status'] = self::$transaction_response['status'];
            $entry['payment_amount'] = $amount;
            $entry['is_fulfilled'] = true;
            $entry['transaction_id'] = $transaction_id;
            $entry['payment_date'] = $payment_date;

            GFAPI::update_entry($entry);

            if ( 'enabled'===self::$transaction_response['is_recurring'] ) {

                gform_update_meta($entry_id, 'payment_gateway', 'square');
                gform_update_meta($entry_id, 'is_recurring', 'enabled');
                gform_update_meta($entry_id, 'customer_card_id', $customer_card_id);
                gform_update_meta($entry_id, 'customer_id', $customer_id);
                gform_update_meta($entry_id, 'payment_status_returned_' . $transaction_id, self::$transaction_response['status']);
                gform_update_meta($entry_id, 'payment_card_brand', self::$transaction_response['card_brand']);
                gform_update_meta($entry_id, 'payment_last_4', self::$transaction_response['last_4']);
                gform_update_meta($entry_id, 'payment_card_exp', self::$transaction_response['card_exp']);
                gform_update_meta($entry_id, 'payment_card_name', self::$transaction_response['card_name']);
                gform_update_meta($entry_id, 'payment_mode', self::$transaction_response['payment_mode']);
                gform_update_meta($entry_id, 'next_payment', $next_payment);
                gform_update_meta($entry_id, 'subscription_status', 1);
                gform_update_meta($entry_id, 'subscription_payments_count', 1);
                gform_update_meta($entry_id, 'subscription_interval', $subscription_interval);
                gform_update_meta($entry_id, 'subscription_cycle', $subscription_cycle);
                gform_update_meta($entry_id, 'subscription_length', $subscription_length);
                gform_update_meta($entry_id, 'transaction_id', $transaction_id );
                gform_update_meta($entry_id, 'amount', $amount );
                gform_update_meta($entry_id, 'currency', get_option('rg_gforms_currency') );

            }

            if ( 'disabled'===self::$transaction_response['is_recurring'] ) {

                gform_update_meta($entry_id, 'payment_gateway', 'square');
                gform_update_meta($entry_id, 'is_recurring', 'disabled');
                gform_update_meta($entry_id, 'customer_card_id', $customer_card_id);
                gform_update_meta($entry_id, 'customer_id', $customer_id);
                gform_update_meta($entry_id, 'payment_status_returned_' . $transaction_id, self::$transaction_response['status']);
                gform_update_meta($entry_id, 'payment_card_brand', self::$transaction_response['card_brand']);
                gform_update_meta($entry_id, 'payment_last_4', self::$transaction_response['last_4']);
                gform_update_meta($entry_id, 'payment_card_exp', self::$transaction_response['card_exp']);
                gform_update_meta($entry_id, 'payment_card_name', self::$transaction_response['card_name']);
                gform_update_meta($entry_id, 'payment_mode', self::$transaction_response['payment_mode']);
                gform_update_meta($entry_id, 'transaction_id', $transaction_id );
                gform_update_meta($entry_id, 'amount', $amount );
                gform_update_meta($entry_id, 'currency', get_option('rg_gforms_currency') );
            }

            //insert transaction
            global $wpdb;
            $gfsr_transactions_table = $wpdb->prefix . 'gfsr_transactions';

            //check for log only once
            $transaction = $wpdb->get_row("select id from $gfsr_transactions_table where entry_id = $entry_id and transaction_id = '$transaction_id'");
            if (!$transaction) {
                $data = array(
                    'entry_id' => $entry_id,
                    'created_at' => date('Y-m-d', current_time('timestamp')),
                    'transaction_id' => $transaction_id
                );
                $format = array('%d', '%s', '%s');
                $wpdb->insert($gfsr_transactions_table, $data, $format);
            }
        }

        //wp_die('stop!');

        return $entry;
    }

    public function is_last_page($form)
    {

        $current_page = GFFormDisplay::get_source_page($form["id"]);
        $target_page = GFFormDisplay::get_target_page($form, $current_page, rgpost('gform_field_values'));

        return ( $target_page == 0 );
    }

    public function get_square_field($form)
    {
        $fields = GFCommon::get_fields_by_type( $form, array( 'square', 'squarerecurring' ) );
        $field = empty($fields) ? false : $fields[0];

        return $field;
    }

    public function is_square_ready_for_capture($validation_result)
    {

        $is_ready_for_capture = true;

        if (!empty(self::$transaction_response) || false == $validation_result['is_valid'] || !$this->is_last_page($validation_result['form'])) {
            $is_ready_for_capture = false;
        }

        //conditional logic check
        if (false !== $is_ready_for_capture) {
            //get square field
            $square_field = $this->get_square_field($validation_result['form']);

            if ($square_field && RGFormsModel::is_field_hidden($validation_result['form'], $square_field, array())) {
                $is_ready_for_capture = false;
            }
        }

        return $is_ready_for_capture;
    }
    
    /**
     * Process Gift Card
     */
    public function gfsqs_process_giftcard () {

        if ( isset($_POST['form_id']) && isset($_POST['nonce']) && $_POST['form_id']!='' && $_POST['nonce']!='' ) {

            $card_nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            $amount = $_POST['totalAmount'];

            if ($amount) {

                $arr['status'] = 'COMPLETED';
                $arr['nonce'] = $card_nonce;
                echo json_encode($arr);

            } else {
                $errors['status'] = 'PRICE_0';
                $errors['detail'] = 'Price should be greater than 0';
                echo json_encode($errors);
            }
            
            try {
                
                
            } catch (\SquareConnect\ApiException $ex) {

                $errors = $ex->getResponseBody()->errors;
                $errors['status'] = 'FAILED';                    
                echo json_encode($errors);
            }

        } else {

            $errors['status'] = 'PRICE_0';
            $errors['detail'] = __('Card Nonce empty', 'gravity-forms-square');
            echo json_encode($errors);
        }
        wp_die();

    }

    /**
     * square payment proccess
     * @param type $validation_result
     * @return string|boolean
     */
    public function payment_proccess($validation_result) { 
                

        $form = $validation_result['form'];
        $form_id = $form['id'];
        $fields = $form['fields'];
        $form_title = $form['title'];
        update_option('payment_request_post_form_id_'.$form_id.'_'.date("Y-m-d H:i:s"), $_POST);

        // if user hide square on condtional logic
        if ( isset( $_POST['sqgf_no_square'] ) && 'yes' == $_POST['sqgf_no_square'] ) {
            $validation_result['form'] = $form;
            return $validation_result;
        }
        //die('mufaddal 1');
        
        // if empty nonce submit as simple form
        if ( empty($_POST['sqgf_square_nonce']) && isset($_POST['gf-square-stored-cards']) && empty($_POST['gf-square-stored-cards']) ) {
            $validation_result['form'] = $form;
            return $validation_result;
        }
        
        //get product field index
        $product_field_index = 0;
        $square_field_index = 0;
        //check if square exist
        $is_square = false;
        $is_coupon = false;

        $is_recurring = 'disabled';
        $subscription_interval = null;
        $subscription_cycle = null;
        $subscription_length = null;
        $conditional_logic = 'disabled';
        $is_multiple_square = 'disabled';
        $pageNumber = 0;
		$is_booking_field = false;

        foreach ($fields as $key => $field) {
            
			if ( $field->type == 'gfb_appointment_cost' ) {
				$is_booking_field = true;
				$booking_cost = $_POST['input_' . $field->id ];
			}
			
            if ($field->type == 'square') {
                $square_field_index = $key;
                $pageNumber = $field->pageNumber;
                $is_square = true;
                $cssClass = $field->cssClass;

                //other payments
                if ( isset($field->enable_applepay) && $field->enable_applepay=='1') {
                    $gfsqs_applepay = 'enabled';
                } else {
                    $gfsqs_applepay = 'disabled';
                }
                
                if ( isset($field->enable_googlepay) && $field->enable_googlepay=='1') {
                    $gfsqs_googlepay = 'enabled';
                } else {
                    $gfsqs_googlepay = 'disabled';
                }
                
                /*if ( isset($field->enable_masterpass) && $field->enable_masterpass=='1') {
                    $gfsqs_masterpass = 'enabled';
                } else {
                    $gfsqs_masterpass = 'disabled';
                }*/
                
                /*if ( isset($field->enable_giftcard) && $field->enable_giftcard=='1') {
                    $gfsqs_giftcard = 'enabled';
                } else {
                    $gfsqs_giftcard = 'disabled';
                }*/

                // echo '<pre>';
                // print_r($field);
                // echo '</pre>';
                // wp_die('stop!');

                if ('gf-multiple' === $cssClass) {
                    //wp_die('working!');
                    $is_multiple_square = 'enabled';
                    $rules = array (
                        'fieldId'  => $field->conditionalLogic['rules'][0]['fieldId'],                        
                    );
                } else {
                    if ( $field->conditionalLogic !='' AND 'gf-multiple' === $cssClass ) {
                        $conditional_logic = 'enabled';
                        $rules = array (
                            'fieldId'  => $field->conditionalLogic['rules'][0]['fieldId'],
                            'operator' => $field->conditionalLogic['rules'][0]['operator'],
                            'value'    => $field->conditionalLogic['rules'][0]['value']
                        );
                    } else {
                        $conditional_logic = 'disabled';
                    }
                     
                }

                if ($field->isrecurring=='1') {
                    $is_recurring = 'enabled';
                }
                
                $subscription_interval = $field->gfsrcycleinterval;
                $subscription_cycle = $field->gfsrcycle;
                $subscription_length = $field->gfsrcyclelength;

            } elseif ($field->type == 'product') {
                $product_field_index = $key;
            }

            //check if coupon is exist
            if ($field->type == 'coupon') {
                $is_coupon = true;
            }

        }

        if ( $conditional_logic === 'enabled' ) {

            if ( $rules['operator'] === 'is' ) {

                if ( $rules['value'] !== @$_POST['input_' . $rules['fieldId']] ) {
                    return $validation_result;
                }
            }

            if ( $rules['operator'] === 'isnot' ) {

                if ( $rules['value'] == @$_POST['input_' . $rules['fieldId']] ) {
                    return $validation_result;
                }
            }

            if ( $rules['operator'] === '>' ) {

                if ( $rules['value'] < @$_POST['input_' . $rules['fieldId']] ) {
                    return $validation_result;
                }
            }

            if ( $rules['operator'] === '<' ) {

                if ( $rules['value'] > @$_POST['input_' . $rules['fieldId']] ) {
                    return $validation_result;
                }
            }

        }

        if ( $is_multiple_square === 'enabled' ) {
            
            if ('simple' === $_POST['input_' . $rules['fieldId']] ) {
                $is_recurring = 'disabled';
            }

            if ('recurring' === $_POST['input_' . $rules['fieldId']] ) {
                $is_recurring = 'enabled';
            }
        }

        if ( $_POST['gform_source_page_number_' . $form_id] == $pageNumber ) {
            if (!$this->is_square_ready_for_capture($validation_result) && ( isset($_POST['cardholder_name']) ) ) {
                return $validation_result;
            }

        } else {
            return $validation_result;
        }


        //get form square settings
        $settings = get_option('gf_square_settings_' . $form_id);
        
        //get form square settings
        $cof_settings = get_option('gf_square_cof_settings_' . $form_id);

        
        $token = null;
        $location_id = null;
        $mode = isset($settings['gf_squaree_mode']) ? $settings['gf_squaree_mode'] : 'test';

        if (isset($settings['gf_squaree_mode']) && $settings['gf_squaree_mode'] == 'test') {
            $token = $settings['square_test_token'];
            $location_id = $settings['square_test_locationid'];
            $appid = $settings['square_test_appid'];
            $host = "https://connect.squareupsandbox.com";
           
        } else {
            $token = $settings['square_token'];
            if (!is_object($settings['square_auth_request']))
                $settings['square_auth_request'] = (object) $settings['square_auth_request'];
            
            if (!empty($settings['square_auth_request']->access_token)) {
                $settings['square_auth_request']->send_email_notification = (empty($settings['send_email_notification'])) ? 0 : $settings['send_email_notification'] ;
                $square_auth_request = $this->refresh_token($settings['square_auth_request']);
                $token = $square_auth_request->access_token;
                if ($square_auth_request->save_db == true) {
                    if (!empty($token)) {
                        $settings['square_token'] = $token;
                        $settings['square_auth_request'] = $square_auth_request;
                        update_option('gf_square_settings_'.$form_id, $settings);
                    }
                }
            }
            $host = "https://connect.squareup.com";
            $location_id = $settings['square_locationid'];
            $appid = $settings['square_appid'];

        }
            
        //creating instance for customer API
        $api_config = new \SquareConnect\Configuration();
        $api_config->setHost($host);
        $api_config->setAccessToken($token);
        $api_client = new \SquareConnect\ApiClient($api_config);        
        $customer_api = new \SquareConnect\Api\CustomersApi($api_client);

        if ($is_square && $token && $location_id) {
			
            $card_nonce = isset($_POST['sqgf_square_nonce']) ? $_POST['sqgf_square_nonce'] : '';
            $verification_token = isset($_POST['sqgf_square_verify']) ? $_POST['sqgf_square_verify'] : '';
			
            //wp_die('test');


            if (!is_user_logged_in()) {
                foreach ($fields as $field) {
                    if ($field->type == 'email') {
                        $email_field_id = $field->id;
                        //break;
                    }
        
                    if ($field->type == 'name') {
                        $name_field_id = $field->id;
                        //break;
                    }
                }
        
                if (isset($_POST['input_'.@$email_field_id])) {
                    $email = $_POST['input_'.$email_field_id];
                    $user_id = email_exists($email);
                }
        
                if (isset($_POST['input_'.@$name_field_id.'_3'])) {
                    $first_name = $_POST['input_'.@$name_field_id.'_3'];
                }
        
                if (isset($_POST['input_'.@$name_field_id.'_6'])) {
                    $last_name = $_POST['input_'.@$name_field_id.'_6'];
                }
            } else {
                //wp_die('I am here');
                $current_user = wp_get_current_user();
                //print_r($current_user);
                $user_id = $current_user->ID;
                if (isset($user_id)) {
                    $email = $current_user->user_email;
                    $first_name = get_user_meta($user_id, 'first_name', true);
                    $last_name = get_user_meta($user_id, 'last_name', true);
                }
            }
            
            if ( 'enabled'===@$cof_settings['square_cof_mode'] || 'enabled'===$is_recurring ) { 
    
                // creating wp user if not exist already
                if (!$user_id) {
                    $random_password = wp_generate_password(12, false);
                    $user_id = wp_create_user($email, $random_password, $email);
                    if (!is_wp_error($user_id)) {
                        update_user_meta($user_id, 'first_name', $first_name);
                        update_user_meta($user_id, 'last_name', $last_name);
                        wp_new_user_notification($user_id, null, 'both');
                    }
                }
                
                if (get_user_meta($user_id, 'gf_square_customer_id', true)) {
                    $square_customer_id = get_user_meta($user_id, 'gf_square_customer_id', true);
                } else {
                    $square_customer_id = 0;
                }
				
                // creating square customer if not exist
                if ($square_customer_id===0) {
                    //create customer
                    $square_customer_details = array(
                        'given_name' => $first_name,
                        'family_name' => $last_name,
                        'email_address' => $email
                    );
    
                    $square_customer = $customer_api->createCustomer($square_customer_details);
					
                    $square_customer = json_decode($square_customer, true);
                    if (isset($square_customer['customer']['id'])) {
                        $square_customer_id = $square_customer['customer']['id'];
                        update_user_meta($user_id, 'gf_square_customer_id', $square_customer_id);
                    }
                }

                //check if there is customer id and not exist in square account
                if ($square_customer_id) {
                    $is_customer_found = true;
                    try {
                        
                        $customer = $customer_api->retrieveCustomer($square_customer_id);
                    } catch (Exception $ex) {
                        //customer not exist
                        $errors = $ex->getResponseBody()->errors;
                        

                        if( 'NOT_FOUND' == $errors[0]->code ) {
                            $is_customer_found = false;
                        }
                                           
                    }

                    if(!$is_customer_found){
                        
                        $status = delete_user_meta($user_id, 'gf_square_customer_id');
                        delete_user_meta($user_id, 'gf_square_customer_card_id');
    
                        if ($status) {
                            $square_customer_details = array(
                                'given_name' => $first_name,
                                'family_name' => $last_name,
                                'email_address' => $email
                            );
                           
                            $square_customer = $customer_api->createCustomer( $square_customer_details);
                           
                            $square_customer = json_decode($square_customer, true);
                            if (isset($square_customer['customer']['id'])) {
                                $square_customer_id = $square_customer['customer']['id'];
                                update_user_meta($user_id, 'gf_square_customer_id', $square_customer_id);
                            }
                        }
                    }
                }
                
            } //check if card on file is enabled

            if ( 'enabled'===$is_recurring ) {
                //wp_die('I am here!');
                // try {
				if ( isset($_POST['cardholder_name']) && $_POST['cardholder_name']!='' ) {
					//die('Test');
					// $customerCard = $customer_api->createCustomerCard($square_customer_id, array(
						// 'card_nonce' => $card_nonce,
						// 'verification_token' => $verification_token,
						// 'cardholder_name' => $first_name . ' ' . $last_name
						// 'cardholder_name' => $_POST['cardholder_name']
					// ));

					$idempotencyKey = time();
					if (isset($settings['gf_squaree_mode']) && $settings['gf_squaree_mode'] == 'test') {
							$square_modd = 'squareupsandbox';
					}else{
							$square_modd = 'squareup';
					}

					$url = "https://connect.".$square_modd.".com/v2/cards";

					$headers = array(
						'Accept' => 'application/json',
						'Authorization' => 'Bearer '.$token,
						'Content-Type' => 'application/json',
						'Cache-Control' => 'no-cache'
					);
					
					$data = array(
						'idempotency_key' => (string) $idempotencyKey,
						'source_id' => $card_nonce,
						'verification_token' => $verification_token,
						'card' => array(
							'customer_id' => $square_customer_id,
							'cardholder_name' => $_POST['cardholder_name'],
						)
					);
				
					$result = json_decode(wp_remote_retrieve_body(wp_remote_post($url, array(
						'method' => 'POST',
						'headers' => $headers,
						'httpversion' => '1.0',
						'sslverify' => false,
						'body' => json_encode($data)
						)))
					);
					
					//save customer card
					// $customerCardData = json_decode($customerCard, true);

					if (isset($result->card->id)) {
						$customerCardId = $result->card->id;
						$user_saved_card_id = get_user_meta($user_id, 'gf_square_customer_card_id', false);
						
						if (!empty($customerCardId)) {
							array_push($user_saved_card_id, $customerCardId);
						}
						
						update_user_meta($user_id, 'gf_square_customer_card_id', $user_saved_card_id);
					} else{
					// } catch (Exception $ex) {
						// $errors = $result->getResponseBody()->errors;
						

						//$errors = $result->errors;
						$message = '';
						foreach ($result->errors as $error) {
							$message = $error->detail;
							if (isset($error->field)) {
								$message = $error->field . ' - ' . $error->detail;
							}
						}
						$validation_result['is_valid'] = false;
						$form["fields"][$square_field_index]["failed_validation"] = true;
						$form["fields"][$square_field_index]["validation_message"] = $message;
						$validation_result['form'] = $form;
						return $validation_result;
					// }
					}  
				}
            }
            

            if ( 'disabled'===$is_recurring ) {
                if ( (isset($_POST['gf_square_payment_toggler']) && 'gf_sqquare_container_'.$form_id==$_POST['gf_square_payment_toggler']) && (isset($_POST['save_card_for_future']) && 'yes'==$_POST['save_card_for_future']) ) {
                    //create customer card
                    try {
                        //$customerCard_api = new \SquareConnect\Api\CustomerCardApi($api_client);
                        if ( isset($_POST['cardholder_name']) && $_POST['cardholder_name']!='' ) {
                            $customerCard = $customer_api->createCustomerCard( $square_customer_id, array(
                                'card_nonce' => $card_nonce,
                                'verification_token' => $verification_token,
                                //'cardholder_name' => $first_name . ' ' . $last_name
                                'cardholder_name' => $_POST['cardholder_name']
                            ));

                            //save customer card
                            $customerCardData = json_decode($customerCard, true);
            
                            if (isset($customerCardData['card']['id'])) {
                                $customerCardId = $customerCardData['card']['id'];
                                $user_saved_card_id = get_user_meta($user_id, 'gf_square_customer_card_id', false);
                                
                                if (!empty($customerCardId)) {
                                    array_push($user_saved_card_id, $customerCardId);
                                }
                                
                                update_user_meta($user_id, 'gf_square_customer_card_id', $user_saved_card_id);
                            }
                        }

                    } catch (Exception $ex) {
                        $errors = $ex->getResponseBody()->errors;
                        $message = '';
                        foreach ($errors as $error) {
                            $message = $error->detail;
                            if (isset($error->field)) {
                                $message = $error->field . ' - ' . $error->detail;
                            }
                        }

                        $validation_result['is_valid'] = false;
                        $form["fields"][$square_field_index]["failed_validation"] = true;
                        $form["fields"][$square_field_index]["validation_message"] = $message;
                        $validation_result['form'] = $form;
                        return $validation_result;
                    }
                }                
            }            

            try {
                $amount = 0;
                $tmp_lead = RGFormsModel::create_lead($form);
                $products = GFCommon::get_product_fields($form, $tmp_lead);                
                foreach ($products['products'] as $product) {
                    $quantity = $product['quantity'] ? $product['quantity'] : 1;
                    $product_price = GFCommon::to_number($product['price']);

                    $options = array();

                    if (isset($product['options']) && is_array($product['options'])) {
                        foreach ($product['options'] as $option) {
                            $options[] = $option['option_label'];
                            $product_price += $option['price'];
                        }
                    }

                    $amount += $product_price * $quantity;
                }

                //print_r($products['shipping']); 

                if ($amount && $amount > 0) {
                    if (isset($products['shipping']) && is_array($products['shipping'])) {
                        $amount =   $amount + $products['shipping']['price'];
                    }
                    /*   Note */
                    $note = '';
                    //$note = 'Gravity Form - '. $form_title;                    
                    if (isset($settings['send_form_id_square']) && $settings['send_form_id_square']==1) {
                        $note.=' Form#'.$form_id.' ';
                    }
                    if (isset($settings['gf_square_inputs']) && !empty($settings['gf_square_inputs'])) {
                        $inputs=explode(',', $settings['gf_square_inputs']);
                        foreach ($inputs as $input) {
                            if (isset($_POST['input_'.str_replace('.', '_', $input)])) {
                                 $note.=sanitize_text_field($_POST['input_'.str_replace('.', '_', $input)]).' ';
                            }
                        }
                    }

                    if(empty($note)) {
                        $note = 'Gravity Form - '. $form_title;
                    }
                    
                    $note=substr($note, 0, 59);
                   // echo $note; exit;
                    /* ends Note */
                    
                    if (function_exists('mps_gfs_setting_form') and !empty($settings['square_mps_locationid'])) {
                        if (empty($settings['commission_description'])) {
                            $description = __('Gravity Form Additional Recipients Amount', 'gravity-forms-square');
                        } else {
                            $description = $settings['commission_description'];
                        }
                        if ($settings['commission_type'] == 'flat' and is_numeric($settings['commission_amount'])) {
                            //$amount += $settings['commission_amount'];
                            $additional_recipients_amount = round($settings['commission_amount'], 2) * 100;
                            $additional_recipients = array(array(
                                    'location_id' => $location_id,
                                    'description' => $description,
                                    'amount_money' =>  array(
                                        'amount' => $additional_recipients_amount,
                                        'currency' => get_option('rg_gforms_currency')
                                    ),
                                ));
                        } elseif ($settings['commission_type'] == 'percentage') {
                            $additional_recipients_amount = $amount*$settings['commission_amount']/100;
                            //$amount += $additional_recipients_amount;
                            $additional_recipients_amount = round($additional_recipients_amount, 2) * 100;
                            $additional_recipients = array(array(
                                    'location_id' => $location_id,
                                    'description' => $description,
                                    'amount_money' =>  array(
                                        'amount' => $additional_recipients_amount,
                                        'currency' => get_option('rg_gforms_currency')
                                    ),
                                ));
                        }
                        if (new DateTime() > new DateTime($settings['square_token_mps_expire'])) {
                        # current time is greater than 2010-05-15 16:00:00
                        # in other words, 2010-05-15 16:00:00 has passed
                            $return_response = renew_access_token($appid, @$settings['application_secret'], $token);
                            if ($return_response['response']['code'] == 200 and $return_response['response']['message'] == 'OK') {
                                $body = json_decode($return_response['body']);
                                $settings['square_token'] = $token = $body->access_token;
                                $settings['square_token_mps_expire'] = $body->expires_at;
                                update_option('gf_square_settings_'.$form_id, $settings);
                            }
                        }
                    }
                    // Addon Mps end


                    // echo '<pre>$settings$settings';
                    // print_r($settings);
                    // echo '</pre>';
                    // wp_die();

                    if ( isset($settings['create_sqr_order']) && $settings['create_sqr_order']==1 ) {
                    
                        /**
                        * Create order for square here
                        */
                        $line_items        = array();
                        foreach ($products['products'] as $product) {
                            $quantity = $product['quantity'] ? $product['quantity'] : 1;
                            $product_price = GFCommon::to_number($product['price']);

                            $line_items[] = array(
                                'name'             => $product['name'],
                                'quantity'         => (string) $quantity,
                                'base_price_money' => array(
                                    'amount'   => $product_price * 100,
                                    'currency' => get_option('rg_gforms_currency'),
                                )
                            );
                        }

                        // check if shipping is enable add shipping price also.
                        if ( isset($products['shipping']) && $products['shipping']['price'] != '' ) {
                            
                            $line_items[] = array(
                                'name'             => $products['shipping']['name'],
                                'quantity'         => '1',
                                'base_price_money' => array(
                                    'amount'   => $products['shipping']['price'] * 100,
                                    'currency' => get_option('rg_gforms_currency'),
                                )
                            );
                        }

                        $order_data = array(
                            'idempotency_key' => uniqid(),
                            'order'           => array(
                                'location_id' => $location_id,
                                'customer_id' => @$square_customer_id,
                                'line_items'  => $line_items,
                            ),
                        );

                        /* Creating Order */
                        $order_request = new SquareConnect\Model\CreateOrderRequest($order_data);
                        $order_api     = new SquareConnect\Api\OrdersApi($api_client);
                        try {
                            $response = $order_api->createOrder($location_id, $order_request);
                            $order_id = $response->getOrder()->getId();
                            $order_amount = $response->getOrder()->getTotalMoney()->getAmount();                            
                        } catch (Exception $ex) {
                            $order_create_errors = $ex->getResponseBody()->errors;
                            update_option('payment_order_create_error_form_id_'.$form_id.'_'.date("Y-m-d H:i:s"), $order_create_errors);
                        }

                    } //end of $settings['create_sqr_order'] check
                    
					if ( class_exists('gfb_bookings_addon') && $is_booking_field ) {
						$amount = round($booking_cost, 2) * 100;
					} else {
						$amount = round($amount, 2) * 100;
					}					
                    
                    $idempotencyKey = time();
                    $payments_api = new \SquareConnect\Api\PaymentsApi($api_client);
                    $body = new \SquareConnect\Model\CreatePaymentRequest();
                    $amountMoney = new \SquareConnect\Model\Money();


                    // For Square Gift Card Processing
                    if ( isset($_POST['giftcard_nonce']) && isset($_POST['pay_with_giftcard']) && $_POST['pay_with_giftcard'] == 'yes' ) {

                        if('test'===$mode) {
                            $body->setSourceId('cnon:gift-card-nonce-ok');
                            //$body->setSourceId($card_nonce);
                        } else {
                            $body->setSourceId($card_nonce);
                        }

                    }
                    
                    //for reccurring payments
                    if ( ('enabled'===$is_recurring || $is_multiple_square == 'enabled') && !isset($_POST['giftcard_nonce']) ) { 
                        
                        if (empty($square_customer_id) || $square_customer_id===0) {
                            $square_customer_id = get_user_meta($user_id, 'gf_square_customer_id', true);
                        }

                        if (is_user_logged_in()) {
							
                            // if paying with new card
                            if (isset($_POST['gf_square_payment_toggler']) && 'gf_sqquare_container_'.$form_id==$_POST['gf_square_payment_toggler']) {
                                
								if ('test'===$mode) {                                  
                                    $body->setSourceId("ccof:customer-card-id-ok");
                                } else {
                                    $body->setSourceId($customerCardId);
                                }
                            } else {
								
								
                                if (isset($_POST['gf-square-stored-cards']) && !empty($_POST['gf-square-stored-cards'])) {                                    
                                    $customerCardId = $this->md5_decrypt($_POST['gf-square-stored-cards'], 'x12admin');
                                    if ('test'===$mode) {
                                        $body->setSourceId("ccof:customer-card-id-ok");
                                    } else {                                        
                                        $body->setSourceId($customerCardId);
                                    }
                                } else {
									//for only admin recurring users no toggler no save card just recurring submission;
									if ('test'===$mode) {
                                        $body->setSourceId("ccof:customer-card-id-ok");
                                    } else {                                        
                                        $body->setSourceId($customerCardId);
                                    }
								}
								
								
                            }
                        } else {
                            if ('test'===$mode) {
                                $body->setSourceId("ccof:customer-card-id-ok");
                            } else {
                                $body->setSourceId($customerCardId);
                            }
                        }
						
                         if ( !empty($square_customer_id) ) {
                            //customer id is mandatory
                            $body->setCustomerId($square_customer_id);
                        }

                    }

                    //for simple payments
                    if ( ( 'disabled'===$is_recurring || $is_multiple_square == 'enabled' ) && !isset($_POST['giftcard_nonce']) ) {
                        if ( isset($cof_settings) && 'enabled'===@$cof_settings['square_cof_mode'] ) { 
                            if (is_user_logged_in()) {
                                // if paying with new card
                                if ( (isset($_POST['gf_square_payment_toggler']) && 'gf_sqquare_container_'.$form_id==$_POST['gf_square_payment_toggler']) || isset($_POST['gfsqs-other-payments']) /*&& 'gfsq-google-pay-'.$form_id.'-wrapper'==$_POST['gfsqs-other-payments']*/ ) {
                                    //wp_die('payment with new card!');
                                    if (isset($_POST['save_card_for_future']) && 'yes'==$_POST['save_card_for_future']) {                                        
                                        if ('test'===$mode) {
                                            $body->setSourceId("ccof:customer-card-id-ok");
                                        } else {
                                            $body->setSourceId($customerCardId);
                                        }
                                        $body->setCustomerId($square_customer_id);                                        
                                    } else {
                                        $body->setSourceId($card_nonce);
                                        $body->setVerificationToken($verification_token);
                                    }
                                } else {
                                    //wp_die('payment with stored card!');
                                    if (isset($_POST['gf-square-stored-cards']) && !empty($_POST['gf-square-stored-cards'])) {                                        
                                        $customerCardId = $this->md5_decrypt($_POST['gf-square-stored-cards'], 'x12admin');
                                        if ('test'===$mode) {
                                            $body->setSourceId("ccof:customer-card-id-ok");
                                        } else {                                            
                                            $body->setSourceId($customerCardId);
                                        }
                                        $body->setCustomerId($square_customer_id);
                                    }
                                }
                            } else {
                                $body->setSourceId($card_nonce);
                                $body->setVerificationToken($verification_token);
                            }
    
                        } else {
                            $body->setSourceId($card_nonce);
                            $body->setVerificationToken($verification_token);
                        }
                    }

                    $amountMoney->setAmount((int) $amount);
                    $amountMoney->setCurrency(get_option('rg_gforms_currency'));
                    $body->setAmountMoney($amountMoney);
                    $body->setLocationId($location_id);                    
                    $body->setNote($note);                    
                    $body->setIdempotencyKey((string) $idempotencyKey);

                    //if authorize payment_only
                    if ( isset($settings['authorize_only']) && '1' == $settings['authorize_only']  ) {
                        $body->setAutocomplete(false);
                    }

                    if ( isset($order_id) && $order_id !='' && $order_amount == $amount ) {
                        $body->setOrderId($order_id);
                    } else {
                        update_option('payment_order_amount_error_form_id_'.$form_id.'_'.date("Y-m-d H:i:s"), 'order not created due to square order amount = ' . @$order_amount . ' and gravity form total = ' . $amount . ' not match.');
                    }     
                                                          
                    update_option('payment_request_body_form_id_'.$form_id.'_'.date("Y-m-d H:i:s"), $body);

                    $transaction = $payments_api->createPayment($body);
                    $transactionData = json_decode($transaction, true);

                    update_option('payment_request_transaction_create_form_id_'.$form_id.'_'.date("Y-m-d H:i:s"), $transactionData);
              
                    if (isset($transactionData['payment']['id'])) {
                        $transactionId = $transactionData['payment']['id'];

                        //if is recurring payment
                        if ('enabled'===$is_recurring) {
                            //calculate next payment 
                            $next_payment = new DateTime();
                            $next_payment->setTimestamp(current_time('timestamp'));
                            $next_payment->modify("+$subscription_interval $subscription_cycle");

                            self::$transaction_response = array(
                                'transaction_id' => $transactionId,
                                'amount' => $amount / 100,
                                'order_id' => $transactionData['payment']['order_id'],
                                'card_brand' => $transactionData['payment']['card_details']['card']['card_brand'],
                                'last_4' => $transactionData['payment']['card_details']['card']['last_4'],
                                'card_exp' => $transactionData['payment']['card_details']['card']['exp_month'] .'/'. $transactionData['payment']['card_details']['card']['exp_year'],
                                'card_name' => @$_POST['cardholder_name'],
                                'payment_mode' => @$mode,
                                'status' => $transactionData['payment']['status'],
                                'created_at' => $transactionData['payment']['created_at'],
                                'currency' => $transactionData['payment']['total_money']['currency'],
                                'customer_card_id' => $customerCardId,
                                'customer_id' => $square_customer_id,
                                'is_recurring' => $is_recurring,
                                'next_payment' => $next_payment->getTimestamp(),
                                'subscription_interval' => $subscription_interval,
                                'subscription_cycle' => $subscription_cycle,
                                'subscription_length' => $subscription_length
                            );
                        }

                        //if is simple payment
                        if ('disabled'===$is_recurring) {
                            self::$transaction_response = array(
                                'transaction_id' => $transactionId,
                                'amount' => $amount / 100,
                                'order_id' => $transactionData['payment']['order_id'],
                                'card_brand' => $transactionData['payment']['card_details']['card']['card_brand'],
                                'last_4' => $transactionData['payment']['card_details']['card']['last_4'],
                                'card_exp' => $transactionData['payment']['card_details']['card']['exp_month'] .'/'. $transactionData['payment']['card_details']['card']['exp_year'],
                                'card_name' => @$_POST['cardholder_name'],
                                'payment_mode' => @$mode,
                                'status' => $transactionData['payment']['status'],
                                'created_at' => $transactionData['payment']['created_at'],
                                'currency' => $transactionData['payment']['total_money']['currency'],
                                'customer_card_id' => @$customerCardId,
                                'customer_id' => @$square_customer_id,
                                'is_recurring' => $is_recurring,
                            );
                        }                        
                        return $validation_result;
                    }
                } else {

                    if ( $is_coupon ) { 
                        $validation_result['form'] = $form;
                    } else { 
                        //wp_die(';stop here!');
                        $validation_result['is_valid'] = false;
                        $form["fields"][$square_field_index]["failed_validation"] = true;
                        $form["fields"][$square_field_index]["validation_message"] = __('Either price is zero or no price field found.', 'gravity-forms-square');
                        $validation_result['form'] = $form;
                    }

                    return $validation_result;
                }
            } catch (\SquareConnect\ApiException $ex) {

                $errors = $ex->getResponseBody()->errors;
                update_option('payment_request_catch_error_form_id_'.$form_id.'_'.date("Y-m-d H:i:s"), $errors);
                $message = '';
                foreach ($errors as $error) {
                    $message = $error->detail;
                    if (isset($error->field)) {
                        $message = $error->field . ' - ' . $error->detail;
                    }
                }

                $validation_result['is_valid'] = false;
                $form["fields"][$square_field_index]["failed_validation"] = true;
                $form["fields"][$square_field_index]["validation_message"] = $message;
            }
        }

        $validation_result['form'] = $form;
        return $validation_result;
    }

    function refresh_token($wpep_live_token_details)
    {
        if (!empty($wpep_live_token_details->expires_at)) {
            // strtotime($wpep_live_token_details->expires_at)-300 <= time()
            $wpep_live_token_details->save_db = false;
            
            if (strtotime($wpep_live_token_details->expires_at)-300 <= time()) {
                //refresh token
                    $oauth_connect_url = WOOSQU_GF_CONNECTURL;
                    $redirect_url = add_query_arg(
                        array(
                            'app_name'    => WOOSQU_GF_APPNAME,
                            'plug'    => WOOSQU_GF_PLUGIN_NAME,
                        ),
                        admin_url('admin.php')
                    );
                    $head = array(
                                'Authorization' => 'Bearer '.$wpep_live_token_details->access_token,
                                'content-type' => 'application/json'
                            );
                    if (!empty($wpep_live_token_details->refresh_token)) {
                        $head['refresh_token'] = $wpep_live_token_details->refresh_token;
                    }
                    $redirect_url = wp_nonce_url($redirect_url, 'connect_woosquare', 'wc_woosquare_token_nonce');
                    $site_url = ( urlencode($redirect_url) );
                    $args_renew = array(
                        'body' => array(
                            'header' => $head,
                            'action' => 'renew_token',
                            'foradmin' => 'true',
                            'site_url'    => $site_url,
                        ),
                        'timeout' => 45,
                    );
                    $oauth_response = wp_remote_post($oauth_connect_url, $args_renew);
                    $send_email_notification = $wpep_live_token_details->send_email_notification;
                    $wpep_live_token_details = json_decode(wp_remote_retrieve_body($oauth_response));
                    
                    if ($send_email_notification) {
                        if (!empty($wpep_live_token_details->access_token)) {
                            $this->email_shoot_sqgf('success', $wpep_live_token_details, WOOSQU_GF_PLUGIN_NAME, WOOSQU_GF_PLUGIN_AUTHOR);
                        } else {
                            $this->email_shoot_sqgf('failed', $wpep_live_token_details, WOOSQU_GF_PLUGIN_NAME, WOOSQU_GF_PLUGIN_AUTHOR);
                        }
                    }
                        
                    $wpep_live_token_details->save_db = true;
            }
        }
        return $wpep_live_token_details;
    }
    
    
    public function email_shoot_sqgf($stat, $decoded_oauth_response, $pluginname, $pluginauthor)
    {
        
                    echo $to = get_bloginfo('admin_email');
                    $headers = array('Content-Type: text/html; charset=UTF-8');
        if ($stat == 'success') {
            $date = date_create($decoded_oauth_response->expires_at);
            $expires_at = date_format($date, "M d, Y H:i:s");
            $subject = 'Your '.$pluginname.' oauth token has been renewed!';
            $message = 'Hi There! <br/><br/> Your '.$pluginname.' oauth token has been renewed successfully. <br/> Expires at '.$expires_at.' 
						<br/> System will renew it automatically once this expiry date is reached.<br/><br/> Thank you <br/>'.$pluginauthor;
            wp_mail($to, $subject, $message, $headers);
        } elseif ($stat == 'failed') {
            $subject = 'Your Access token has expired.';
            $message = 'Hi there! <br/><br/>  
							Your Access token could not be renewed automatically due to "'.ucfirst($decoded_oauth_response->message).'". 
							Please renew your Access token by reconnecting your square account, 
							Still if you have any issues visit our support page with respective error message.<br/><br/> Thank you <br/>'.$pluginauthor;
            wp_mail($to, $subject, $message, $headers);
        } elseif ($stat == 'email_notify') {
            $subject = 'IMPORTANT NOTICE FOR '.$decoded_oauth_response['Name'];
            $message = 'Dear Customer, <br/><br/>  
							This is very important email kindly read it till the end, API Experts just released a major update which covers the fix of OAuth disconnect issue of plugin. At this point you MUST disconnect and then reconnect your Square account with plugin as soon as possible. once you reconnect the Square account with plugin all new changes of latest update will implement automatically.

							Please follow this <a href="https://apiexperts.io/woosquare-plus-documentation/#11-faqs" target="_blank" >helpful Documentation </a> to see how you Disconnect & reconnect Square account with plugin. 

							If you have any question regarding email please feel free to contact us at support@wpexperts.io    
							
							<br/><br/> Thank you <br/>'.$pluginauthor;
                wp_mail($to, $subject, $message, $headers);
        }
    }
    
    
    
    public function payment_scripts($form) {
        //echo YEAR_IN_SECONDS ;

        //wp_die('test in simple');
        
        $is_square = false;
        $is_recurring = 'disabled';
        $card_num = __('Card Number', 'gravity-forms-square');
        $card_exp = __('MM/YY', 'gravity-forms-square');
        $card_cvv = __('CVV', 'gravity-forms-square');
        $card_zip = __('ZIP', 'gravity-forms-square');
        $card_name = __('Cardholder name', 'gravity-forms-square');

        foreach ($form['fields'] as $field) {
            
            if ($field->type == 'square') {
                $is_square = true;

                if ($field->isrecurring=='1') {
                    $is_recurring = 'enabled';
                }

                //other payments
                if ( isset($field->enable_applepay) && $field->enable_applepay=='1' && $is_recurring === 'disabled') {
                    $gfsqs_applepay = 'enabled';
                } else {
                    $gfsqs_applepay = 'disabled';
                }
                
                if ( isset($field->enable_googlepay) && $field->enable_googlepay=='1' && $is_recurring === 'disabled') {
                    $gfsqs_googlepay = 'enabled';
                } else {
                    $gfsqs_googlepay = 'disabled';
                }
                
                /*if ( isset($field->enable_masterpass) && $field->enable_masterpass=='1' && $is_recurring === 'disabled') {
                    $gfsqs_masterpass = 'enabled';
                } else {
                    $gfsqs_masterpass = 'disabled';
                }*/
                
                if ( isset($field->enable_giftcard) && $field->enable_giftcard=='1' && $is_recurring === 'disabled') {
                    $gfsqs_giftcard = 'enabled';
                } else {
                    $gfsqs_giftcard = 'disabled';
                }

                if (trim($field->card_num)!='') {
                    $card_num = $field->card_num;
                }
                if (trim($field->card_exp)!='') {
                    $card_exp = $field->card_exp;
                }
                if (trim($field->card_cvv)!='') {
                    $card_cvv = $field->card_cvv;
                }
                if (trim($field->card_zip)!='') {
                    $card_zip = $field->card_zip;
                }
                if (trim($field->card_name)!='') {
                    $card_name = $field->card_name;
                }

                break;
            }

            
        }

        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            if (isset($user_id)) {
                $email = $current_user->user_email;
                $first_name = get_user_meta($user_id, 'first_name', true);
                $last_name = get_user_meta($user_id, 'last_name', true);
            } else {
                $email = '';
                $first_name = '';
                $last_name = '';
            }
        } else {
            $email = '';
            $first_name = '';
            $last_name = '';
        }

        $form_id = $form['id'];

        //getting card of file settings
        $cof_settings = get_option('gf_square_cof_settings_' . $form_id);

        if ($cof_settings && 'enabled'==$cof_settings['square_cof_mode']) {
            $square_cof_mode = 'enabled';
        } else {
            $square_cof_mode = 'disabled';
        }

        //get form square settings
        $settings = get_option('gf_square_settings_' . $form_id);
        $token = null;
        $location_id = null;
        if (isset($settings['gf_squaree_mode']) && $settings['gf_squaree_mode'] == 'test') {
            $application_id = $settings['square_test_appid'];
            $location_id = $settings['square_test_locationid'];
        } else {
            $application_id = isset($settings['square_appid']) ? $settings['square_appid'] : '';
            $location_id = isset($settings['square_locationid']) ? $settings['square_locationid'] : '';
        }

        if ($is_square && $application_id && $location_id) {
            wp_enqueue_style('gfsq-style', SQGF_PLUGIN_URL . 'assets/style/style.css', '', '2.2t='. strtotime('now'));

            if (isset($settings['gf_squaree_mode']) && $settings['gf_squaree_mode'] == 'test') {
                wp_register_script('gfsq-paymentform', 'https://js.squareupsandbox.com/v2/paymentform', '', '100.0.0', true);
                wp_enqueue_script('gfsq-paymentform');
                 
            } else {
                wp_register_script('gfsq-paymentform', 'https://js.squareup.com/v2/paymentform', '', '', true);                
                wp_enqueue_script('gfsq-paymentform');
            }

            wp_register_script('gfsq-checkout', SQGF_PLUGIN_URL . 'assets/js/scripts.js', array(), '2.2&t='. strtotime('now'), true);
            wp_localize_script('gfsq-checkout', 'gfsqs', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'application_id' => $application_id,
                'form_id' => $form_id,
                'currency_charge' => get_option('rg_gforms_currency'),
                'location_id' => $location_id,
                'placeholder_card_number' => __( $card_num, 'gravity-forms-square'),
                'placeholder_card_expiration' => __( $card_exp, 'gravity-forms-square'),
                'placeholder_card_cvv' => __( $card_cvv, 'gravity-forms-square'),
                'placeholder_card_postal_code' => __( $card_zip, 'gravity-forms-square'),
                'payment_form_input_styles' => esc_js($this->get_input_styles()),
                'square_cof_mode' => $square_cof_mode,
                'fname' => $first_name,
                'lname' => $last_name,
                'email' => $email,
                'applepay' => $gfsqs_applepay,
                'googlepay' => $gfsqs_googlepay,
                /*'masterpass' => $gfsqs_masterpass,*/
                'giftcard' => $gfsqs_giftcard,
                'cancel_text' => __('Cancel', 'gravity-forms-square'),
                'delete_text' => __('Delete', 'gravity-forms-square'),
            ));
            ?>
            <script>
                jQuery(function(){
                    jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="application_id" value="<?=$application_id;?>" />');
                    jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="location_id" value="<?=$location_id;?>" />');
                    jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="currency_charge" value="<?=get_option('rg_gforms_currency');?>" />');
                    jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="square_cof_mode" value="<?=$square_cof_mode;?>" />');
                    jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="form_id" value="<?=$form_id;?>" />');
                    jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="is_recurring" value="<?=$is_recurring;?>" />');
                    jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="is_googlepay" value="<?=$gfsqs_googlepay;?>" />');
                    jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="is_applepay" value="<?=$gfsqs_applepay;?>" />');
                    /* jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="is_masterpass" value="<?=$gfsqs_masterpass;?>" />'); */
                    jQuery('form#gform_<?=$form_id?>').append('<input type="hidden" class="is_giftcard" value="<?=$gfsqs_giftcard;?>" />');
                });
            </script>
            <?php

            wp_enqueue_script('gfsq-checkout');
        }
    }
    
    public function admin_style_gfsqu()
    {
        wp_enqueue_style('gfsq-style-admin', SQGF_PLUGIN_URL . 'assets/style/admin-style.css', array(), '2.6&' . mktime(date('dmY')));
        wp_enqueue_script( 'hide_notice_script', SQGF_PLUGIN_URL . 'assets/js/hide_notice.js', array(), '2.6&' . mktime(date('dmY')), true );
    }

    private function get_input_styles()
    {
        $styles = array(
            array(
                'fontSize' => '14px',
                'padding' => '12px 0',
                'backgroundColor' => 'transparent',
                'placeholderColor' => '#777',
                'fontWeight' => 'normal'
            )
        );

        return wp_json_encode($styles);
    }

    public function get_form_editor_field_title()
    {
        return esc_attr__('Square CC', 'gravityforms');
    }

    public function get_form_editor_button()
    {
        return array(
            'group' => 'pricing_fields',
            'text' => $this->get_form_editor_field_title(),
        );
    }

    function get_form_editor_field_settings()
    {
        return array(
            'conditional_logic_field_setting',
            'prepopulate_field_setting',
            'error_message_setting',
            'label_setting',
            'label_placement_setting',
            'admin_label_setting',
            'size_setting',
            'rules_setting',
            'visibility_setting',
            'duplicate_setting',
            'default_value_setting',
            'placeholder_setting',
            'description_setting',
            'css_class_setting',
        );
    }

    public function is_conditional_logic_supported()
    {
        return true;
    }
    public function check_form_api_keys($form_id)
    {
        $settings = get_option('gf_square_settings_' . $form_id);
        $check=false;
        if (isset($settings['gf_squaree_mode']) && $settings['gf_squaree_mode']=='test' && !empty($settings['square_test_appid']) && !empty($settings['square_test_locationid']) && !empty($settings['square_test_token'])) {
            $check=true;
        } elseif (isset($settings['gf_squaree_mode']) && $settings['gf_squaree_mode']=='live' && !empty($settings['square_appid']) && !empty($settings['square_locationid']) && !empty($settings['square_token'])) {
            $check=true;
        } else {
            $check=false;
        }
        return $check;
    }

    public function my_cof_delete_card() {
        
        if ( isset($_POST['card_id']) && isset($_POST['form_id']) && $_POST['form_id']!='' && $_POST['card_id']!='' ) {
            
            $card_id = $this->md5_decrypt($_POST['card_id'], 'x12admin');
            $form_id = $_POST['form_id'];
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;


            $token = null;
            $location_id = null;
            $api_client = null;
            $api_config = null;
            $gf_sqr_settings = get_option('gf_square_settings_' . $form_id);


            if (get_user_meta($user_id, 'gf_square_customer_id', true)) {
                $square_customer_id = get_user_meta($user_id, 'gf_square_customer_id', true);
            } else {
                $square_customer_id = 0;
            }

            if (isset($gf_sqr_settings['gf_squaree_mode']) && $gf_sqr_settings['gf_squaree_mode'] == 'test') {
                $token = $gf_sqr_settings['square_test_token'];
                $host = "https://connect.squareupsandbox.com";
            } else {
                $token = $gf_sqr_settings['square_token'];
                
                if (!is_object($gf_sqr_settings['square_auth_request'])) {
                    $gf_sqr_settings['square_auth_request'] = (object) $gf_sqr_settings['square_auth_request'];
                }

                if (!empty($gf_sqr_settings['square_auth_request']->access_token)) {
                    $gf_sqr_settings['square_auth_request']->send_email_notification = (empty($gf_sqr_settings['send_email_notification'])) ? 0 : $gf_sqr_settings['send_email_notification'] ;
                    $square_auth_request = $this->refresh_token($gf_sqr_settings['square_auth_request']);
                    $token = $square_auth_request->access_token;
                    if ($square_auth_request->save_db == true) {
                        if (!empty($token)) {
                            $gf_sqr_settings['square_token'] = $token;
                            $gf_sqr_settings['square_auth_request'] = $square_auth_request;
                            update_option('gf_square_settings_'.$form_id, $gf_sqr_settings);
                        }
                    }
                }

                $host = "https://connect.squareup.com";
            }

            //creating instance for customer API
            $api_config = new \SquareConnect\Configuration();
            $api_config->setHost($host);
            $api_config->setAccessToken($token);
            $api_client = new \SquareConnect\ApiClient($api_config);        
            $customer_api = new \SquareConnect\Api\CustomersApi($api_client);

            //print_r($customer_api); exit;
			$url = $host."/v2/cards/{$card_id}/disable";

			if(!empty($card_id)){
				$headers = array(
								'Accept' => 'application/json',
								'Authorization' => 'Bearer '.$token,
								'Content-Type' => 'application/json',
								'Cache-Control' => 'no-cache'
							);

				$result = json_decode(wp_remote_retrieve_body(wp_remote_post($url, array(
								'method' => 'POST',
								'headers' => $headers,
								'httpversion' => '1.0',
								'sslverify' => false
								
								)))
							);
				
				if($result->card->id){
					return;
				}else{
					// $errors = $ex->getResponseBody()->errors;
					$errors = isset($result->get_error_message()->errors);
					$errors['status'] = 'error';
					echo json_encode($errors);
					print_r($errors);
				}
			}

            // try {
                // $apiResponse = $customer_api->deleteCustomerCard($square_customer_id, $card_id);
                // $apiResponse['status'] = 'success';
                // echo json_encode($apiResponse);
            // } catch (Exception $ex) {
                // $errors = $ex->getResponseBody()->errors;
                // $errors['status'] = 'error';
                // echo json_encode($errors);
                // print_r($errors);
            // }

            wp_die();
        }
    }

    Public function my_cof_delete_card_confirmation_box () {
        require_once( SQGF_PLUGIN_PATH . 'includes/views/confirmation_box.php' );
    }

    public function get_field_input($form, $value = '', $entry = null)
    {

        $form_id = $form['id'];
        $fields = $form['fields'];
        $sep_text = __('OR', 'gravity-forms-square');
        $card_num = __('Card Number', 'gravity-forms-square');
        $card_exp = __('MM/YY', 'gravity-forms-square');
        $card_cvv = __('CVV', 'gravity-forms-square');
        $card_zip = __('ZIP', 'gravity-forms-square');
        $card_name = __('Cardholder name', 'gravity-forms-square');


        foreach ( $fields as $field) {
                        
            if ($field->type == 'square') {

                //other payments
                if ( isset($field->enable_applepay) && $field->enable_applepay=='1') {
                    $gfsqs_applepay = 'enabled';
                } else {
                    $gfsqs_applepay = 'disabled';
                }
                
                if ( isset($field->enable_googlepay) && $field->enable_googlepay=='1') {
                    $gfsqs_googlepay = 'enabled';
                } else {
                    $gfsqs_googlepay = 'disabled';
                }
                
                /*if ( isset($field->enable_masterpass) && $field->enable_masterpass=='1') {
                    $gfsqs_masterpass = 'enabled';
                } else {
                    $gfsqs_masterpass = 'disabled';
                }*/
                
                if ( isset($field->enable_giftcard) && $field->enable_giftcard=='1') {
                    $gfsqs_giftcard = 'enabled';
                } else {
                    $gfsqs_giftcard = 'disabled';
                }
                
                if (trim($field->card_num)!='') {
                    $card_num = $field->card_num;
                }
                if (trim($field->card_exp)!='') {
                    $card_exp = $field->card_exp;
                }
                if (trim($field->card_cvv)!='') {
                    $card_cvv = $field->card_cvv;
                }
                if (trim($field->card_zip)!='') {
                    $card_zip = $field->card_zip;
                }
                if (trim($field->card_name)!='') {
                    $card_name = $field->card_name;
                }

                if($field->isrecurring==1) {
                    $is_recurring = 'enabled';
                    $show_save_card_checkbox = false;
                } else {
                    $is_recurring = 'disabled';
                    $show_save_card_checkbox = true;
                }                

                break;
            }

        }
        
        $settings = get_option('gf_square_cof_settings_' . $form_id, false);
        
        if (is_user_logged_in() && $settings && !empty($settings['square_cof_save_card_text']) && 'enabled'==$settings['square_cof_mode']) {
            
			$square_cof_delete_card = $settings['square_cof_delete_card'];
			
            if ($show_save_card_checkbox) {
                $save_card_for_future_opt = '<div class="gf-square-field">
                                            <input type="checkbox" id="save_card_for_future_' . $form_id . '" name="save_card_for_future" value="yes">
                                            <label for="save_card_for_future_' . $form_id . '">' . __(esc_html($settings['square_cof_save_card_text']), 'gravity-forms-square') . '</label>                                            
                                        </div>';
            } else {
                $save_card_for_future_opt = '<div class="gf-square-field">
                                            <input type="hidden" id="save_card_for_future_' . $form_id . '" name="save_card_for_future" value="yes">                                            
                                        </div>';
            }

            if (!is_admin()) {
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;

                $token = null;
                $location_id = null;
                $api_client = null;
                $api_config = null;
                $gf_sqr_settings = get_option('gf_square_settings_' . $form_id);

                if (get_user_meta($user_id, 'gf_square_customer_id', true)) {
                    $square_customer_id = get_user_meta($user_id, 'gf_square_customer_id', true);
                } else {
                    $square_customer_id = 0;
                }

                if (isset($gf_sqr_settings['gf_squaree_mode']) && $gf_sqr_settings['gf_squaree_mode'] == 'test') {
                    $token = $gf_sqr_settings['square_test_token'];
                    $host = "https://connect.squareupsandbox.com";
                } else {
                    $token = $gf_sqr_settings['square_token'];
                    
                    if (!is_object($gf_sqr_settings['square_auth_request'])) {
                        $gf_sqr_settings['square_auth_request'] = (object) $gf_sqr_settings['square_auth_request'];
                    }

                    if (!empty($gf_sqr_settings['square_auth_request']->access_token)) {
                        $gf_sqr_settings['square_auth_request']->send_email_notification = (empty($gf_sqr_settings['send_email_notification'])) ? 0 : $gf_sqr_settings['send_email_notification'] ;
                        $square_auth_request = $this->refresh_token($gf_sqr_settings['square_auth_request']);
                        $token = $square_auth_request->access_token;
                        if ($square_auth_request->save_db == true) {
                            if (!empty($token)) {
                                $gf_sqr_settings['square_token'] = $token;
                                $gf_sqr_settings['square_auth_request'] = $square_auth_request;
                                update_option('gf_square_settings_'.$form_id, $gf_sqr_settings);
                            }
                        }
                    }

                    $host = "https://connect.squareup.com";
                }

                //creating instance for customer API
                $api_config = new \SquareConnect\Configuration();
                $api_config->setHost($host);
                $api_config->setAccessToken($token);
                $api_client = new \SquareConnect\ApiClient($api_config);        
                $customer_api = new \SquareConnect\Api\CustomersApi($api_client);
                $is_customer_found = true;
                try {
                    if (isset($square_customer_id) && !empty($square_customer_id)) {
                        $square_customer = $customer_api->retrieveCustomer($square_customer_id);
                        $square_customer = json_decode($square_customer, true);
    
                        if (isset($square_customer['customer']['cards'])) {
                            $square_customer_cards = $square_customer['customer']['cards'];
                            $cards_output = '';
                            foreach ($square_customer_cards as $key => $card) {

                                $card_id = $this->md5_encrypt($card['id'], 'x12admin');

                                if ( 'enabled'===$square_cof_delete_card ) {
                                    $delete_card = '<div class="gfsqs-trigger-btn"><span class="">Remove</span></div>';
                                }
                                
                                $my_card =    '<div class="credit-card ' . strtolower($card['card_brand']) . ' selectable" data-card-id="' . $card_id . '">
                                                    <input type="radio" name="gf-square-stored-cards" value="' . $card_id . '">
                                                    <div class="credit-card-name">' . $card['cardholder_name'] . '</div>
                                                    <div class="credit-card-last4">' . $card['last_4'] . '</div>
                                                    <div class="credit-card-expiry">' . $card['exp_month'] . '/' . $card['exp_year'] . '</div>
                                                    '. @$delete_card .'
                                                </div>';
    
                                $cards_output .= $my_card;
                            }
                        
                            $radio_for_square_saved_cards = '<div style="clear:both;height:15px;">&nbsp;</div><div class="saved_card_wrapper"><label class="gfield_label" for="toggler_1_' . $form_id . '" >' . __('Stored payment cards', 'gravity-forms-square') . '</label><input type="radio" id="toggler_1_' . $form_id . '" class="gf_square_payment_toggler" data-id="existing_card_paymment" name="gf_square_payment_toggler" value="gf_sqquare_saved_card_container_' . $form_id . '">';
                            $radio_for_square_saved_cards .=    '<div class="ginput_container gf_sqquare_saved_card_container" id="gf_sqquare_saved_card_container_' . $form_id . '">
                                                                <div class="gf-stored-cards">' . $cards_output . '</div>                                                
                                                            </div></div>';
                            
                            $radio_for_square_rendered_fields = '<input type="radio" id="toggler_2_' . $form_id . '" class="gf_square_payment_toggler" data-id="new_card_paymment" name="gf_square_payment_toggler" value="gf_sqquare_container_' . $form_id . '" >';
                        } else {
                            $radio_for_square_rendered_fields = '<input type="radio" id="toggler_2_' . $form_id . '" class="gf_square_payment_toggler" data-id="new_card_paymment" name="gf_square_payment_toggler" value="gf_sqquare_container_' . $form_id . '" >';
                        }
                    } else {
                        $radio_for_square_rendered_fields = '<input type="radio" id="toggler_2_' . $form_id . '" class="gf_square_payment_toggler" data-id="new_card_paymment" name="gf_square_payment_toggler" value="gf_sqquare_container_' . $form_id . '" >';
                    }
                    //wp_die(';stop');
                } catch (Exception $ex) { 
                    //customer not exist
                    $errors = $ex->getResponseBody()->errors;
                    
                    if( 'NOT_FOUND' == $errors[0]->code ) {
                        $is_customer_found = false;
                    }
                }

                if (!$is_customer_found) {
                    $radio_for_square_rendered_fields = '<input type="radio" id="toggler_2_' . $form_id . '" class="gf_square_payment_toggler" data-id="new_card_paymment" name="gf_square_payment_toggler" value="gf_sqquare_container_' . $form_id . '" >';
                }

            } else {
                //$hide_class = 'style="display:none!important"';
            }
        }

        if ( !is_admin() ) { //only show on frontend not for admin
            
            if ( 'enabled'===$gfsqs_googlepay && 'disabled'===$is_recurring ) {
                $other_payment_seperator = '<div style="height:20px;clear:both;">&nbsp;</div><span class="gfsqs-sep">' . $sep_text . '</span><div class="digital-message"></div>';
                $googlepay_payments = '<div style="height:20px;clear:both;">&nbsp;</div><label><input type="checkbox" data-type="google" name="gfsqs-other-payments" value="gfsq-google-pay-' . $form_id . '-wrapper"> ' . __('Google Pay', 'gravity-forms-square') . '</label><div class="gfsqs-digital-pay-wrapper" id="gfsq-google-pay-' . $form_id . '-wrapper"><button id="gfsq-google-pay-' . $form_id . '" class="button-google-pay gfsqs-digital-pay"></button></div>';
            }

            if ( 'enabled'===$gfsqs_applepay && 'disabled'===$is_recurring ) {
                $other_payment_seperator = '<div style="height:20px;clear:both;">&nbsp;</div><span class="gfsqs-sep">' . $sep_text . '</span><div class="digital-message"></div>';
                $applepay_payments = '<div style="height:20px;clear:both;">&nbsp;</div><label><input type="checkbox" data-type="apple" name="gfsqs-other-payments" value="gfsq-apple-pay-' . $form_id . '-wrapper"> ' . __('Apple Pay', 'gravity-forms-square') . '</label><div class="gfsqs-digital-pay-wrapper" id="gfsq-apple-pay-' . $form_id . '-wrapper"><button id="gfsq-apple-pay-' . $form_id . '" class="apple-pay-button apple-pay-button-white gfsqs-digital-pay"></button></div>';
            }

            /*if ( 'enabled'===$gfsqs_masterpass && 'disabled'===$is_recurring ) {
                $other_payment_seperator = '<div style="height:20px;clear:both;">&nbsp;</div><span class="gfsqs-sep">' . $sep_text . '</span><div class="digital-message"></div>';
                $masterpass_payments = '<div style="height:20px;clear:both;">&nbsp;</div><label><input type="checkbox" data-type="masterpass" name="gfsqs-other-payments" value="gfsq-masterpass-' . $form_id . '-wrapper"> ' . __('Master Pass', 'gravity-forms-square') . '</label><div class="gfsqs-digital-pay-wrapper" id="gfsq-masterpass-' . $form_id . '-wrapper"><button id="gfsq-masterpass-' . $form_id . '" class="button-masterpass gfsqs-digital-pay"></button></div>';
            }*/

            if ( 'enabled'===$gfsqs_giftcard && 'disabled'===$is_recurring ) {
                $other_payment_seperator = '<div style="height:20px;clear:both;">&nbsp;</div><span class="gfsqs-sep">' . $sep_text . '</span><div class="digital-message"></div>';
                $giftcard_payments =    '<div style="height:20px;clear:both;">&nbsp;</div>
                                        <label>
                                            <input type="checkbox" data-type="giftcard" class="gift-card-radio" name="gfsqs-other-payments" value="gfsq-giftcard-' . $form_id . '-wrapper"> ' . __('Gift Card', 'gravity-forms-square') . 
                                        '</label>    
                                        <div class="gfsqs-digital-pay-wrapper" id="gfsq-giftcard-' . $form_id . '-wrapper">
                                            <div class="single-element-configuration">
                                                <div class="element-toLeft">
                                                    <div class="gfsq-ccard-container">
                                                        <div class="gfsq-card">
                                                            <div class="gfsq-front"></div>
                                                        </div>
                                                    </div>
                                                    <div id="gfsq-giftcard-' . $form_id . '"></div>
                                                </div>
                                            </div>                                            
                                        </div>';
            }

        }
 
        if ($this->check_form_api_keys($form_id)==false) {
            return '<p>' . __('Please add api keys', 'gravity-forms-square') . '</p>';
        } else {
            $input = '<div style="height:20px;clear:both;">&nbsp;</div><div class="new_card_wrapper" '. @$hide_class .'><label style="clear:both;display:inline-block;line-height:1.3;font-weight:700;font-size: inherit;" class="gfsr_gfield_label" for="toggler_2_' . $form_id . '">' . __('Add new card', 'gravity-forms-square') . '</label>'. @$radio_for_square_rendered_fields .'<div class="ginput_container gf_sqquare_container " id="gf_sqquare_container_' . $form_id . '">  
                <div class="messages"></div>
                <div class="single-element-configuration">
                    <div class="element-toLeft">
                        <div class="gfsq-ccard-container">
                            <div class="gfsq-card">
                                <div class="gfsq-front"></div>
                                <div class="gfsq-back"></div>
                            </div>
                        </div>
                        <div id="gfsq-card-number-' . $form_id . '"><input type="text" class="medium" Placeholder="' . $card_num . '"></div>
                    </div>
                    <div class="element-toRight">
                        <div id="gfsq-expiration-date-' . $form_id . '"><input type="text" class="medium" Placeholder="' . $card_exp . '"></div>
                        <div id="gfsq-cvv-' . $form_id . '"><input type="text" class="medium" Placeholder="' . $card_cvv . '"></div>
                        <div id="gfsq-postal-code-' . $form_id . '"><input type="text" class="medium" Placeholder="' . $card_zip . '"></div>
                    </div>
                </div>
                <div class="cardholder_name">
                    <input type="text" id="cardholder_name-' . $form_id . '" name="cardholder_name" Placeholder="' . $card_name . '" />
                </div>' .                
                @$save_card_for_future_opt
            . '</div></div>';            

            @$radio_for_square_saved_cards .= $input . @$other_payment_seperator . @$googlepay_payments . @$applepay_payments . @$masterpass_payments . @$giftcard_payments;

            return $radio_for_square_saved_cards;
        }
    }
    public function api_keys_not_found_for_form() {
        if (isset($_GET['page']) && $_GET['page']=='gf_entries' && isset($_GET['id']) && !empty($_GET['id'])
           || isset($_GET['page']) && $_GET['page']=='gf_edit_forms' && isset($_GET['id']) && !empty($_GET['id'])
            ) {
            if ($this->check_form_api_keys($_GET['id'])==false  && @$_GET['subview']=='square_settings_page') {
                $class = 'notice notice-error';
                $message=  __('Please add Square API keys', 'gravity-forms-square');
                printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
            }
        }
    }
}
