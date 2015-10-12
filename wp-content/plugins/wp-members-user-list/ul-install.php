<?php
/**
 * WP-Members User List Shortcode Extension Install
 *
 * Installs default settings for WP-Members User List Shortcode Extension
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


function wpmem_ul_do_install()
{
	if( ! get_option( 'wpmembers_ul_settings' ) ) {

		$defaults = array(
			'version'      => WPMEM_UL_VERSION,
			'role'         => 'subscriber',
			'exclude'      => '',
			'number'       => '10',
			'search'       => "true",
			'search_by'    => '',
			'nav'          => "true",
			'avatar'       => 45,
			'h2'           => 'first_last',
			'order_by'     => 'user_login',
			'order'        => 'ASC',             // can be ASC/DESC
			'show_titles'  => "false",           // can be yes/no/meta
			'show_empty'   => "true",            // keeps empty fields in display
			'meta_key'     => '',
			'profile_page' => ''
		);
		
		update_option( 'wpmembers_ul_settings', $defaults );
	} else {
	
		// updating 1.3 requires adding an array value for 'exclude',
		// updating 1.4 requires adding profile_page, 'order', and 'meta_key'
		// and updating the version number
		
		$defaults = get_option( 'wpmembers_ul_settings' );
		
		if( ! isset( $defaults['exclude'] ) ) {
			$defaults['exclude'] = '';
		}
		
		if( ! isset( $defaults['order'] ) ) {
			$defaults['order'] = '';
		}
		
		if( ! isset( $defaults['meta_key'] ) ) {
			$defaults['meta_key'] = '';
		}
		
		if( ! isset( $defaults['profile_page'] ) ) {
			$defaults['profile_page'] = '';
		}
		
		$defaults['version'] = WPMEM_UL_VERSION;
		
		update_option( 'wpmembers_ul_settings', $defaults );
		
	}
}

/** End of File **/