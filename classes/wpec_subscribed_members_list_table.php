<?php
/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}



class Subscribed_Members_List_Table extends WP_List_Table {


    function __construct() {
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'member',     //singular name of the listed records
            'plural'    => 'members',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

    }

   function column_username( $item ) {

        //Build row actions
        $actions = array(
            'delete_all'        => sprintf( '<a href="?page=%s&action=%s&member=%s">' . __('Remove All Subscriptions', 'wpsc_members') . '</a>', $_REQUEST['page'], 'delete_all', $item['ID'] ),
            'edit_subscription' => sprintf( '<a href="?page=%s&action=%s&member=%s&tab=edit_member">' . __('Edit Subscriptions', 'wpsc_members') . '</a>', $_REQUEST['page'], 'edit', $item['ID'] ),
        );

        //Return the title contents
        return $item['username'] . get_avatar( $item['ID'], 32 ). $this->row_actions( $actions );

    }


    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("member")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }


    function column_default( $item, $column_name ) {

        $array_data = $item[ $column_name ];

            $return = '';
            foreach ( $array_data as $data )
                $return .= '<p>' . $data . '</p>';

            return $return;

    }

    function column_name( $item ) {

        return $item['name'];

    }


    function get_columns() {
        $columns = array(
            'cb'                        => '<input type="checkbox" />', //Render a checkbox instead of text
            'username'                  => __('Username', 'wpsc_members'),
            'name'                      => __('Name', 'wpsc_members'),
            'subscription'              => __('Subscription', 'wpsc_members'),
            'subscription_length'       => __('Subscription Length', 'wpsc_members'),
            'subscription_remaining'    => __('Subscription Remaining', 'wpsc_members'),
            'expriry'                   => __('Expiry Date', 'wpsc_members'),
            'status'                    => __('Status', 'wpsc_members'),
        );
        return $columns;
    }

    function get_sortable_columns() {
        global $wpdb;

        $sortable_columns = array(
            'username'                  => array( "user_login", true ),     //true means its already sorted
            'name'                      => array( "name" ),
            'subscription'              => array( "subscription" ),
            'subscription_length'       => array( "subscription_length" ),
            'subscription_remaining'    => array( "subscription_remaining" ),
            'expriry'                   => array( "expriry" ),
            'status'                    => array( "status" ),
        );
        return $sortable_columns;
    }

    function process_bulk_action() {
        $user_id = $_GET['member'];
        if( 'delete_all' === $this->current_action() )
            wpec_members_remove_all_capabilities( $user_id );
    }

    function prepare_items() {

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 40;

        $this->process_bulk_action();

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();



        $this->_column_headers = array( $columns, $hidden, $sortable );
        $current_page = $this->get_pagenum();

        /* work out the query limits */
        $end_limit = $per_page;
        $start_limit = ( ( $current_page - 1 ) * $per_page );


        /* set a default order currently we can only sort by username */
         $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'user_login'; //If no sort, default to title
         $order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc

        $this->orderby = $orderby;
        $this->order = $order;

        $where = ( ! empty( $_REQUEST['subscription_filter'] ) ) ? "`meta_key`='_subscription_ends' AND `meta_value` LIKE '%\"" . esc_attr( $_REQUEST['subscription_filter'] ) . "\"%'" : "`meta_key` = '_has_current_subscription' AND `meta_value` = 'true'";

        if ( ! empty( $_GET['s'] ) ) {
            $where .= " AND `user_login` LIKE '%" . $_GET['s'] . "%'";
        }

        global $wpdb;

        $query = "SELECT `ID`, `user_login` FROM " . $wpdb->users . " LEFT JOIN " . $wpdb->usermeta . " ON " . $wpdb->users . ".ID = " . $wpdb->usermeta . ".user_id WHERE " . $where . " ORDER BY user_login " . $order . " LIMIT " . $start_limit . "," . $end_limit;

        $users = $wpdb->get_results( $query );

        /* build a nice array of userdata */
        $data = array();
        $i = 0;

        foreach( $users as $user ) {

            $user_id = $user->ID;
            $fname =  $user_last = get_user_meta( $user_id, 'first_name', true );
            $lname =  $user_last = get_user_meta( $user_id, 'last_name', true );

            if ( $fname == '' && $lname == '' )
                $name = __('Not provided', 'wpsc_members');
            else $name = $fname . " " . $lname;

            $name = apply_filters( 'wpsc_members_name', $name, $user_id );

            $subscription_ends = get_user_meta( $user_id, '_subscription_ends', true );
            $subscription_length = get_user_meta( $user_id, '_subscription_length', true );


            $expiry = array();
            $subscription_remaining = array();
            $length = array();
            $status = array();
            $time = time();

            foreach ( $subscription_ends as $end ) {
                if ( $end == 'never' )
                    $expiry[] = $end;
                else
                    $expiry[] = date( "F j Y", $end );

                if ( ( $end < $time ) && ( $end != 'never' ) ) {
                    $status[] = __('Expired', 'wpsc_members');

                    $subscription_remaining[] = 0;
                }
                else
                {
                    $status[] = __('Active', 'wpsc_members');
                    /* this will need to be changed */
                    $subscription_remaining[] = vl_wpscsm_time_duration( intval( $end ) - $time, 'yMw' );
                }

            }
            $subscriptions = array_keys( $subscription_ends );

            $length = display_subscription_length( $subscription_length );

            $subscription_remaining = display_subscription_remaining( $subscription_remaining );

            $data[$i]['ID'] = $user_id;
            $data[$i]['username'] = $user->user_login;
            $data[$i]['name'] = $name;
            $data[$i]['subscription'] = $subscriptions;
            $data[$i]['subscription_length'] = $length;
            $data[$i]['subscription_remaining'] = $subscription_remaining;
            $data[$i]['expriry'] = $expiry;
            $data[$i]['status'] = $status;

            $i++;
        }

        /* this is the toatal items used for pagination */
        $query = "SELECT COUNT(`umeta_id`) FROM " . $wpdb->usermeta . " WHERE `meta_key` = '_has_current_subscription'";
        $total_items = $wpdb->get_var( $query );

        if ( $orderby != "user_login" ) {
            usort( $data, array( "Subscribed_Members_List_Table", "sort_data" ) );
        }

       /* set the sorted data items for the table display */
        $this->items = $data;

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil( $total_items/$per_page )   //WE have to calculate the total number of pages
        ) );
    }

    function sort_data( $data1, $data2 ) {
        if ( $data1[ $this->orderby ] == $data2[ $this->orderby ] ) {
            return 0;
        }

        if ( $this->orderby == "subscription_length" ) {
            if ( ! empty( $data1[ $this->orderby ] ) ) {
                foreach ( $data1[ $this->orderby ] as $key => $value ) {
                    if ( $value != "1 year" && $value != "2 years" ) {
                        $data1[ $this->orderby ][ $key ] = $data1[ $this->orderby ][ $key ] / 12;
                    }
                }
            }

            if ( ! empty( $data2[ $this->orderby ] ) ) {
                foreach ( $data2[ $this->orderby ] as $key => $value ) {
                    if ( $value != "1 year" && $value != "2 years" ) {
                        $data2[ $this->orderby ][ $key ] = $data2[ $this->orderby ][ $key ] / 12;
                    }
                }
            }
        } elseif ( $this->orderby == "expriry" ) {
            if ( ! empty( $data1[ $this->orderby ] ) ) {
                foreach ( $data1[ $this->orderby ] as $key => $value ) {
                    $data1[ $this->orderby ][ $key ] = strtotime( $value );
                }
            }

            if ( ! empty( $data2[ $this->orderby ] ) ) {
                foreach ( $data2[ $this->orderby ] as $key => $value ) {
                    $data2[ $this->orderby ][ $key ] = strtotime( $value );
                }
            }
        }

        if ( $this->order == "desc" ) {
            if ( $this->orderby == "user_login" || $this->orderby == "name" ) {
                return ( $data1[ $this->orderby ] < $data2[ $this->orderby ] ) ? 1 : -1;
            }else{
                return ( max( $data1[ $this->orderby ] ) < max( $data2[ $this->orderby ] ) ) ? 1 : -1;
            }
        }else{
            if ( $this->orderby == "user_login" || $this->orderby == "name" ) {
                return ( $data1[ $this->orderby ] < $data2[ $this->orderby ] ) ? -1 : 1;
            }else{
                return ( min( $data1[ $this->orderby ] ) < min( $data2[ $this->orderby ] ) ) ? -1 : 1;
            }
        }
    }

}

function display_subscription_length( $subs ) {
    $results = array();
    foreach ( $subs as $key => $sub )   {
        $sub = intval( $sub );

        $results[] = my_time_duration( $sub );
    }
    return $results;
}

function display_subscription_remaining( $subs ) {
    $results = array();
    foreach ( $subs as $key => $sub ) {
        $sub = intval( $sub );

        if ( ( $sub == 0 ) || ( $sub < 0 ) )
            $results[] = '&#8734;';
        else
            $results[] = $sub;
    }
    return $results;
}

function my_time_duration( $v, $format = 'yMw' ) {
    if ( ( $v == 1336950338 ) || ( $v < 0 ) )
        return '&#8734;';

    //return $v;
    return vl_wpscsm_time_duration( $v, $format );

}