<?php
/*
Plugin Name: WP-Members MailChimp Module
Plugin URI:  http://rocketgeek.com
Description: Integrates the WP-Members plugin with your MailChimp list (WP-Members must be installed and activated)
Version:     1.1
Author:      Chad Butler
*/


/*
	This file is a premium module for the WP-Members plugin by Chad Butler
	You can find out more about the WP-Members plugin at http://rocketgeek.com
	Copyright (c) 2006-2012  Chad Butler (email : plugins@butlerblog.com)
	WP-Members(tm) is a trademark of butlerblog.com
*/


/**
 * Constants
 */
define( 'WPMEM_MC_VERSION', '1.1' );
define( 'WPMEM_MC_PATH', plugin_dir_path( __FILE__ ) );


/**
 * Add Actions and Filters
 */
add_action( 'init',                     'wpmem_mc_chk_admin' );
add_action( 'wpmem_post_register_data', 'wpmem_mailchimp_subscribe', 1, 1 );
add_action( 'wpmem_pre_update_data',    'wpmem_mailchimp_update_check' );
add_action( 'wpmem_post_update_data',   'wpmem_mailchimp_update', 1, 1 );


/**
 * Checks if the user is an admin and loads admin functions
 */
function wpmem_mc_chk_admin() {
	if( current_user_can( 'activate_plugins' ) ) { include_once( 'admin/admin.php' ); }
}


/**
 * Subscribe to MailChimp List
 *
 * Subscribes a user to a MailChimp list if they have checked
 * the mc_subscribe box (passing a value of 'subscribe').
 * The function needs to have your MailChimp API key and
 * your list ID.  Also, you can add or subtract fields to 
 * pass to MailChimp in $merge_vars.
 *
 * @since 1.0
 *
 * @param $fields  array  The array of fields passed from wpmem_register
 * @var   $api_key string Your MailChimp API Key
 * @var   $list_id string Your MailChimp List ID
 */
function wpmem_mailchimp_subscribe( $fields )
{
	extract( wp_parse_args( '', get_option( 'wpmembers_mailchimp' ) ) );

	if( $fields[$sub_field] == $sub_value ) {

		/** Load the MCAPI class if it not already loaded */
		if( ! class_exists( 'MCAPI' ) )
			require_once 'MCAPI.class.php';
		
		$api = new MCAPI( $api_key );

		/** 
		 * Filter the merge_vars.
		 *
		 * @since 1.0
		 *
		 * @param array An array with MC merge fields as the key, $fields as the value.
		 */
		$merge_vars = apply_filters( 'wpmem_mc_merge', array( 'FNAME' => $fields['first_name'], 'LNAME' => $fields['last_name'] ) );

		/** Subscribe the user based on settings. */
		$retval = $api->listSubscribe( $list_id, $fields['user_email'], $merge_vars, 'html', $double_optin, true, false, $send_welcome );

		/** Error checking - displays when WPMEM_DEBUG is turned on */
		if( WPMEM_DEBUG && $api->errorCode ){
			echo "Unable to load listSubscribe()! \n";
			echo "\tCode=" . $api->errorCode . "\n";
			echo "\tMsg=" . $api->errorMessage . "\n";
		}
	}
}


/**
 * Check if MailChimp settings are being changed
 *
 * When on the user update page, checks to see if the 
 * user is updating their subscription setting and if
 * so, will allow wpmem_mailchimp_update to update 
 * the user's subscription settings.
 *
 * @since 1.0
 *
 * @param $fields  array  The array of fields
 */
function wpmem_mailchimp_update_check( $fields )
{
	/** set globals for wpmem_mailchimp_update */
	global $current_user, $chk_email, $chk_fname, $chk_lname, $chk_subscribe;
	
	/** get the current user's user info */
    get_currentuserinfo();
	
	/** put the current info in the global chk variables */
	$chk_email = $current_user->user_email;
	$chk_fname = $current_user->user_firstname;
	$chk_lname = $current_user->user_lastname;
}


/**
 * Update a user's MailChimp subscription
 *
 * If the wpmem_mailchimp_update_check indicates the user
 * is updating their settings, update accordingly.
 *
 * @since 1.0
 *
 * @param $fields  array  The array of fields
 */
function wpmem_mailchimp_update( $fields )
{
	/** set globals from wpmem_mailchimp_update_check */
	global $chk_email, $chk_fname, $chk_lname, $chk_subscribe;
	
	/** get the options */
	extract( wp_parse_args( '', get_option( 'wpmembers_mailchimp' ) ) );
	
	/** Load the MCAPI class if it not already loaded */
	if( ! class_exists( 'MCAPI' ) )
		require_once 'MCAPI.class.php';

	/** invoke the $api object */
	$api = new MCAPI( $api_key );

	/**
	 * you need a checkbox with the option name
	 * mc_subscribe and a checked value of subscribe.
	 */
	if( $fields[$sub_field] == '' ) {

		/** If mc_subscribe is empty, the user is unsubscribing */
		$retval = $api->listUnsubscribe( $list_id, $fields['user_email'] );

	} elseif( ( $chk_fname != $fields['first_name'] ) ||  ( $chk_lname != $fields['last_name'] )  ) {
	
		/** 
		 * Filter the merge_vars.
		 *
		 * @since 1.0
		 *
		 * @param array An array with MC merge fields as the key, $fields as the value.
		 */
		$merge_vars = apply_filters( 'wpmem_mc_merge', array( 'FNAME' => $fields['first_name'], 'LNAME' => $fields['last_name'] ) );
		
		$retval = $api->listUpdateMember( $list_id, $fields['user_email'], $merge_vars );
		
	} elseif( ( $chk_email != $fields['user_email'] ) || ( $chk_subscribe != $fields[$sub_field] ) ) {
	
		/** User is changing their email, or is a new subscription */

		/** 
		 * Filter the merge_vars.
		 *
		 * @since 1.0
		 *
		 * @param array An array with MC merge fields as the key, $fields as the value.
		 */
		$merge_vars = apply_filters( 'wpmem_mc_merge', array( 'FNAME' => $fields['first_name'], 'LNAME' => $fields['last_name'] ) );

		$retval = $api->listSubscribe( $list_id, $fields['user_email'], $merge_vars, 'html', $double_optin, true, false, $send_welcome );
		
		/** if changing email, unsubscribe the previous email */
		if( $chk_email != $fields['user_email'] ) { $retval = $api->listUnsubscribe( $list_id, $chk_email ); }
	}
}


/** End of File **/