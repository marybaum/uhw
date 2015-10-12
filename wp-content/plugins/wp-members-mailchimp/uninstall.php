<?php
/**
 * WP-Members MailChimp Add-on Uninstall
 *
 * Removes settings for the WP-Members MailChimp Addon from the WP options table
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2012  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2012
 */

/**
 * If uninstall is not called from WordPress, kill the uninstall
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( 'invalid uninstall' );
}
 
/**
 * Uninstall process removes WP-Members MailChimp Add-on 
 * settings from the WordPress database (_options table)
 */
if ( WP_UNINSTALL_PLUGIN ) {

	delete_option( 'wpmembers_mailchimp' );
	
}
?>