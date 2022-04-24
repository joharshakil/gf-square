<?php

// Create a helper function for easy SDK access.
function gravity_forms_square()
{
    global  $gravity_forms_square ;
    
    if ( !isset( $gravity_forms_square ) ) {
        // Include Freemius SDK.
        require_once dirname( __FILE__ ) . '/freemius/start.php';
        $gravity_forms_square = fs_dynamic_init( array(
            'id'               => '1651',
            'slug'             => 'gf-square',
            'type'             => 'plugin',
            'public_key'       => 'pk_ff64585d9c96ce5b44d4660157cf1',
            'is_premium'       => true,
            'has_addons'       => false,
            'has_paid_plans'   => true,
            'is_org_compliant' => false,
            'has_affiliation'     => 'selected',
            'menu'                => array (
                'first-path'     => 'plugins.php',
                'support'        => false,
            ),
            'is_live'          => true,
        ) );
    }
    
    return $gravity_forms_square;
}

// Init Freemius.
gravity_forms_square();
// Signal that SDK was initiated.
do_action( 'gravity_forms_square_loaded' );
function gravity_forms_square_custom_connect_message_on_update(
    $message,
    $user_first_name,
    $plugin_title,
    $user_login,
    $site_link,
    $freemius_link
)
{
    return sprintf(
        __( 'Hey %1$s' ) . ',<br>' . __( 'Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'gf-square' ),
        $user_first_name,
        '<b>' . $plugin_title . '</b>',
        '<b>' . $user_login . '</b>',
        $site_link,
        $freemius_link
    );
}

gravity_forms_square()->add_filter(
    'connect_message_on_update',
    'gravity_forms_square_custom_connect_message_on_update',
    10,
    6
);
// lisence expired message
function my_custom_admin_notice( $message ) {
	
    $message = 'Your License is expired. You can still continue using the free plugin forever.Install GF Square free plugin from <a href="https://wordpress.org/plugins/pay-with-square-in-gravity-forms/">here</a>';

    return $message;
}

gravity_forms_square()->add_filter( 'sticky_message_license_expired', 'my_custom_admin_notice', 10, 1 );