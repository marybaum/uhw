<?php
/**
 * WP-Members User List Shortcode Extension Admin Functions
 *
 * Functions to manage administration.
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
 * Actions and filters
 */
add_filter( 'wpmem_admin_tabs', 'wpmem_a_ul_tab' );
add_action( 'wpmem_admin_do_tab', 'wpmem_a_userlist_tab', 1, 1 );
add_action( 'admin_init', 'wpmem_a_update_userlist' );
add_filter( 'plugin_action_links', 'wpmem_ul_plugin_links', 10, 2 ); 


/**
 * Adds User List to the admin tab array
 *
 * @since 1.1
 *
 * @param  array $tabs
 * @return array $tabs the updated array
 */
function wpmem_a_ul_tab( $tabs ) {
	return array_merge( $tabs, array( 'userlist'  => 'User List' ) );
}


/**
 * Builds the User List tab in the admin
 *
 * @since 1.1
 *
 * @param string $tab
 */
function wpmem_a_userlist_tab( $tab ) {
	if( $tab == 'userlist' )
		wpmem_a_build_userlist();
	return;
}


/**
 * Builds the settings tab
 *
 * @since 1.1
 */
function wpmem_a_build_userlist()
{
	$fields_arr   = get_option( 'wpmembers_ul_fields' );
	$fields_arr   = ( ! $fields_arr  ) ? array() : $fields_arr;
	
	$search_arr   = get_option( 'wpmembers_ul_search' );
	$search_arr   = ( ! $search_arr ) ? array() : $search_arr;
	
	$settings_arr = get_option( 'wpmembers_ul_settings' );
	
	// include admin page for generating page list
	include_once( WPMEM_PATH . '/admin/tab-options.php' );
	
	global $did_update;
	
	if( $did_update ) { ?>
		<div id="message" class="updated fade"><p><strong><?php echo $did_update; ?></strong></p></div>
	<?php } ?>

		<div class="metabox-holder has-right-sidebar">

		<div class="inner-sidebar">
			<div class="postbox">
				<h3><span>WP-Members User List Module</span></h3>
				<div class="inside">
					<p><strong><?php _e('Version:', 'wp-members'); ?> <? echo ( defined( 'WPMEM_UL_VERSION' ) ) ? WPMEM_UL_VERSION : '?'; ?></strong><br /></p>
				</div>
			</div>
			<?php wpmem_a_meta_box(); ?>
		</div>	
		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
					<h3><span><?php _e( 'User List Settings', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<p>Choose default display options (individual instances can be overridden with shortcode parameters)</p>
						<form name="update=ul-settings" id="update-ul-settings" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
							<?php wp_nonce_field( 'wpmem-update-ul-settings' ); ?>
							<ul>
								<li>
									<label for="role">Role:</label><?php 
									$roles = get_editable_roles();
									$value = '"All Roles|",';
									foreach( $roles as $key => $role ) {
										$value.= trim( '"' . trim( $role['name'] ) . '|' . trim( $key ) . '"' . "," );
									}
									$value = rtrim( $value, ',' );
									
									// can't use this with PHP <5.3:
									// $arr = str_getcsv( $value, ',', '"' );
									// use this instead for PHP 5.2 compatibility
									if( ! function_exists( 'str_getcsv' ) ) {
										$value = str_replace( '"', '', $value );
										$arr = explode( ',', $value );
									} else {
										$arr = str_getcsv( $value, ',', '"' );
									}
									echo wpmem_create_formfield( 'role', 'select', $arr, $settings_arr['role'] ); ?>
									<span class="description">Choose default role to display</span>
								</li>
								<li>
									<label for="exclude">User IDs to Exclude:</label>
									<?php echo wpmem_create_formfield( 'exclude', 'text', $settings_arr['exclude'], '', 'text' ); ?>
									<span class="description">User IDs to be excluded from the list, separated by commas (123,456,789)</span>
								</li>
								<li>
									<label for="records">Records per page:</label>
									<?php echo wpmem_create_formfield( 'number', 'text', $settings_arr['number'], '', 'small-text' ) ;?>
								</li>
								<li>
									<label for="search">Show search:</label>
									<?php echo wpmem_create_formfield( 'search', 'checkbox', "true", $settings_arr['search'] ); ?>
								</li>
								<li>
									<label for="navigation">Navigation:</label><?php
									$arr = array(
										'No nav links|',
										'No page numbers|true',
										'Include page numbers|page'								
									);
									echo wpmem_create_formfield( 'nav', 'select', $arr, $settings_arr['nav'] ); ?>
								</li>
								<li>
									<label for="avatar">Avatar size:</label>
									<?php echo wpmem_create_formfield( 'avatar', 'text', $settings_arr['avatar'], '', 'small-text' ) ?> 
									<span class="description">Use negative number for no avatar</span>
								</li>
								<li>
									<label for="h2">Heading value:</label><?php
									$arr = array(
										'None|',
										'Username|user_login',
										'Display Name|display_name',
										'Nicename|user_nicename',
										'Email|user_email',
										'First Name / Last Name|first_last',
										'Last Name / First Name|last_first'										
									);
									echo wpmem_create_formfield( 'h2', 'select', $arr, $settings_arr['h2'] ); ?>
								</li>
								<li>
									<label for="order_by">Order by:</label><?php
									$arr = array(
										'Username|user_login',
										'Display Name|display_name',
										'Nicename|user_nicename',
										'Email|user_email',
										'User Meta|meta_value'
									);
									echo wpmem_create_formfield( 'order_by', 'select', $arr, $settings_arr['order_by'] );
									$wpmem_fields = get_option( 'wpmembers_fields' ); 
									$arr = array(
										'Order by Meta:|',
										'First Name|first_name',
										'Last Name|last_name',
									);
									foreach ( $wpmem_fields as $field ) {	
										if( $field[6] != 'y' ) {
											$arr[] = $field[1] . '|' . $field[2];
										}
									}
									echo wpmem_create_formfield( 'meta_key', 'select', $arr, $settings_arr['meta_key'] );
									$arr = array(
										'Sort Order:|',
										'Ascending|ASC',
										'Decending|DESC'
									);
									echo wpmem_create_formfield( 'order', 'select', $arr, $settings_arr['order'] ); ?>
								<li>
									<label for="show_labels">Show labels:</label>
									<select name="show_titles">
										<option value="true" <?php echo wpmem_selected( $settings_arr['show_titles'], "true", 'select' ); ?>>Yes</option>
										<option value="false" <?php echo wpmem_selected( $settings_arr['show_titles'], "false", 'select' ); ?>>No</option>
										<option value="meta" <?php echo wpmem_selected( $settings_arr['show_titles'], 'meta', 'select' ); ?>>Meta</option>
									</select>
									<span class="description">select meta to use meta keys as the label</span>
								</li>
								<li>
									<label for="show_labels">Show empty:</label>
									<?php echo wpmem_create_formfield( 'show_empty', 'checkbox', "true", $settings_arr['show_empty'] ); ?>
									<span class="description">show fields even if they are empty</span>
								</li>
								<li>
									<label for="profile_page">Profile page:</label>
									<select name="profile_page">
									<?php wpmem_admin_page_list( $settings_arr['profile_page'], false ); ?>
									</select>
									<span class="description">Optional page for displaying detailed profiles</span>
								</li>
								<li class="ul-update">
									<input type="hidden" name="wpmem_admin_a" value="update_ul_settings" />
									<input type="submit" name="update" class="button-primary" value="<?php _e( 'Update' ); ?> &raquo;" />
								</li>
							</ul>
						</form>
					</div><!-- .inside -->
				</div>
				<?php $wpmem_fields = get_option( 'wpmembers_fields' ); ?>
				<div class="postbox">
					<h3><span><?php _e( 'User List Display Fields', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<form name="updateuserlist" id="updateuserlist" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
						<?php wp_nonce_field( 'wpmem-update-ul-fields' ); ?>
							<table class="form-table">
								<tr>
									<td>List</td>
									<td>Search</td>
									<td>Field Name</td>
								</tr>
								<tr class="alternate" width="400">
									<td width="10"><?php echo wpmem_create_formfield( "ul_fields[]", 'checkbox', 'user_login', ( ( in_array( 'user_login', $fields_arr ) ) ? 'user_login' : '' ) ); ?></td>
									<td width="10"><?php echo wpmem_create_formfield( "ul_search[]", 'checkbox', 'Username|user_login', ( ( in_array( 'Username|user_login', $search_arr ) ) ? 'Username|user_login' : '' ) ); ?></td>
									<td>Username</td>
								</tr>	
								<tr class="" width="400">
									<td width="10"><?php echo wpmem_create_formfield( "ul_fields[]", 'checkbox', 'display_name', ( ( in_array( 'display_name', $fields_arr ) ) ? 'display_name' : '' ) ); ?></td>
									<td width="10"><?php echo wpmem_create_formfield( "ul_search[]", 'checkbox', 'Display Name|display_name', ( ( in_array( 'Display Name|display_name', $search_arr ) ) ? 'Display Name|display_name' : '' ) ); ?></td>
									<td>Display Name (WP native)</td>
								</tr>									
						<?php
							$class = '';
							for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
								$chk   = ( in_array( $wpmem_fields[$row][2], $fields_arr ) ) ? $wpmem_fields[$row][2] : '';
								$chk2  = ( in_array( $wpmem_fields[$row][1] . '|' . $wpmem_fields[$row][2], $search_arr ) ) ? $wpmem_fields[$row][1] . '|' . $wpmem_fields[$row][2] : '';
								$class = ( $class == 'alternate' ) ? '' : 'alternate'; ?>
								<tr class="<?php echo $class; ?>" width="400">
									<td width="10"><?php echo wpmem_create_formfield( "ul_fields[]", 'checkbox', $wpmem_fields[$row][2], $chk ); ?></td>
									<td width="10"><?php echo wpmem_create_formfield( "ul_search[]", 'checkbox', $wpmem_fields[$row][1] . '|' . $wpmem_fields[$row][2], $chk2 ); ?></td>
									<td><?php echo $wpmem_fields[$row][1]; ?></td>
								</tr>
						<?php } ?>
								<tr class="<?php echo ( $class == 'alternate' ) ? '' : 'alternate'; ?>" width="400">
									<td width="10"><?php echo wpmem_create_formfield( "ul_fields[]", 'checkbox', 'user_registered', ( ( in_array( 'user_registered', $fields_arr ) ) ? 'user_registered' : '' ) ); ?></td>
									<td width="10">&nbsp;</td>
									<td>User Registered (WP native "user_registered")</td>
								</tr>
								<tr class="<?php echo ( $class == '' ) ? 'alternate' : ''; ?>" width="400">
									<td width="10"><?php echo wpmem_create_formfield( "ul_fields[]", 'checkbox', 'member_since', ( ( in_array( 'member_since', $fields_arr ) ) ? 'member_since' : '' ) ); ?></td>
									<td width="10">&nbsp;</td>
									<td>Members Since (displayed as Member since: August 27, 2013, 9:18 pm)</td>
								</tr>
								<tr>
									<td colspan="3">
										<input type="hidden" name="wpmem_admin_a" value="update_ul_fields" />
										<input type="submit" name="update" class="button-primary" value="<?php _e( 'Update' ); ?> &raquo;" />
									</td>
								</tr>
							</table>
						</form>
					</div><!-- .inside -->
				</div>
			</div><!-- #post-body-content -->
		</div><!-- #post-body -->
	</div><!-- .metabox-holder -->	
	<?php
}


/**
 * Updates the User List add-on settings
 *
 * @since 1.1
 *
 * @return string $did_update Contains the update message
 */
function wpmem_a_update_userlist()
{
	global $did_update;
	
	if( isset( $_POST['wpmem_admin_a'] ) ) {
	
		if( ( $_POST['wpmem_admin_a'] == 'update_ul_fields' ) ) {
		
			// check nonce
			check_admin_referer('wpmem-update-ul-fields');
			
			// update main list fields
			$ul_fields = ( isset( $_POST['ul_fields'] ) ) ? $_POST['ul_fields'] : '';
			update_option( 'wpmembers_ul_fields', $ul_fields  );
			
			// update search by fields
			$ul_search = ( isset( $_POST['ul_search'] ) ) ? $_POST['ul_search'] : '';
			update_option( 'wpmembers_ul_search', $ul_search );

			$did_update = __( 'WP-Members User List fields were updated', 'wp-members' );
		
		}
		
		if( ( $_POST['wpmem_admin_a'] == 'update_ul_settings' ) ) {
		
			// check nonce
			check_admin_referer('wpmem-update-ul-settings');
			
			$settings = array(
				'version'      => WPMEM_UL_VERSION,
				'role'         => $_POST['role'],
				'exclude'      => ( isset( $_POST['exclude'] ) ) ? $_POST['exclude'] : '',
				'number'       => ( isset( $_POST['number'] ) ) ? $_POST['number'] : '10',
				'search'       => ( isset( $_POST['search'] ) ) ? $_POST['search'] : "false",
				'search_by'    => '',
				'nav'          => ( isset( $_POST['nav'] ) ) ? $_POST['nav'] : "false",
				'avatar'       => ( isset( $_POST['avatar'] ) ) ? $_POST['avatar'] : '45',
				'h2'           => $_POST['h2'],
				'order_by'     => $_POST['order_by'],
				'order'        => $_POST['order'],
				'show_titles'  => $_POST['show_titles'],
				'show_empty'   => ( isset( $_POST['show_empty'] ) ) ? $_POST['show_empty'] : "false",
				'profile_page' => ( isset( $_POST['profile_page'] ) ) ? $_POST['profile_page'] : '',
				'meta_key'     => ( isset( $_POST['meta_key'] ) ) ? $_POST['meta_key'] : '',
			);
			
			update_option( 'wpmembers_ul_settings', $settings );

			$did_update = __( 'WP-Members User List settings were updated', 'wp-members' );
		
		}
		
		return $did_update;
	}
}


/**
 * filter to add link to settings from plugin panel
 *
 * @since 1.1
 *
 * @param  array  $links
 * @param  string $file
 * @static string $wpmem_plugin
 * @return array  $links
 */
function wpmem_ul_plugin_links( $links, $file )
{
	static $wpmem_ul_plugin;
	if( !$wpmem_ul_plugin ) $wpmem_ul_plugin = plugin_basename( 'wp-members-user-list/wp-members-user-list.php' );
	if( $file == $wpmem_ul_plugin ) {
		$settings_link = '<a href="options-general.php?page=wpmem-settings&tab=userlist">' . __( 'Settings' ) . '</a>';
		$links = array_merge( array( $settings_link ), $links );
	}
	return $links;
}

/** End of File */