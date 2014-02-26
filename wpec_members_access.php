<?php
/*
Plugin Name: Members Only: Membership & Subscription plugin for WP e-Commerce
Plugin URI: http://www.instinct.co.nz
Description: A premium add-on for WP e-Commerce that allows admins to restrict WordPress content to free or paid members, set up multiple membership tiers, administrate members and to create subscriptions. Requires the free WP e-Commerce plugin and WP e-Commerce Gold Cart. See also: <a href="http://getshopped.org" target="_blank">GetShopped.org</a> | <a href="http://getshopped.org/forums/" target="_blank">Support Forum</a> | <a href="http://docs.getshopped.org/" target="_blank">Documentation</a>
Version: 2.9
Author: Instinct Entertainment
Author URI:  http://getshopped.org/extend/premium-upgrades/premium-upgrades/members-only-module/
*/

/*
@TODO before 3.0 gets launched
    Fix the email user when sub ends issues -michelle will fix currently only using 3.7 code
    Add messaging in for updated subscriptions and updated members when manually added
    PayPal - (Nuno is sorting PP)
    Canceling sub in PP must also cancel it on the wp site,
    Canceling your subscription on the WP site will cancel it in paypal.
    Deleting a subscription will remove that sub from the user should also delete the paypal auto payment
    Deleting a subscription from a user need to cancel it in PP
    Test buying more than one fo the same subscription

    Remove default capoabilities we dont need these
    Members widget - needs a revist and fix up
    Remove all reference to bbPress or will this support bbpress - if its going to support it then lets code this
    For now move bbpress code to a seperate file
    sort out including all these files correctly and having an admin init etc
    Users search implimented into the lsit table class
    Add Remove all subscriptions back into the bulk options for memebers list table
    Ensure we can filter product content / make this work with wpec pages to
    Jeff auto update stuff to be added
    ERROR WARNING / NOTICE DEBUG
    all function indeneted and named consistantly eg: wpec_members_
    fix div issue after memebers have been imported
    Check users subscriptions are running out etc when run out they should be ccanceleld from the site
    Update script to update current memebers on ppls sites to the new system add in the extra user_meta value
    product page metabox function needs rewrite - remove 3.7 compat issues this is now only 3.8
    Get the JS in the JS file.
    Fix the email sending when subscriptions have expried - currently using old 3.7 code.
    filter for filtering tables by different capabilities
*/

add_action( 'init', 'wpec_members_init' );

function wpec_members_init() {
    //define constants
    $wpsc_plugin_url = plugins_url( '', __FILE__ );
    define( 'WPSC_MEMBERS_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
    define( 'WPSC_MEMBERS_URL', $wpsc_plugin_url );

    //include files
    $files = array(
        'classes/wpec_subscribed_members_list_table.php', //subscribers table class
        'classes/wpec_subscriptions_list_table.php', //subscriptions table class
        'classes/wpec_subscribed_import_members_list_table.php', //import table class

        'wpec_members_access_admin.php', //contains admin functions for edit save etc
        'wpec_members_admin_display.php', // loads the views for each of the tabs and buttons
        'ipn_integration.php', // Handles the IPN notifications from PayPal

        'widgets/my-subscriptions.php', //my subscriptions widget

        'purchase_capability.php' // this will possbily be deleted
    );

    foreach ($files as $file)
        include_once($file);
}

register_activation_hook( __FILE__, 'my_activation' );
add_action( 'send_email_daily', 'wpsc_send_email' );

function my_activation() {
    wp_schedule_event( time(), 'hourly', 'send_email_daily' );
}

register_deactivation_hook( __FILE__, 'my_deactivation' );


function my_deactivation() {
    wp_clear_scheduled_hook( 'send_email_daily' );
}

$wpsc_product_capability_list = get_option( 'wpsc_product_capability_list' );

/* Add the menu */
function wpec_members_menu_items() {
    add_submenu_page( 'edit.php?post_type=wpsc-product' , __( 'Memberships & Subscriptions', 'wpec_members' ), __( 'Memberships & Subscriptions', 'wpec_members' ), 'manage_options', 'wpec_members', 'wpec_members_render_list_page' );
}

add_action( 'admin_menu', 'wpec_members_menu_items' );

function wpec_members_deactivation( $subscr_id, $details) {
    global $wpdb;

    // what is the user_id
    $user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_ID FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE transactid = %s", $subscr_id ) );

    //error_log("SUBSCR CANCELED USER ID = ".$user_id);

    // cancel the subscription locally
    update_user_meta( $user_id, '_subscription_canceled', true );
}

add_action( 'wpsc_paypal_standard_deactivate_subscription', 'wpec_members_deactivation', 10, 2 );
// display the subscription information on the my account page with a link to cancel
function wpec_members_add_cancel() {
    $user_id = get_current_user_id();
    ?>
     | <a href="<?php echo get_option( 'user_account_url' ) . "&cancel_members_subscription=true&id=".$user_id; ?>"><?php _e('Cancel Subscription', 'wpsc_members'); ?></a>
    <?php
}

add_action( 'wpsc_additional_user_profile_links', 'wpec_members_add_cancel' );

function wpec_members_cancel_subscription() {
    if ( isset ( $_REQUEST['cancel_members_subscription'] ) && $_REQUEST['cancel_members_subscription'] == true ) {
        wpec_members_cancel_user_subscription(); // this will remove the subscription entirely
    }
}

add_action( 'init', 'wpec_members_cancel_subscription' );

/**
 * A function for making time periods readable
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     2.0.1
 * @link        http://aidanlister.com/2004/04/making-time-periods-readable/
 * @param       int     number of seconds elapsed
 * @param       string  which time periods to display
 * @param       bool    whether to show zero time periods
 */
/// this function needs to be deleted and mktime used or at least use one function or the other!
function vl_wpscsm_time_duration( $seconds, $use = null, $zeros = false ) {
    $periods = array (
            'years'     => 31556926,
            'Months'    => 2629743,
            'weeks'     => 604800,
            'days'      => 86400,
            'hours'     => 3600,
            'minutes'   => 60,
            'seconds'   => 1
    );
    $seconds = (float) $seconds;
    $segments = array();
    foreach ( $periods as $period => $value ) {
        if ( $use && strpos( $use, $period[0] ) === false ) {
            continue;
        }
        $count = floor( $seconds / $value );
        if ( $count == 0 && !$zeros ) {
            continue;
        }
        $segments[ strtolower($period) ] = $count;
        $seconds = $seconds % $value;
    }

    $string = array();
    foreach ( $segments as $key => $value ) {
        $segment_name = substr( $key, 0, -1 );
        $segment = $value . ' ' . $segment_name;
        if ( $value != 1 ) {
            $segment .= 's';
        }
        $string[] = $segment;
    }
    return implode( ', ', $string );
}

function wpsc_members_init() {
    load_plugin_textdomain( 'wpsc_members', false, dirname( plugin_basename( __FILE__ ) ) );
}

add_action( 'plugins_loaded', 'wpsc_members_init' );
?>