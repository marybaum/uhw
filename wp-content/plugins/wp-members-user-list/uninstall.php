<?php
/**
 * WP-Members User List Shortcode Extension Uninstall
 *
 * Removes all settings WP-Members User List Shortcode Extension added to the WP options table
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2013  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2013
 */

/**
 * If uninstall is not called from WordPress, kill the uninstall
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( 'invalid uninstall' );
}
 
/**
 * Uninstall process removes WP-Members settings from the WordPress database (_options table)
 */
if ( WP_UNINSTALL_PLUGIN ) {

	delete_option( 'wpmembers_ul_fields'   );
	delete_option( 'wpmembers_ul_search'   );
	delete_option( 'wpmembers_ul_settings' );

}

/** End of File **/