<?php
namespace GFSR\Refund;

/**
 * Singleton class pattern
 */
if (!defined('ABSPATH'))
    exit;
    
class Square_Refund {

    // Hold the class instance.
    private static $instance = null;

    /**
     * Class Constructor
     */
    private function __construct() {
        add_action('gfsr_after_simple_payment_details', array(__CLASS__, 'gfsr_add_refund_simple_payment'), 10, 2);
    
        add_action('gfsr_after_recurring_payment_details', array(__CLASS__, 'gfsr_add_refund_recurring_payment'), 10, 2);
        
        add_action('wp_ajax_gfsr_add_refund_payment_ajax', array(__CLASS__, 'gfsr_add_refund_payment_ajax'));
        
        add_action('wp_ajax_gfsr_process_payment_ajax', array(__CLASS__, 'gfsr_process_payment_ajax'));
        
        add_action('admin_enqueue_scripts', array(__CLASS__, 'gfsr_refund_scripts'));
		
		add_filter('gform_noconflict_scripts', array(__CLASS__, 'rgstr_sf_script_wp_square') );
    }
	
	public function rgstr_sf_script_wp_square( $scripts ){
		//registering my script with Gravity Forms so that it gets enqueued when running on no-conflict mode
		$scripts[] = "gfsr_refund_script";
		return $scripts;
	}

    public static function gfsr_refund_scripts ( $hook ) {
        if ( 'forms_page_gf_entries' === $hook ) {
            
            wp_register_script( 'gfsr_refund_script', SQGF_PLUGIN_URL . 'assets/js/gfsr_refund.js', array(), '2.6&' . mktime(date('dmY')), true );
            wp_localize_script('gfsr_refund_script', 'gfsr_refund', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'form_id' => isset($_REQUEST['id']) ? $_REQUEST['id'] : '',
            ));
            wp_enqueue_script('gfsr_refund_script');
        }
    }

    public static function gfsr_add_refund_simple_payment ( $entry, $transaction_id ) {

        $payment_status_returned_from_square = !empty( gform_get_meta( $entry['id'], 'payment_status_returned' ) ) ? gform_get_meta( $entry['id'], 'payment_status_returned' ) : gform_get_meta( $entry['id'], 'payment_status_returned_' . $transaction_id );

        if ( 'APPROVED' === $payment_status_returned_from_square ) {
            ?>
            <div class="gfsr_processing_payment_container">
                <a href="#" data-type="complete" data-transaction-id="<?php echo $transaction_id;?>" data-entry-id="<?php echo $entry['id']; ?>" class="gfsr_proccess_transaction button-primary button-success">Complete</a>
                <a href="#" data-type="cancel" data-transaction-id="<?php echo $transaction_id;?>" data-entry-id="<?php echo $entry['id']; ?>" class="gfsr_proccess_transaction button-primary button-failed">Cancel</a>
            </div>
            <div class="refund_result"></div>
            <?php
            return;
        }
        
        if ( 'COMPLETED' === $payment_status_returned_from_square && '' == gform_get_meta( $entry['id'], 'refund_id_for_' . $transaction_id ) ) {
            
            ?>            
            <textarea class="refund_reason" cols="30" rows="5" Placeholder="Enter Reason of Refund"></textarea>
            <a href="#" data-type="simple" data-transaction-id="<?php echo $transaction_id;?>" data-entry-id="<?php echo $entry['id']; ?>" class="gfsr_refund button-primary">Make Refund</a>
            <div class="refund_result"></div>
            <?php
        } elseif ( 'PENDING' === $payment_status_returned_from_square && '' != gform_get_meta( $entry['id'], 'refund_id_for_' . $transaction_id ) ) {
            $reason = gform_get_meta( $entry['id'], 'refund_reason_for_' . $transaction_id );
            $refund_id = gform_get_meta( $entry['id'], 'refund_id_for_' . $transaction_id );
            ?>
            <p style="max-width:350px;"><strong>Refund ID: </strong><?php echo $refund_id; ?></p>
            <p style="max-width:350px;"><strong>Refund Reason: </strong><?php echo $reason; ?></p>
            <?php
        }
              
    }

    public static function gfsr_add_refund_recurring_payment ( $entry, $transaction_id ) {   
        
        $payment_status_returned_from_square = !empty( gform_get_meta( $entry['id'], 'payment_status_returned' ) ) ? gform_get_meta( $entry['id'], 'payment_status_returned' ) : gform_get_meta( $entry['id'], 'payment_status_returned_' . $transaction_id );

        if ( 'APPROVED' === $payment_status_returned_from_square ) {
            ?>
            <div class="gfsr_processing_payment_container">
                <a href="#" data-type="complete" data-transaction-id="<?php echo $transaction_id;?>" data-entry-id="<?php echo $entry['id']; ?>" class="gfsr_proccess_transaction button-primary button-success">Complete</a>
                <a href="#" data-type="cancel" data-transaction-id="<?php echo $transaction_id;?>" data-entry-id="<?php echo $entry['id']; ?>" class="gfsr_proccess_transaction button-primary button-failed">Cancel</a>
            </div>
            <div class="refund_result"></div>
            <?php
            return;
        }
        
        if ( 'COMPLETED' === $payment_status_returned_from_square && '' == gform_get_meta( $entry['id'], 'refund_id_for_' . $transaction_id ) ) {
            
            ?>            
            <textarea class="refund_reason" cols="30" rows="5" Placeholder="Enter Reason of Refund"></textarea>
            <a href="#" data-type="recurring" data-transaction-id="<?php echo $transaction_id;?>" data-entry-id="<?php echo $entry['id']; ?>" class="gfsr_refund button-primary">Make Refund</a>
            <div class="refund_result"></div>
            <?php
        } elseif ( 'PENDING' === $payment_status_returned_from_square && '' != gform_get_meta( $entry['id'], 'refund_id_for_' . $transaction_id ) ) {
            $reason = gform_get_meta( $entry['id'], 'refund_reason_for_' . $transaction_id );
            $refund_id = gform_get_meta( $entry['id'], 'refund_id_for_' . $transaction_id );
            ?>
            <p style="max-width:350px;"><strong>Refund ID: </strong><?php echo $refund_id; ?></p>
            <p style="max-width:350px;"><strong>Refund Reason: </strong><?php echo $reason; ?></p>
            <?php
        }
    }

    public static function gfsr_add_refund_payment_ajax () {

        $result = array();
        $result['status'] = '';
        $result['code']   = '';
        $result['message']   = '';
        $result['refund_id']   = '';
        
        if ( isset($_POST['hdnAction']) && isset($_POST['entry_id']) && !empty($_POST['entry_id']) && isset($_POST['form_id']) && !empty($_POST['form_id']) ) {

            $form_id = $_POST['form_id'];
            $entry_id = $_POST['entry_id'];
            $transaction_id = $_POST['transaction_id'];
            $amount = gform_get_meta( $entry_id, 'amount' );
            $currency = gform_get_meta( $entry_id, 'currency' );
            $payment_status = !empty( gform_get_meta( $entry['id'], 'payment_status_returned' ) ) ? gform_get_meta( $entry['id'], 'payment_status_returned' ) : gform_get_meta( $entry_id, 'payment_status_returned_' . $transaction_id );
            $mode = gform_get_meta( $entry_id, 'payment_mode' );
            $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

            if ( 'COMPLETED' === $payment_status ) {
                $fields = array(
                    "idempotency_key" => uniqid(),
                    "payment_id" => $transaction_id,
                    "reason" => $reason,
                    "amount_money" => array(
                        "amount" => (int) round( $amount  * 100, 2 ),
                        "currency" => $currency,
                    ),
                );

                //get form square settings
                $settings = get_option('gf_square_settings_' . $form_id);

                if ( 'test' === $mode ) {
                    $url   = "https://connect.squareupsandbox.com/v2/refunds";
                    $token = $settings['square_test_token'];

                } else {
                    $url   = "https://connect.squareup.com/v2/refunds";
                    $token = $settings['square_token'];
                }

                $headers = array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token, //
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache'
                );

                $response = json_decode( wp_remote_retrieve_body(wp_remote_post( $url, array(
                        'method' => 'POST',
                        'headers' => $headers,
                        'httpversion' => '1.0',
                        'sslverify' => false,
                        'body' => json_encode($fields)
                        ))
                    )
                );                

                if ( !isset( $response->errors ) ) {

                    $result['status'] = $response->refund->status;
                    $result['refund_id']   = $response->refund->id;
                    $result['message'] = 'Refunded Successfully.';
                    //saving reason in entry meta
                    gform_update_meta( $entry_id, 'payment_status_returned_' . $transaction_id, $response->refund->status );
                    gform_update_meta( $entry_id, 'refund_reason_for_' . $transaction_id, $reason );
                    gform_update_meta( $entry_id, 'refund_id_for_' . $transaction_id, $response->refund->id );
                
                } else {

                    $result['status'] = 'FAILED';
                    $result['code'] = $response->errors[0]->code;
                    $result['message'] = $response->errors[0]->detail;
                }
                

            }
        }

        print_r(json_encode($result));
        wp_die();
        
    }

    public static function gfsr_process_payment_ajax () {

        $result = array();
        $result['status'] = '';
        $result['code']   = '';
        $result['message']   = '';
        
        if ( isset($_POST['hdnAction']) && isset($_POST['entry_id']) && !empty($_POST['entry_id']) && isset($_POST['form_id']) && !empty($_POST['form_id']) ) {

            $form_id = $_POST['form_id'];
            $entry_id = $_POST['entry_id'];
            $transaction_id = $_POST['transaction_id'];
            $payment_status = !empty( gform_get_meta( $entry['id'], 'payment_status_returned' ) ) ? gform_get_meta( $entry['id'], 'payment_status_returned' ) : gform_get_meta( $entry_id, 'payment_status_returned_' . $transaction_id );
            $mode = gform_get_meta( $entry_id, 'payment_mode' );
            if ( isset($_POST['hdnAction']) && 'complete' == $_POST['hdnAction'] ) {
                $message = 'Payment captured Successfully.';
            } else {
                $message = 'Payment cancelled Successfully';
            }

            if ( 'APPROVED' === $payment_status ) {
                
                //get form square settings
                $settings = get_option('gf_square_settings_' . $form_id);

                if ( 'test' === $mode ) {
                    $url   = "https://connect.squareupsandbox.com/v2/payments/". $transaction_id ."/". $_POST['hdnAction'];
                    $token = $settings['square_test_token'];

                } else {
                    $url   = "https://connect.squareup.com/v2/payments/". $transaction_id ."/". $_POST['hdnAction'];
                    $token = $settings['square_token'];
                }

                $headers = array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token, //
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache'
                );

                $response = json_decode( wp_remote_retrieve_body(wp_remote_post( $url, array(
                        'method' => 'POST',
                        'headers' => $headers,
                        'httpversion' => '1.0',
                        'sslverify' => false,
                        ))
                    )
                );   
                
                // echo '<pre>$response$response';
                // print_r($response);
                // echo '</pre>';
                // wp_die('stop!');

                if ( !isset( $response->errors ) ) {

                    $result['status'] = $response->payment->status;
                    $result['payment_id']   = $response->payment->id;
                    $result['message'] = $message;
                    //saving reason in entry meta
                    gform_update_meta( $entry_id, 'payment_status_returned_' . $transaction_id, $response->payment->status );
                
                } else {

                    $result['status'] = 'FAILED';
                    $result['code'] = $response->errors[0]->code;
                    $result['message'] = $response->errors[0]->detail;
                }
                

            }
        }

        print_r(json_encode($result));
        wp_die();

    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Square_Refund();
        }

        return self::$instance;
    }
}

$square_refund = Square_Refund::getInstance();