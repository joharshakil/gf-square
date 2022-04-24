<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

if (!class_exists('GFSR_Renew')) {

    class GFSR_Renew {

        /**
         * Class Constructor
         */
        public function __construct() {
            /*global $gravity_forms_square;
            if ( gravity_forms_square()->is__premium_only() ) {
            if ( gravity_forms_square()->can_use_premium_code() ) {
                //subscription renew cronjob
                
                // add_action('admin_init', array($this, 'gfsr_renew_cronjob_func'));

                }
            }*/

            add_action('gfsr_renew_cronjob', array($this, 'gfsr_renew_cronjob_func'));

        }

        public function gfsr_renew_cronjob_func() {
            
            

            $search_criteria = array(
                'field_filters' => array(
                    /*array(
                        'key' => 'payment_gateway',
                        'value' => 'square',
                    ),*/
                    /*array(
                        'key' => 'payment_gateway',
                        'value' => 'squarerecurring', //array('squarerecurring', 'square'),
                        'compare' => '=',
                    ),*/
                    array(
                        'key' => 'subscription_status',
                        'value' => '1',
                    )
                )
            );

            $paging = array('offset' => 0, 'page_size' => 100);
            $entries = GFAPI::get_entries(0, $search_criteria, null, $paging);            
			
            foreach ($entries as $entry) {
                $entry_id = $entry['id'];
				$next_payment = gform_get_meta($entry_id, 'next_payment');
                if ($next_payment) {
                    $next_payment = date('Y-m-d', $next_payment);
                    $current_date = date('Y-m-d', current_time('timestamp'));
                    if ($current_date >= $next_payment) {
                        
                        $form_id = $entry['form_id'];
                        $amount = $entry['payment_amount'];
                        $customer_id = gform_get_meta($entry_id, 'customer_id');
                        $customer_card_id = gform_get_meta($entry_id, 'customer_card_id');
                        $existing_transaction_id = gform_get_meta( $entry_id, 'transaction_id' );
						
                        if ($form_id && $amount && $customer_id && $customer_card_id) {
                            //get form square settings
                            $settings = get_option('gf_square_settings_' . $form_id);
                            $token = null;
                            $location_id = null;
                            $mode = isset($settings['gf_squaree_mode']) ? $settings['gf_squaree_mode'] : 'test';
							
                            if (isset($settings['gf_squaree_mode']) && $settings['gf_squaree_mode'] == 'test') {
                                $token = $settings['square_test_token'];
                                $location_id = $settings['square_test_locationid'];

                                $api_config = new \SquareConnect\Configuration();
                                $api_config->setHost("https://connect.squareupsandbox.com");
                                $api_config->setAccessToken($token);
                                $api_client = new \SquareConnect\ApiClient($api_config);

                            } else {

                                $token = $settings['square_token'];
							
								if (!is_object(@$settings['square_auth_request'])) {
									@$settings['square_auth_request'] = (object) $settings['square_auth_request'];
								}
								if(!empty($settings['square_auth_request']->access_token)){
									
									$square_auth_request = $this->refresh_token($settings['square_auth_request']);
									$token = $square_auth_request->access_token;
									if($square_auth_request->save_db == true){
										$settings['square_token'] = $token; 
										$settings['square_auth_request'] = $square_auth_request;
										update_option('gf_square_settings_'.$form_id, $settings);
									}
								}
								$location_id = $settings['square_locationid'];
								$appid = $settings['square_appid'];

                                $api_config = new \SquareConnect\Configuration();
                                $api_config->setHost("https://connect.squareup.com");
                                $api_config->setAccessToken($token);
                                $api_client = new \SquareConnect\ApiClient($api_config);

                            }

                            if ($token && $location_id) {

                                try {

                                    $subscription_interval = gform_get_meta($entry_id, 'subscription_interval');
                                    $subscription_cycle = gform_get_meta($entry_id, 'subscription_cycle');
                                    $subscription_length = gform_get_meta($entry_id, 'subscription_length');
                                    $subscription_payments_count = gform_get_meta($entry_id, 'subscription_payments_count');

                                    if ($subscription_payments_count != $subscription_length) {


                                        $amount = round($amount, 2) * 100;
                                        $idempotencyKey = time();

                                        $payments_api = new \SquareConnect\Api\PaymentsApi($api_client);
                                        $body = new \SquareConnect\Model\CreatePaymentRequest();
                                        $amountMoney = new \SquareConnect\Model\Money();
                                        $amountMoney->setAmount($amount);
                                        $amountMoney->setCurrency(get_option('rg_gforms_currency'));
                                        if ('test'===$mode) {
                                            $body->setSourceId("ccof:customer-card-id-ok");
                                        } else {
                                            $body->setSourceId($customer_card_id);
                                        }
                                        $body->setAmountMoney($amountMoney);
                                        $body->setLocationId($location_id);
                                        $body->setNote("This is a reccurring payment of transaction ID: ". $existing_transaction_id );
                                        $body->setCustomerId($customer_id);
                                        $body->setIdempotencyKey((string) $idempotencyKey);
                                        $body->setReferenceId((string) $idempotencyKey);
                                        $transaction = $payments_api->createPayment($body);


                                        $transactionData = json_decode($transaction, true);
                                        if (isset($transactionData['payment']['id'])) {
                                            $transactionId = $transactionData['payment']['id'];

                                            //insert transaction
                                            global $wpdb;
                                            $gfsr_transactions_table = $wpdb->prefix . 'gfsr_transactions';
                                            $data = array(
                                                'entry_id' => $entry_id,
                                                'created_at' => date('Y-m-d', current_time('timestamp')),
                                                'transaction_id' => $transactionId
                                            );
                                            $format = array('%d', '%s', '%s');
                                            $wpdb->insert($gfsr_transactions_table, $data, $format);

                                            //calculate next payment 
                                            $next_payment = new DateTime();
                                            $next_payment->setTimestamp(current_time('timestamp'));
                                            $next_payment->modify("+$subscription_interval $subscription_cycle");

                                            gform_update_meta($entry_id, 'next_payment', $next_payment->getTimestamp());
                                            gform_update_meta($entry_id, 'subscription_payments_count', $subscription_payments_count + 1);
                                        }
                                    } else {
                                        gform_update_meta($entry_id, 'subscription_status', 0);
                                    }
                                } catch (Exception $ex) {
                                    gform_update_meta($entry_id, 'subscription_status', 0);
                                }
                            }
                        } else {
                            gform_update_meta($entry_id, 'subscription_status', 0);
                        }
                    }
                } else {
                    gform_update_meta($entry_id, 'subscription_status', 0);
                }
            }            
        }
		
		
	
		public function refresh_token($wpep_live_token_details){
			
			if(!empty($wpep_live_token_details->expires_at)){
				// strtotime($wpep_live_token_details->expires_at)-500000 <= time()
				$wpep_live_token_details->save_db = false;
				
				if (strtotime($wpep_live_token_details->expires_at)-500000 <= time()) {
					//refresh token
						$oauth_connect_url = WOOSQU_GF_REC_CONNECTURL;
						$redirect_url = add_query_arg(
							array(
								'app_name'    => WOOSQU_GF_REC_APPNAME,
								'plug'    => WOOSQU_GF_REC_PLUGIN_NAME,
							),
							admin_url( 'admin.php' )
						);
						
						$redirect_url = wp_nonce_url( $redirect_url, 'connect_woosquare', 'wc_woosquare_token_nonce' );
						$site_url = ( urlencode( $redirect_url ) );
						$args_renew = array(
							'body' => array( 
								'header' => array(
												'Authorization' => 'Bearer '.$wpep_live_token_details->access_token,
												'content-type' => 'application/json'
											),
								'action' => 'renew_token',
								'foradmin' => 'true',
								'site_url'    => $site_url,
							),
							'timeout' => 45,
						);
						
						$oauth_response = wp_remote_post( $oauth_connect_url, $args_renew );
						
						$wpep_live_token_details = json_decode( wp_remote_retrieve_body( $oauth_response ) );
						$wpep_live_token_details->save_db = true;
				}
			} 
			return $wpep_live_token_details;
		}

    }

}