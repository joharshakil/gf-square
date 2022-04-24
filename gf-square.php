<?php
/**
 * Plugin Name: GF Square  (Premium)
 * Description: Gravity Form Square plugin is a WordPress plugin that allows users to pay from their gravity form using Square payment gateway.
 * Author: wpexperts
 * Author URI: https://gravitymore.com/addons/pay-with-square-in-gravity-forms/
 * Version: 2.6.8
 * Text Domain: gravity-forms-square
 * Domain Path: /languages
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;

if( !function_exists('get_plugin_data') ){
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$plugin_data = get_plugin_data( __FILE__ );
if (!defined("GFSR_NEW_PLUGIN_NAME")) {
	define("GFSR_NEW_PLUGIN_NAME", $plugin_data['Name']);
} 
if (!defined("GFSR_NEW_PLUGIN_VER")) {
	define("GFSR_NEW_PLUGIN_VER", $plugin_data['Version']);
}
	
add_action( 'upgrader_process_complete', 'wp_upe_upgrade_completed', 10, 2 );
function wp_upe_upgrade_completed( $upgrader_object, $options ) {

	// The path to our plugin's main file
	$our_plugin = plugin_basename( __FILE__ );
	// If an update has taken place and the updated type is plugins and the plugins element exists
	if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
	 	// Iterate through the plugins being updated and check if ours is there
	 	foreach( $options['plugins'] as $plugin ) {
	  		if( $plugin == $our_plugin ) {
				if (in_array('gravity-forms-square-recurring-premium/gf-square-recurring.php', apply_filters('active_plugins', get_option('active_plugins')))) {
					ob_start();		
						// Set a transient to record that our plugin has just been updated
						set_transient( 'mp-admin-notice-activation', true, 0 );		    
						deactivate_plugins('gravity-forms-square-recurring-premium/gf-square-recurring.php');
					ob_end_flush();
				}
	  		}
	 	}
	}

}

add_action('admin_menu', 'gf_square_custom_menus');
		function gf_square_custom_menus(){
		
			add_menu_page('Gravity Form Square', 'Gravity Form Square', 'manage_options', get_admin_url() . 'admin.php?page=gf_settings');
				add_submenu_page(get_admin_url() . 'admin.php?page=gf_settings', 'Gravity Form Square', 'Gravity Form Square', 'manage_options', get_admin_url() . 'admin.php?page=gf_settings');
				add_submenu_page( get_admin_url() . 'admin.php?page=gf_settings', __( 'Support', 'gravity-forms-square' ), __( 'Support', 'memberpress-square' ), 'manage_options', 'http://support.apiexperts.io/' );

			/* Technical Documentation */
				add_submenu_page( get_admin_url() . 'admin.php?page=gf_settings', __( 'Technical Documentation', 'gravity-forms-square' ), __( 'Technical Documentation', 'memberpress-square' ), 'manage_options', 'https://apiexperts.io/documentation/square-for-gravity-forms/' );

			/* Contact Us */
				add_submenu_page( get_admin_url() . 'admin.php?page=gf_settings', __( 'Customization', 'gravity-forms-square' ), __( 'Customization', 'memberpress-square' ), 'manage_options', 'https://apiexperts.io/contact-us/' );
		}

add_action('admin_notices', 'add_notice_gf_square_premium');
function add_notice_gf_square_premium(){
	if( get_transient( 'mp-admin-notice-activation' ) ){
		//connection auth credentials
		?>
        <div id="gfsqs-notice-1" class="notice notice-warning is-dismissible <?php if ( isset(get_option('gfsqs_all_notices')['gfsqs-notice-1']) && get_option('gfsqs_all_notices')['gfsqs-notice-1'] == 1 ) : echo 'hide-noticed'; endif; ?>">
			<?php echo sprintf( '<p>%s <a href="https://apiexperts.io/documentation/square-for-gravity-forms/#upgrade-to-version-2-1" target="_blank">%s</a></p>', __('New version', 'gravity-forms-square') . ' ' . GFSR_NEW_PLUGIN_VER . ' ' . __('of', 'gravity-forms-square') . ' ' . GFSR_NEW_PLUGIN_NAME . ' '. __('have both simple and recurring payment processing functionality. After Updating to Version', 'gravity-forms-square') . ' ' . GFSR_NEW_PLUGIN_VER . ' ' . __('your current Square Recurring addon will be deactivated automatically and your existing recurring payments will remain the same.', 'gravity-forms-square'), __('Click here', 'gravity-forms-square')  ); ?>
        </div>
        <?php
	}
	?>
	<div id="gfsqs-notice-2" class="notice notice-success is-dismissible <?php if ( isset(get_option('gfsqs_all_notices')['gfsqs-notice-2']) && get_option('gfsqs_all_notices')['gfsqs-notice-2'] == 1 ) : echo 'hide-noticed'; endif; ?>">
		<?php echo sprintf('<p>%s <a href="https://apiexperts.io/documentation/square-for-gravity-forms/#upgrade-to-version-2-1" target="_blank">%s</a></p>', GFSR_NEW_PLUGIN_NAME . ' - ' . __('Now you can setup both simple/recurring payment in a single form.', 'gravity-forms-square'), __('Click Here', 'gravity-forms-square')); ?>
    </div>
	<?php
}

add_action( 'admin_init', 'sp_subscriber_check_activation_notice' );
function sp_subscriber_check_activation_notice(){
    if( get_transient( 'mp-admin-notice-activation' ) ){
		$forms = GFAPI::get_forms();
		foreach ($forms as $form) {
			$fields = $form['fields'];
			$form_id = $form['id'];
			foreach( $fields as $field )  { 
				if ($field->type == 'squarerecurring') {
					//changing type of field
					$field['type'] = 'square';
					$field['isrecurring'] = 1;

					//add card on file setting for this recurring form
					$settings = get_option('gf_square_cof_settings_' . $form_id);
					if (!$settings) {
						$settings = array(
							'square_cof_mode' => 'enabled',
							'square_cof_non_logged_user_text' => __('click here if you are logged in user', 'gravity-forms-square'),
							'square_cof_save_card_text' => __('save card', 'gravity-forms-square'),
							'square_cof_delete_card' => 'disabled',
						);
						update_option('gf_square_cof_settings_'. $form_id, $settings);
					}

					$result = GFAPI::update_form( $form );
					return $result;
				}
			}
		}

		//create transactions table
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
		global $wpdb;
		if (!empty($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if (!empty($wpdb->collate))
			$charset_collate .= " COLLATE $wpdb->collate";

		//notifications table
		$transaction_table = $wpdb->prefix . 'gfsr_transactions';
		if ($wpdb->get_var("SHOW TABLES LIKE '$transaction_table'") != $transaction_table) {
			$sql = "CREATE TABLE " . $transaction_table . " (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`entry_id` bigint(20) NOT NULL,
				`created_at` date NOT NULL,
				`transaction_id` varchar(255) NOT NULL,                        
				PRIMARY KEY (`id`)
			) $charset_collate;";

			dbDelta($sql);
		}

		//always create cronjob event
		if (!wp_next_scheduled('gfsr_renew_cronjob')) {
			wp_schedule_event(time(), 'daily', 'gfsr_renew_cronjob');
		}

        //delete_transient( 'mp-admin-notice-activation' );
    }
}
	
if (!class_exists('Gravity_Forms_Square') ) {

	require_once( plugin_dir_path(__FILE__) . 'lib/gf_square_freemius.php' );

    class Gravity_Forms_Square
    {
        public function __construct(){
            /**
             * check for gravity forms plugin
             */
			//check for gf recurring so deactivate it first.			
			if (in_array('gravity-forms-square-recurring-premium/gf-square-recurring.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				ob_start();		
					set_transient( 'mp-admin-notice-activation', true, 0 );		    
					deactivate_plugins('gravity-forms-square-recurring-premium/gf-square-recurring.php');
				ob_end_flush();
			}
			add_action('wp_loaded', array($this,'square_plugin_dependencies'));
			add_filter( 'gform_form_tag', array($this, 'before_gf_form_render'), 10, 2 );
			
			if ( !define("GFSR_NEW_PLUGIN_PATH", plugin_dir_path(__FILE__)) )
				define("GFSR_NEW_PLUGIN_PATH", plugin_dir_path(__FILE__));

			if ( !define("GFSR_NEW_PLUGIN_URL", plugin_dir_url(__FILE__)) )
				define("GFSR_NEW_PLUGIN_URL", plugin_dir_url(__FILE__));

			if ( !define('GFSR_NEW_PLUGIN_FILE', __FILE__) )
				define('GFSR_NEW_PLUGIN_FILE', __FILE__);

			if ( !define('GFSR_NEW_SLUG', 'GFSR_NEW_SLUG') )
				define('GFSR_NEW_SLUG', 'GFSR_NEW_SLUG');

			register_activation_hook(GFSR_NEW_PLUGIN_FILE, array($this, 'gfsr_renew_cronjob_active'));
			register_deactivation_hook(GFSR_NEW_PLUGIN_FILE, array($this, 'gfsr_renew_cronjob_deactivation'));

			add_action('wp_ajax_gfsqs_hide_notifications', array($this, 'gfsqs_hide_notifications'));
			add_action('wp_ajax_nopriv_gfsqs_hide_notifications', array($this, 'gfsqs_hide_notifications'));
			
			/*$debug_tags = array();
			add_action( 'all', function ( $tag ) {
				global $debug_tags;
				if ( in_array( $tag, $debug_tags ) ) {
					return;
				}
				echo "<pre>" . $tag . "</pre>";
				$debug_tags[] = $tag;
			} );*/

		
		}

		public function gfsqs_hide_notifications () {
			if ( isset($_POST['id']) && ! empty($_POST['id']) ) {
				
				$id = $_POST['id'];
				
				// add notice id to database
				$notices = get_option('gfsqs_all_notices');
				$notices[$id] = 1;
				update_option( 'gfsqs_all_notices', $notices );

				$result['status'] = 'SUCCESS';
                print_r(json_encode($result));
			} else {
				$result['status'] = 'FAILED';
                print_r(json_encode($result));
			}

			// to return proper response
			wp_die();
		}

		public function gfsr_renew_cronjob_active() {
			//create transactions table
			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
			global $wpdb;
			if (!empty($wpdb->charset))
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if (!empty($wpdb->collate))
				$charset_collate .= " COLLATE $wpdb->collate";
	
			//notifications table
			$transaction_table = $wpdb->prefix . 'gfsr_transactions';
			if ($wpdb->get_var("SHOW TABLES LIKE '$transaction_table'") != $transaction_table) {
				$sql = "CREATE TABLE " . $transaction_table . " (
					`id` bigint(20) NOT NULL AUTO_INCREMENT,
					`entry_id` bigint(20) NOT NULL,
					`created_at` date NOT NULL,
					`transaction_id` varchar(255) NOT NULL,                        
					PRIMARY KEY (`id`)
				) $charset_collate;";
	
				dbDelta($sql);
			}
	
			if (!wp_next_scheduled('gfsr_renew_cronjob')) {
				wp_schedule_event(time(), 'daily', 'gfsr_renew_cronjob');
			}
		}

		public function gfsr_renew_cronjob_deactivation() {
			wp_clear_scheduled_hook('gfsr_renew_cronjob');
		}

		public function before_gf_form_render ( $form_tag, $form ) {

			$fields = $form['fields'];
			$is_recurring = 'disabled';
			$current_path = $_SERVER['REQUEST_URI'];
			$login_url = wp_login_url($current_path);
			$settings = get_option( 'gf_square_cof_settings_' . $form['id'] , false );

			foreach ($fields as $key => $field) { 
				if ($field->type == 'square') { 
					if ($field->isrecurring=='1') { 
						$is_recurring = 'enabled';
					} 
				}
			}

			if ( $settings && 'disabled'===$settings['square_cof_mode'] && 'enabled'===$is_recurring ) {
				$content = '<p style="color:red">'. __('You need to enable card on file for recurring payment form', 'gravity-forms-square') . '</p>';
				return $form_tag. ''.$content;
				wp_die();
			}
			
			if ( !is_user_logged_in() && $settings && !empty($settings['square_cof_non_logged_user_text']) && 'enabled'==$settings['square_cof_mode'] ) {
				$link = '<a href="' . $login_url . '" class="square_gf_login" >' . $settings['square_cof_non_logged_user_text'] . '</a>';
				//$content = $form_tag .''. $link;
				$content = $link;
				return $form_tag .''. apply_filters( 'gfsr_before_form', $content, $form['id'] );
		
			} else {
				return $form_tag;
			}
		}
		
        public function square_plugin_dependencies() {

            if (!class_exists('GF_Field') || !$this->is_allowed_currencies_for_gravity()) {
                add_action('admin_notices', array($this, 'admin_notices'));
            } 
            else 
            {
                
				add_action( 'deactivated_plugin', array($this, 'detect_plugin_deactivation_gravity_forms_square'));

                define("SQGF_PLUGIN_PATH", plugin_dir_path(__FILE__));
                define("SQGF_PLUGIN_URL", plugin_dir_url(__FILE__));
                /**
                 * include square lib
                 */
                require_once( SQGF_PLUGIN_PATH . 'lib/square-sdk/autoload.php' );
                /**
                 * include freemius lib
                 */
                global $gravity_forms_square;
                if ( gravity_forms_square()->is__premium_only() ) {
                	if ( gravity_forms_square()->can_use_premium_code() ) {
					
						//connection auth credentials
						if( !function_exists('get_plugin_data') ){
							require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
						}
						$plugin_data = get_plugin_data( __FILE__ );

						$WOOSQU_GF_PLUGIN_NAME = $plugin_data['Name'];
						if (!defined('WOOSQU_GF_PLUGIN_NAME')) define('WOOSQU_GF_PLUGIN_NAME',$WOOSQU_GF_PLUGIN_NAME);
						if (!defined('WOOSQU_GF_PLUGIN_AUTHOR')) define('WOOSQU_GF_PLUGIN_AUTHOR',$plugin_data['Author']);
						if (!defined('WOOSQU_GF_CONNECTURL')) define('WOOSQU_GF_CONNECTURL','http://connect.apiexperts.io');
						if (!defined('WOOSQU_GF_APPID')) define('WOOSQU_GF_APPID','sq0idp-PjaAvPLQm6-cbgbzeSh16w');
						if (!defined('WOOSQU_GF_APPNAME')) define('WOOSQU_GF_APPNAME','APIExperts Gravity Forms');
						
						add_action( 'admin_init', array($this, 'square_gf_auth_success_action'));	
						
						/**
						 * Square Refund Class Call
						 */						
						require_once( SQGF_PLUGIN_PATH . 'includes/class-square-refund.php' );

						/**
						 * square form settings
						 */
						require_once( SQGF_PLUGIN_PATH . 'includes/class-square-settings.php' );
						new Square_GF_Settings();
						
						
						/**
						 * include square class
						 */
						require_once( SQGF_PLUGIN_PATH . 'includes/class-square-gf.php' );
						$new_obj = new Square_GF();
						GF_Fields::register($new_obj);
						add_action('wp_ajax_cof_delete_card', array($new_obj, 'my_cof_delete_card'));
						add_action('wp_footer', array($new_obj, 'my_cof_delete_card_confirmation_box'));
						//gift card ajax action
						add_action('wp_ajax_gfsqs_process_giftcard', array($new_obj, 'gfsqs_process_giftcard') );
                		add_action('wp_ajax_nopriv_gfsqs_process_giftcard', array($new_obj, 'gfsqs_process_giftcard') );

						/**
						 * renew subscription
						 */
						require_once( SQGF_PLUGIN_PATH . 'includes/class-square-renew.php' );
						new GFSR_Renew();
						
						
						add_action('admin_notices', array($this, 'admin_notices_check_refresh_token'));

                    }
                }
            }
        }
		
		public function admin_notices_check_refresh_token(){
			
			$forms = GFAPI::get_forms();
			$plugin_data = get_plugin_data( __FILE__ );
			$count = 0;
			foreach ( $forms as $form) {
				$settings = get_option('gf_square_settings_'.$form['id']);
				if(!empty($settings['square_auth_request'])){
				    
				    
        			if (!is_object($settings['square_auth_request'])) {
        				$settings['square_auth_request'] = (object) $settings['square_auth_request'];
        			}
				    
					if(
						!empty($settings['square_auth_request']->access_token)
						and 
						empty($settings['square_auth_request']->refresh_token)
					){
						$message = sprintf('%s <a href="%s">%s</a>', __('IMPORTANT NOTICE FOR', 'gravity-forms-square') . ' ' . WOOSQU_GF_PLUGIN_NAME . ' ' . __('THIS IS A MAJOR UPDATE VERSION AND ADMINISTRATOR MUST RECONNECT SQUARE APPLICATION WITH PLUGIN TO APPLY NEW CHANGES AUTOMATICALLY.', 'gravity-forms-square'), 'admin.php?page=gf_edit_forms&view=settings&subview=square_settings_page&id='.$form['id'], __('here', 'gravity-forms-square') );
			            $count++;
						printf('<div class="notice notice-error"><p>%1$s</p></div>',  $message);
    					if($count > 3){
    				         break;
    				    }
					}
				}

			}
		}
		public function detect_plugin_deactivation_gravity_forms_square(){
			if (in_array('woosquare/woocommerce-square-integration.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				delete_option('fs_accounts');
			}
		}
        public function is_allowed_currencies_for_gravity(){
            if ( in_array( 'gravityforms/gravityforms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                $currency = version_compare(GFCommon::$version , '2.5', '<') ? get_option( '_gform_setting_currency' ) : GFCommon::get_currency();


				if ( 
                'USD' == $currency || 
                'CAD' == $currency || 
                'JPY' == $currency ||
                'AUD' == $currency ||
                'EUR' == $currency ||
                'GBP' == $currency 
                ) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        public function admin_notices() {
            $class = 'notice notice-error';  

			if (!class_exists('GF_Field')) {
                 $messages[] = __('Gravity Forms Square Payment requires Gravity Forms to be installed and active.', 'gravity-forms-square');
            }			
            if ($this->is_allowed_currencies_for_gravity()  == false  ) {
                $messages[] =  __( 'To enable Gravity Form Square Payment. Gravity Form Currency must be USD,CAD,AUD,JPY,GBP', 'gravity-forms-square' );
            }
           
            if(!empty($messages) and is_array($messages)){
               foreach($messages as $message){
                    printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
               }
           }
        }
		
		public function square_gf_auth_success_action(){
			
			if(
				!empty($_REQUEST['access_token']) and 
				!empty($_REQUEST['token_type']) and 
				!empty($_REQUEST['id']) and 
				!empty($_REQUEST['gravity_forms_square_token_nonce']) and 
				is_numeric($_REQUEST['id']) and 
				$_REQUEST['token_type'] == 'bearer' 
				){
					if ( function_exists( 'wp_verify_nonce' ) && ! wp_verify_nonce( $_GET['gravity_forms_square_token_nonce'], 'connect_gravity_forms_square' ) ) {
						wp_die( __( 'Cheatin&#8217; huh?', 'woosquare-square' ) );
					}
					$form_id = $_REQUEST['id']; 
					$settings = get_option('gf_square_settings_' . $form_id);
					
					if(!empty($settings) and is_array($settings)){
						$settings['square_token'] = $_REQUEST['access_token'];
						$settings['square_appid'] = WOOSQU_GF_APPID;
						$settings['square_auth_request'] = $_REQUEST;
					} else  {
						 $settings = array(
							'square_test_appid' => '',
							'square_test_locationid' => '',
							'square_test_token' => '',
							'square_appid' => WOOSQU_GF_APPID,
							'square_locationid' => '',
							'square_token' => $_REQUEST['access_token'],
							'gf_squaree_mode' => 'live',
							'square_auth_request' => $_REQUEST,
							'gf_square_inputs' => '',
							'send_email_notification' => 0,
							'send_form_id_square'=> $form_id
						);
					}
					
					update_option('gf_square_settings_'.$form_id, $settings);
					
					$gravity_forms_square_form_counter = get_option('gravity_forms_square_form_counter');
					if(!$gravity_forms_square_form_counter){
						$gravity_forms_square_form_counter = 0;
					} 
					$gravity_forms_square_form_counter = $gravity_forms_square_form_counter+1;
					update_option('gravity_forms_square_form_counter',$gravity_forms_square_form_counter);
					
					$location = $this->get_location($_REQUEST['access_token']);
					
					update_option('gf_square_settings_'.$form_id, $settings);
					
					update_option('gf_square_settings_location_'.$form_id,$location);
					
					unset($_REQUEST['app_name']);
					unset($_REQUEST['plug']);
					unset($_REQUEST['gravity_forms_square_token_nonce']);
					unset($_REQUEST['access_token']);
					unset($_REQUEST['token_type']);
					unset($_REQUEST['expires_at']);
					unset($_REQUEST['merchant_id']);
					unset($_REQUEST['refresh_token']);
					
					wp_redirect(add_query_arg(
						$_REQUEST,
						admin_url( 'admin.php' )
					));
					exit;
				
				}
				
				if(
					!empty($_REQUEST['disconnect_gravity_forms_square']) and 
					!empty($_REQUEST['gravity_forms_square_token_nonce']) 
				){
					
					if ( function_exists( 'wp_verify_nonce' ) && ! wp_verify_nonce( $_GET['gravity_forms_square_token_nonce'], 'disconnect_gravity_forms_square' ) ) {
						wp_die( __( 'Cheatin&#8217; huh?', 'woocommerce-square' ) );
					}
					
					
					$form_id = $_REQUEST['id'];
					$settings = get_option('gf_square_settings_' . $form_id);
					$square_token = $settings['square_token'];
					$settings['square_token'] = '';
					$settings['square_appid'] = '';
					$settings['square_locationid'] = '';
					$settings['square_auth_request'] = '';
					update_option('gf_square_settings_'.$form_id, $settings);
					
					$gravity_forms_square_form_counter = get_option('gravity_forms_square_form_counter');
					$form_counter = ' Form '.$gravity_forms_square_form_counter;
					if(!$gravity_forms_square_form_counter){
						$gravity_forms_square_form_counter = 0;
					} 
					$gravity_forms_square_form_counter = $gravity_forms_square_form_counter-1;
					update_option('gravity_forms_square_form_counter',$gravity_forms_square_form_counter);
					
					delete_option('gf_square_settings_location_'.$form_id);
					
					unset($_REQUEST['app_name']);
					unset($_REQUEST['plug']);
					unset($_REQUEST['gravity_forms_square_token_nonce']);
					
					//revoke token
					$oauth_connect_url = WOOSQU_GF_CONNECTURL;
					$headers = array(
						'Authorization' => 'Bearer '.$square_token, // Use verbose mode in cURL to determine the format you want for this header
						'Content-Type'  => 'application/json;',
					);	

					$redirect_url = add_query_arg(
						array(
							'app_name'    => WOOSQU_GF_APPNAME,
							'plug'    => WOOSQU_GF_PLUGIN_NAME.$form_counter,
						),
						admin_url( 'admin.php' )
					);

					$redirect_url = wp_nonce_url( $redirect_url, 'disconnect_gravity_forms_square', 'gravity_forms_square_token_nonce' );
					$site_url = ( urlencode( $redirect_url ) );
					$args_renew = array(
						'body' => array(
							'header' => $headers,
							'action' => 'revoke_token',
							'site_url'    => $site_url,
						),
						'timeout' => 45,
					);

					$oauth_response = wp_remote_post( $oauth_connect_url, $args_renew );

					$decoded_oauth_response = json_decode( wp_remote_retrieve_body( $oauth_response ) );
					
					wp_redirect(add_query_arg(
						$_REQUEST,
						admin_url( 'admin.php' )
					));
					exit;
				}
		}
		
		
		
		
		public function get_location($token){
				$url = 'https://connect.squareup.com/v2/locations';
				$headers = array(
					'Authorization' => 'Bearer '.$token, // Use verbose mode in cURL to determine the format you want for this header
					'Content-Type'  => 'application/json;',
					'token'  => $token,
				);
				$method = "GET";
				$args = array('');
				$response = $this->wp_remote_wcsrs($url,$args,$method,$headers);
				return $response;
			}
			
			
		public function wp_remote_wcsrs($url,$args,$method,$headers){
				// $args = array( 'id' => 1234 );
				// $method = 'GET'; // or 'POST', 'HEAD', etc
				// $headers = array(
					// 'Authorization' => 'Bearer ' . $auth, // Use verbose mode in cURL to determine the format you want for this header
					// 'Accept'        => 'application/json;ver=1.0',
					// 'Content-Type'  => 'application/json; charset=UTF-8',
					// 'Host'          => 'api.example.com'
				// );
				$token = $headers['token'];
				unset($headers['token']);
				$request = array(
					'headers' => $headers,
					'method'  => $method,
				);

				if ( $method == 'GET' && ! empty( $args ) && is_array( $args ) ) {
					$url = add_query_arg( $args, $url );
				} else {
					$request['body'] = json_encode( $args );
				}
				
				$response = wp_remote_request( $url, $request );
				
				
				
				$decoded_response = json_decode( wp_remote_retrieve_body( $response ) );
				
				return $decoded_response;
			}
		
    }
    $instance = new Gravity_Forms_Square();
}