<?php
/**
 * This file has some functions that are used to display the admin page of the plugin.
*/

/* switch the tabs to decide what table or page to display */
function wpec_members_render_list_page() {
    global $wpsc_product_capability_list;

/*  The subscription table contains the subscription name (also pass via url with row actions) this is different to the array key in the global cap list so we need to loop through and return the key for processing */

    if ( isset( $_GET['subscription'] ) ) {
        $subscription_name = $_GET['subscription'];
        $capability = '';
        foreach ( $wpsc_product_capability_list as  $key => $value ) {
            if ( $value['name'] == $subscription_name ) {
                $capability = $key;
                continue;
            }
        }
    }

	if ( isset ( $_GET['tab'] ) ) {
		switch( $_GET['tab'] ) {
			case 'wpec_manage_members' :
				wpec_members_display_manage_members_table();
				break;
			case 'wpec_manage_subscriptions' :
				wpec_members_display_manage_subscriptions_table();
				break;
			case 'wpec_edit_subscription' :
				wpec_members_display_edit_subscription( $capability );
				break;
			case 'wpec_add_subscription' :
				wpec_members_display_add_subscription();
				break;
			case 'wpec_add_new_member' :
				wpec_members_display_add_new_member();
				break;
			case 'wpec_import_members':
				wpec_members_display_import_members();
				break;
			case 'wpec_manage_settings':
				wpec_members_display_manage_settings();
				break;
			case 'edit_member' :
				//wpec_members_display_edit_member();
				wpec_members_display_wpec_edit_member();
				break;
		}
	} else {
		wpec_members_display_manage_subscriptions_table();
	}
}

function wpec_members_display_add_subscription() {
    global $wpdb, $wpsc_product_capability_list, $user_ID;
    ?>

    <div class="wrap">
        <?php wpec_members_display_manage_subscription_tabs(); ?>

        <h2>
            <?php _e('Add New Membership/Subscription', 'wpsc_members'); ?>
        </h2>

        <?php wpec_members_subscription_form( 'new' ); ?>
    </div>

<?php
}

function wpec_members_subscription_form( $capability = "new" ) {
    global $wpsc_product_capability_list;

    $capability_data = $wpsc_product_capability_list[ $capability ];

    ?>
    <form class="purchasable-capabilities-form" enctype="multipart/form-data" action="" method="post">
        <p>Create a new membership/subscription to limit content on your website to only those who purchase it or have access to it.</p>
        <p>Once created, you can edit any product to link it to the subscription/membership to sell it to your users.</p>
        <table class="form-table">
            <tr>
                <td valign="top"><?php _e('Display Name', 'wpsc_members') ?></td>
                <td>
                    <input name='capability_list[<?php echo $capability; ?>][name]' value='<?php echo esc_attr( $capability_data['name'] ); ?>' size='22' />
                </td>
            </tr>
            <tr>
                <td width="200" valign="top"><?php _e('Internal Name (Slug)', 'wpsc_members'); ?></td>
                <td>
                    <?php if ($capability === "new") { ?>
                        <input name='capability_list[<?php echo $capability; ?>][capability]' value='' size='22' />
                        <p class="description"><?php echo _e('This is the internal name used by plugins and themes to identify this membership/subscription. This should probably be all lower-case and not contain spaces.', 'wpsc_members'); ?></p>
                    <?php } else { ?>
                        <p><code><?php echo esc_attr( stripslashes( $capability ) ); ?></code></p>
                        <p class="description"><?php echo _e("Slugs are not editable once created.", 'wpsc_members'); ?>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td valign="top"><?php _e('Permissions Required Message', 'wpsc_members'); ?></td>
                <td>
                    <textarea name='capability_list[<?php echo $capability; ?>][message-details]' cols='80' rows='5' /><?php echo esc_attr( stripslashes( $capability_data['message-details'] ) ); ?></textarea>
                    <p class="description"><?php _e('This is the message that will get displayed to people who do not have the required membership/subscription or are not logged in.', 'wpsc_members'); ?></p>
                </td>
            </tr>
            <!--
            <tr>
                <td><?php _e('Selected by Default', 'wpsc_members') ?>: </td>
                <td colspan="2"><input type='checkbox' name='capability_list[<?php echo $capability ?>][default]' value='true' />
                </td>
            </tr>
            -->
        </table>

        <?php wp_nonce_field( 'edit-capability', 'wpsc-edit-capability' ); ?>
        <input type='hidden' name='wpsc_admin_action' value='capability_action' />
        <input type='hidden' name='page_action' value='add' />
        <input type='hidden' name='capability_list[<?php echo $capability; ?>][owner]' value='<?php echo $user_ID; ?>' />
        <input class='button-primary' type='submit' name='submit' value='<?php _e('Add Membership/Subscription', 'wpsc_members'); ?>' />
    </form>
    <?php
}

/* @TODO use labels!!!!
    formate html and indentation
    add in redirrect
*/
function wpec_members_display_edit_subscription ( $capability ) { ?>

    <div class="wrap">

        <?php wpec_members_display_manage_subscription_tabs(); ?>
        <h2>
            <?php echo esc_html( __('Edit Subscription', 'wpsc_members') ); ?>
            <?php wpec_members_display_add_subscription_button(); ?>
        </h2>
        <?php wpec_members_subscription_form( $capability ); ?>
<!--
        <form class="purchasable-capabilities-form" enctype="multipart/form-data" action="" method="post">
            <p>
                <?php _e('Subscription', 'wpsc_members'); ?>: <?php echo $capability; ?>
            </p>

            <p>
                <?php _e('Display Name', 'wpsc_members') ?>:
                <input name='capability_list[<?php echo $capability; ?>][name]' value='<?php echo esc_attr( stripslashes( $capability_data['name'] ) ); ?>' size='22' />
            </p>
            <p>
                <?php _e('Subscription Permission Message', 'wpsc_members'); ?>: <br />
                <textarea name='capability_list[<?php echo $capability; ?>][message-details]' cols='80' rows='15' /><?php echo esc_attr( stripslashes( $capability_data['message-details'] ) ); ?></textarea><br />
                <?php _e('This is the message that will get displayed to people who do not have the required subscription or are not logged in.', 'wpsc_members'); ?>
            </p>

            <?php wp_nonce_field( 'edit-capability', 'wpsc-edit-capability' ); ?>
            <input type='hidden' name='wpsc_admin_action' value='capability_action' />
            <input type='hidden' name='page_action' value='edit' />
            <input class='button-primary' type='submit' name='submit' value='<?php _e('Edit Subscription', 'wpsc_members'); ?>' />
        </form>
        -->
    </div> <!-- close wrap  -->
    <?php
}


function wpec_members_display_manage_subscriptions_table() {
    $subscription_table = new Subscriptions_List_Table();
    $subscription_table->prepare_items();

    $class="nav-tab";
    ?>
    <div class="wrap">
        <?php wpec_members_display_manage_subscription_tabs(); ?>
        <div style="margin: 10px 0; border: 1px solid #ccc; padding: 0 15px;">
            <h3><?php echo _e("Welcome"); ?></h3>
            <p><?php echo _e("Create a new membership/subscription to restrict content on your website to those who belong or purchase it.", 'wpsc_members'); ?></p>
            <p><?php echo _e("Once you've created a new membership/subscription, go to Products and link a product to your new membership/subscription to sell it to your users.", 'wpsc_members'); ?></p>
        </div>
        <h2>
            <?php echo esc_html( __('Subscriptions', 'wpsc_members') ); ?>
            <?php wpec_members_display_add_subscription_button(); ?>
        </h2>
        <p><?php _e('Listed below are all the membership/subscriptions that can be granted to users by products.', 'wpsc_members'); ?></p>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="members-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
            <!-- Now we can render the completed list table -->
            <?php $subscription_table->display(); ?>
        </form>
    </div>
    <?php
}

function wpec_members_display_manage_members_table() {
    $subscribed_members_table = new Subscribed_Members_List_Table();
    $subscribed_members_table->prepare_items();
    ?>
    <div class="wrap">
        <?php wpec_members_display_manage_subscribers_tabs(); ?>
        <h2>
            <?php _e('Manage Members Subscriptions', 'wpsc_members'); ?>
            <a href="admin.php?page=wpec_members&amp;tab=wpec_add_new_member" class="add-new-h2"><?php _e('Add New', 'wpsc_members'); ?></a>
            <a href="admin.php?page=wpec_members&amp;tab=wpec_import_members" class="add-new-h2"><?php _e('Import', 'wpsc_members'); ?></a>
        </h2>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="members-filter" method="get">
            <div class="tablenav top">
                <p class="searchbox">
					<form method="get">
						<!-- For plugins, we also need to ensure that the form posts back to our current page -->
						<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
						<input type="hidden" name="tab" value="wpec_manage_members" />
						<?php $subscribed_members_table->search_box( __( 'Search Subscriber' ), 'search_subscriber' ); ?>
					</form>
                </p>
                <div class="alignleft actions">
                    <?php _e('Filter by subscription', 'wpsc_members'); ?>
                    <select name="subscription_filter">
                        <option value=""><?php _e('Select a Subscription', 'wpsc_members'); ?></option>
                            <?php
                            $roles = get_option( 'wpsc_product_capability_list' );
                            foreach ( $roles as $role => $key ) {
                            ?>
                                <option value="<?php echo esc_attr( $role );  ?>" <?php if ( $_REQUEST['subscription_filter'] == $role ) { echo('selected="selected"'); } ?>><?php echo esc_attr( $role );  ?></option><?php
                            }
                            ?>
                    </select> <input type="submit" class="button-secondary action" value="<?php _e('Go', 'wpsc_members'); ?>" />
                </div>
            </div>
            <!-- Now we can render the completed list table -->
            <?php $subscribed_members_table->display(); ?>
        </form>
    </div>
<?php
}

function wpec_members_display_add_new_member(){
    global $wpdb;

    $users_sql = "SELECT `ID`, `user_login` FROM " . $wpdb->users . " LIMIT 0,10000";
    $users = $wpdb->get_results( $users_sql );
    //exit('<pre>'.print_r($users,1).'</pre>');
    ?>
    <div class="wrap">
        <?php wpec_members_display_manage_subscribers_tabs(); ?>
        <h2>
            <?php _e('Manage Members Subscriptions', 'wpsc_members'); ?>
            <a href="admin.php?page=wpec_members&amp;tab=wpec_add_new_member" class="add-new-h2"><?php _e('Add New', 'wpsc_members'); ?></a>
            <a href="admin.php?page=wpec_members&amp;tab=wpec_import_members" class="add-new-h2"><?php _e('Import', 'wpsc_members'); ?></a>
        </h2>
        <p><?php _e('Select a site member from the list below to manualy assign a subscription to them.', 'wpsc_members'); ?></p>

        <form id="your-profile-form" enctype="multipart/form-data" method="post" action="">

            <label for="user"><?php _e('User:', 'wpsc_members'); ?></label>
            <select name="add_user_subscription">
                <?php
                $hidden = 0;
                $i = 0;
                foreach ( $users as $user ) {
                    $user_object = new WP_User( $user->ID );
                    //don't want to display admin users
                    if ( $user_object->has_cap( 'administrator' ) ) {
                        $hidden++;
                        continue;
                    }
                    ?>
                    <option value="<?php echo $user->ID; ?>"><?php echo esc_attr( $user->user_login ); ?></option>
                    <?php
                    $i++;
                }?>
            </select>
            <?php
                if ($hidden > 0) { // show hidden user count
                    echo "(";
                    if ($hidden === 1) {
                        _e('1 administrator user hidden', 'wpsc_members');
                    } else {
                        echo sprintf( __('%d administrator users hidden', 'wpsc_members'), $hidden);
                    }
                    echo ")";
                }
            ?>
            <br />

            <label for="role"><?php _e('Subscription Type:', 'wpsc_members'); ?></label>
            <select name="roles">
                <?php
                $roles = get_option( 'wpsc_product_capability_list' );

                foreach ( $roles as $role => $key ) {
                ?>
                    <option value="<?php echo esc_attr( $role ); ?>"><?php echo esc_attr( $role ); ?></option>
                <?php
                }
                ?>
            </select>
            <br />

            <label for="length"><?php _e('Subscription Length:', 'wpsc_members'); ?></label>

            <select name="length">
                <option value="63113852">2 <?php _e('years', 'wpsc_members'); ?></option>
                <option value="31556926">12 <?php _e('months', 'wpsc_members'); ?></option>
                <option value="15778463">6 <?php _e('months', 'wpsc_members'); ?></option>
                <option value="7889231">3 <?php _e('months', 'wpsc_members'); ?></option>
                <option value="2629743">1 <?php _e('month', 'wpsc_members'); ?></option>
            </select>

            <p class="submit">
                <input type="submit" value="<?php _e('Create Subscription', 'wpsc_members'); ?>" class="button-primary" />
                <input type="hidden" name="action" value="create_new" />
            </p>
        </form>
    </div>
<?php
}

/* This is where the importat list class table is going to go! */
function wpec_members_display_import_members(){
    $import_members_table = new Import_Members_List_Table();
    $import_members_table->prepare_items();
    ?>
    <div class="wrap">
        <?php wpec_members_display_manage_subscribers_tabs(); ?>
        <h2>
            <?php _e('Manage Members Subscriptions', 'wpsc_members'); ?>
            <a href="admin.php?page=wpec_members&amp;tab=wpec_add_new_member" class="add-new-h2"><?php _e('Add New', 'wpsc_members'); ?></a>
            <a href="admin.php?page=wpec_members&amp;tab=wpec_import_members" class="add-new-h2"><?php _e('Import', 'wpsc_members'); ?></a>
        </h2>
        <?php _e('Use the bulk options to add subscriptions to your WordPress users, this will import them  your WP-e-Commerce subscribers', 'wpsc_members'); ?> <br />
        <br />
        <div class='tablenav'>
            <form method="get">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
                <input type="hidden" name="tab" value="wpec_import_members" />
                <?php $import_members_table->search_box( __( 'Search Subscriber' ), 'search_subscriber' ); ?>
            </form>
            <form id="bulk_updates" method="post" action="">
                <select name="bulkchange">
                    <option value="0" selected="selected"><?php _e('Bulk Actions', 'wpsc_members'); ?></option>
                    <option value="1"><?php _e('Add Subscription', 'wpsc_members'); ?></option>
                    <option value="2"><?php _e('Remove all Subscriptions', 'wpsc_members'); ?></option>
                </select>

                <select name="roles">
                    <option value=""><?php _e('Select a Subscription', 'wpsc_members'); ?></option>
                    <?php
                    $roles = get_option( 'wpsc_product_capability_list' );
                    foreach ( $roles as $role => $key ) {
                    ?>
                        <option value="<?php echo esc_attr( $role ); ?>"><?php echo esc_attr( $role ); ?></option>
                    <?php
                    }
                    ?>
                </select>

                <select name="length">
                    <option value=""><?php _e('Choose a Length', 'wpsc_members'); ?></option>
                    <option value="63113852">2 <?php _e('years', 'wpsc_members'); ?></option>
                    <option value="31556926">12 <?php _e('months', 'wpsc_members'); ?></option>
                    <option value="15778463">6 <?php _e('months', 'wpsc_members'); ?></option>
                    <option value="7889231">3 <?php _e('months', 'wpsc_members'); ?></option>
                    <option value="2629743">1 <?php _e('month', 'wpsc_members'); ?></option>
                    <option value="657436">1 <?php _e('week', 'wpsc_members'); ?></option>
                </select>
                <input type="submit" value="<?php _e('Apply', 'wpsc_members'); ?>" class="button-secondary" />
                <input type="hidden" name="action" value="bulksave" />

                <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
                <!-- Now we can render the completed list table -->
                <?php $import_members_table->display(); ?>
            </form>
    </div>
    <?php
}

/* @todo tidy this html */
function wpec_members_display_manage_subscribers_tabs() {
    wpec_members_display_manage_tabs('members');
}

function wpec_members_display_manage_settings_tabs() {
    wpec_members_display_manage_tabs('settings');
}

function wpec_members_display_manage_subscription_tabs() {
    wpec_members_display_manage_tabs('subscriptions'); ?>
<?php
}

function wpec_members_display_add_subscription_button() { ?>
    <a href="admin.php?page=wpec_members&amp;tab=wpec_add_subscription" class="add-new-h2"><?php _e('Add new Subscription', 'wpsc_members'); ?></a>
<?php
}

function wpec_members_display_manage_tabs($current_tab) {
?>
    <h2 class="nav-tab-wrapper">
        <div class="icon32" id="icon-members"><br /></div>
        <a href="admin.php?page=wpec_members&amp;tab=wpec_manage_subscriptions" class="nav-tab <?php echo ($current_tab === 'subscriptions' ? 'nav-tab-active' : ''); ?>"><?php _e('Subscriptions', 'wpsc_members'); ?></a>
        <a href="admin.php?page=wpec_members&amp;tab=wpec_manage_members" class="nav-tab <?php echo ($current_tab === 'members' ? 'nav-tab-active' : ''); ?>"><?php _e('Subscribers', 'wpsc_members'); ?></a>
        <a href="admin.php?page=wpec_members&amp;tab=wpec_manage_settings" class="nav-tab <?php echo ($current_tab === 'settings' ? 'nav-tab-active' : ''); ?>"><?php _e('Notifications', 'wpsc_members'); ?></a>
    </h2>
<?php
}

function wpec_members_display_wpec_edit_member() {
    $user_id = $_GET['member'];
    //$starts = get_user_meta( $user_id, '_subscription_starts', true );
    //$cancels = get_user_meta( $user_id, '_subscription_canceled', true);
    $length = get_user_meta( $user_id, '_subscription_ends', true );
    $subscription_length = get_user_meta( $user_id, '_subscription_length', true );
    $user_info = get_userdata( $user_id );
    $current_subscriptions = array_keys( $length );
    $roles = get_option( 'wpsc_product_capability_list' );
    ?>

    <div class="wrap">

    <?php wpec_members_display_manage_subscribers_tabs(); ?>
        <h2><?php _e('Edit Subscriptions for ', 'wpsc_members'); ?> <?php echo ' ' . $user_info->user_login; ?> </h2>
        <?php _e('Here you can edit any of the members subscriptions, update the subscription as you wish then click update subscriptions to save.', 'wpsc_members'); ?>

        <!--
        <p>Starts: <pre><?php print_r( $starts ); ?></pre></p>
        <p>Cancelled: <pre><?php print_r( $cancels ); ?></pre></p>
        <p>Ends: <pre><?php print_r( $length ); ?></pre></p>
        <p>Length: <pre><?php print_r( $subscription_length ); ?></pre></p> 
        -->

        <form id="edit-profile" method="post" action="">
            <input type="hidden" name="page" value="wpec_members" />
            <p>
                <?php _e('User:', 'wpsc_members'); ?>
                <?php echo ' ' . $user_info->user_login; ?>
                <?php if ( ! empty( $user_info->first_name ) && ! empty( $user_info->last_name ) ) {
                    echo "(" . $user_info->first_name . " " . $user_info->last_name . ")";
                } ?>
                <a href="user-edit.php?user_id=<?php echo $user_id; ?>"><?php _e( 'Edit', 'wpsc_members' ); ?></a>
            </p>
            <h3><?php _e( 'Current Subscriptions', 'wpsc_members' ); ?></h3>

            <?php foreach ( $current_subscriptions as $subscription ): ?>
            <?php
            echo '<h4> ' . $subscription . ' </h4>';
            ?>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row">
                            <label for="roles_<?php echo $subscription; ?>"><?php _e( 'Subscription:', 'wpsc_members' ); ?></label>
                        </th>
                        <td>
                            <select name="roles[<?php echo $subscription; ?>]"> <?php
                            foreach ( $roles as $role => $key ) {
                                if ( $subscription == $role ) {
                                ?>
                                    <option selected="selected" value="<?php echo esc_attr( $role ); ?>"><?php echo esc_attr( $role ); ?></option>
                                <?php
                                } else {
                                ?>
                                    <option value="<?php echo esc_attr( $role ); ?>"><?php echo esc_attr( $role ); ?></option>
                                <?php
                                }
                            }?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="length"><?php _e('Subscription Length:', 'wpsc_members'); ?></label>
                        </th>
                        <td>
                            <select name="length">
                                <option <?php selected( $subscription_length[ $subscription ], "63113852" ); ?> value="63113852">2 <?php _e('years', 'wpsc_members'); ?></option>
                                <option <?php selected( $subscription_length[ $subscription ], "31556926" ); ?> value="31556926">12 <?php _e('months', 'wpsc_members'); ?></option>
                                <option <?php selected( $subscription_length[ $subscription ], "15778463" ); ?> value="15778463">6 <?php _e('months', 'wpsc_members'); ?></option>
                                <option <?php selected( $subscription_length[ $subscription ], "7889231" );  ?> value="7889231">3 <?php _e('months', 'wpsc_members'); ?></option>
                                <option <?php selected( $subscription_length[ $subscription ], "2629743" );  ?> value="2629743">1 <?php _e('month', 'wpsc_members'); ?></option>
                                <option <?php selected( $subscription_length[ $subscription ], "0" ); ?> value="0"><?php _e('Deactivate', 'wpsc_members'); ?></option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php endforeach; ?>
            <p class="submit">
                <input type="submit" value="<?php _e('Update Subscription', 'wpsc_members'); ?>" class="button-primary" />
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
            </p>
        </form>
    </div>
    <?php
}

function wpec_members_display_manage_settings() {
    $subscription_end_email = get_site_option( 'subscription_end_email' );
    $subscription_end_email_subject = get_site_option( 'subscription_end_email_subject' );
    $subscription_recurring_email = get_site_option( 'subscription_recurring_email' );
    $subscription_recurring_email_subject = get_site_option( 'subscription_recurring_email_subject' );
    $ipn_error_notification_email = get_site_option( 'ipn_error_notification_email' );
    ?>
    <div class="wrap">
        <?php wpec_members_display_manage_settings_tabs(); ?>
        <h2>
            <?php _e('Notification Emails', 'wpsc_members'); ?>
        </h2>
        <p>
            <?php _e('Configure the subject and content your subscribers will receive under the following circumstances.', 'wpsc_members'); ?>
        </p>
        <p>
            <?php printf( __('Please note that emails are sent via the admin address specified in your Dashboard Settings and sent using the <code>wp_mail()</code> function. If emails are not going through correctly, please consult <a href="%1$s">this documentation</a>.', 'wpsc_members'), 'http://codex.wordpress.org/Function_Reference/wp_mail'); ?>
        </p>
        <p>
            <?php _e('Note: You may use the following tags in your emails: <code>%subscription_number%</code> <code>%purchase_id%</code>, <code>%shop_name%</code>, <code>%find_us%</code>, <code>%product_list%</code>, <code>%total_price%</code>, <code>%total_shipping%</code>, <code>%total_tax%</code>.', 'wpsc_members'); ?>
        </p>
        <form id="members-settings" method="post" action="">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="action" value="update-settings" />

            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e('End of the Subscription', 'wpsc_members'); ?></th>
                        <td><fieldset>
                        <p><?php _e('This is the email your subscribers will recieve the day their subscription ends. <br /><span class="description">(Set the length of the subscription when you set up the subscription product.)</span>', 'wpsc_members'); ?> </p>
                        <p>
                        <?php _e('Subject:', 'wpsc_members'); ?> <input type="text" name="subscription_end_email_subject" id="subscription_end_email_subject" class="large-text" value="<?php echo esc_attr( $subscription_end_email_subject ); ?>"></input>
                        <?php _e('Message:', 'wpsc_members'); ?>
                        <textarea name="subscription_end_email" rows="10" cols="50" id="subscription_end_email" class="large-text code"><?php echo esc_textarea( $subscription_end_email ); ?></textarea>
                        </p>
                        </fieldset></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e("Buyer's Recurring E-mail", "wpsc_members"); ?></th>
                        <td><fieldset>
                        <p><?php _e('This is the e-mail that is sent to a subscriber each time the subscription is billed. <br /><span class="description">(Set how often billing recurs when you set up the subscription product.)</span>', 'wpsc_members'); ?></p>
                        <p>
                        <?php _e('Subject:', 'wpsc_members'); ?> <input type="text" name="subscription_recurring_email_subject" id="subscription_recurring_email_subject" class="large-text" value="<?php echo esc_attr( $subscription_recurring_email_subject ); ?>"></input>
                        <?php _e('Message:', 'wpsc_members'); ?>
                        <textarea name="subscription_recurring_email" rows="10" cols="50" id="subscription_recurring_email" class="large-text code"><?php echo esc_textarea( $subscription_recurring_email ); ?></textarea>
                        </p>
                        </fieldset></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('E-mail for IPN error notification', 'wpsc_members'); ?></th>
                        <td><fieldset>
                        <input type="text" name="ipn_error_notification_email" id="ipn_error_notification_email" class="large-text" value="<?php echo esc_attr( $ipn_error_notification_email ); ?>"></input>
                        <p><?php printf( __('IPN (Instant Payment Notification) is a protocol Paypal uses to tell us when payments are made. If you\'d like to receive an email when there is an IPN error, enter the email address to send it to here. For more information on IPN, read <a href="%1$s">Paypal IPN documentation</a>.', 'wpsc_members'), 'https://www.paypal.com/ipn/'); ?>
                        </p>
                        </fieldset></td>
                    </tr>
                </tbody>
            </table>

            <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save Changes', 'wpsc_members'); ?>">
        </form>

    </div>
<?php
}