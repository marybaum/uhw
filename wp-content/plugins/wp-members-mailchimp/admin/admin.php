<?php
/**
 * WP-Members MailChimp Extension Admin Functions
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
 * Actions and Filters
 */
add_filter( 'wpmem_admin_tabs',   'wpmem_a_mc_tab' );
add_action( 'wpmem_admin_do_tab', 'wpmem_a_mailchimp_tab', 1, 1 );
add_action( 'admin_init',         'wpmem_a_update_mailchimp' );
add_action( 'delete_user',        'wpmem_a_mc_del_user' );

/**
 * Adds MailChimp to the admin tab array
 *
 * @since 1.0
 *
 * @param  array $tabs
 * @return array $tabs the updated array
 */
function wpmem_a_mc_tab( $tabs ) {
	return array_merge( $tabs, array( 'mailchimp'  => 'MailChimp' ) );
}


/**
 * Builds the MailChimp tab in the admin
 *
 * @since 1.0
 *
 * @param string $tab
 */
function wpmem_a_mailchimp_tab( $tab ) {
	if( $tab == 'mailchimp' )
		wpmem_a_build_mailchimp();
	return;
}


/**
 * Builds the settings tab
 *
 * @since 1.0
 */
function wpmem_a_build_mailchimp()
{
	$defaults = array( 
		'api_key'      => '', 
		'list_id'      => '', 
		'sub_field'    => '', 
		'sub_value'    => '', 
		'double_optin' => true, 
		'send_welcome' => false,
		'user_delete'  => 0
	);
	/** merge settings with defaults and extract **/
	extract( wp_parse_args( get_option( 'wpmembers_mailchimp' ), $defaults ) );
	
	global $did_update;
	
	if( $did_update ) { ?>
		<div id="message" class="updated fade"><p><strong><?php echo $did_update; ?></strong></p></div>
	<?php } ?>

		<div class="metabox-holder has-right-sidebar">

		<div class="inner-sidebar">
			<div class="postbox">
				<h3><span>WP-Members MailChimp Extension</span></h3>
				<div class="inside">
					<p><strong><?php _e('Version:', 'wp-members'); ?> <?php echo WPMEM_MC_VERSION; ?></strong><br /></p>
				</div>
			</div>
			<?php wpmem_a_meta_box(); ?>
		</div>	

		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
					<h3><span><?php _e( 'MailChimp Settings', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<form name="updatesettings" id="updatesettings" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
							<ul>
								<li>
									<label>MailChimp API Key:</label>
									<?php echo wpmem_create_formfield( 'api_key', 'text', $api_key, '', 'regular-text code' ); ?>
								</li>
								<li>
									<label>List ID:</label>
									<?php echo wpmem_create_formfield( 'list_id', 'text', $list_id, '', 'regular-text code' ); ?>
								</li>
								<li>
								<li>
									<label>Subscribe field:</label>
									<?php
									$wpmem_fields = get_option( 'wpmembers_fields' ); $chk = false; $arr = '';
									for( $row = 0; $row < count($wpmem_fields); $row++ ) {
										if( $wpmem_fields[$row][3] == 'checkbox' && $wpmem_fields[$row][2] != 'tos' ) {
											$arr.= '<option value="' . $wpmem_fields[$row][2] . '" ' 
												. wpmem_selected( $wpmem_fields[$row][2], $sub_field, 'select' ) . '>' 
												.  $wpmem_fields[$row][1] . '</option>';
											$chk = true;
										}
									}
									if( $chk ) {
										echo '<select name="sub_field">' . $arr . '</select>';
									} else {
										echo 'No checkbox fields have been defined. Define one in the <a href="' 
											. get_admin_url() . 'options-general.php?page=wpmem-settings&tab=fields">fields tab</a>.';
									}
									?>
								</li>
								<li>
									<label>Double Opt-in</label>
									<?php echo wpmem_create_formfield( 'double_optin', 'checkbox', true, $double_optin, 'regular-text code' ); ?>
									<span class="description">Controls whether a double opt-in confirmation message is sent.</span>
								</li>
								<li>
									<label>Send Welcome</label>
									<?php echo wpmem_create_formfield( 'send_welcome', 'checkbox', true, $send_welcome, 'regular-text code' ); ?>
									<span class="description">If double_optin is off and this is on, MC will send your list's Welcome Email.</span>
								</li>
								<li>
									<label>When a WP user is deleted:</label><?php
									$arr = array(
										'Do nothing|0',
										'Delete the user from MailChimp|1',
										'Unsubscribe the user from MailChimp|3',
										'Unsubscribe the user from MailChimp and send notification to user|4'								
									);
									echo wpmem_create_formfield( 'user_delete', 'select', $arr, $user_delete ); ?>
								</li>
								<li>
									<label>&nbsp;</label>
									<input type="hidden" name="wpmem_admin_a" value="update_mc" />
									<input type="submit" name="save"  class="button-primary" value="<?php _e( 'Update', 'wp-members' ); ?> &raquo;" />
								</li>
							</ul>
						</form>
					</div><!-- .inside -->
				</div>
				<div class="postbox">
					<h3><span><?php _e( 'MailChimp Merge Fields', 'wp-members' ); ?></span></h3>
					<div class="inside">
					<p>Do you have mail merge fields in MailChimp? Do you have mail merge fields that are required fields for MailChimp?</p>
					<p>The WP-Members MailChimp Extension passes FNAME and LNAME as default merge fields. If you have additional merge fields, you can add those with the wpmem_mc_merge filter.  If you have merge fields that are set as required, you must add those with the wpmem_mc_merge filter or the signup process will not complete and the user will not be added to your MailChimp list.</p>
					<p>See the <a href="http://rocketgeek.com/plugins/wp-members/users-guide/filter-hooks/wpmem_mc_merge/">documentation for information on using the wpmem_mc_merge filter</a>.</p>
					</div><!-- .inside -->
				</div>
			</div><!-- #post-body-content -->
		</div><!-- #post-body -->
	</div><!-- .metabox-holder -->	
	<?php
}


/**
 * Updates the MailChimp add-on settings
 *
 * @since 1.0
 *
 * @return string $did_update Contains the update message
 */
function wpmem_a_update_mailchimp()
{
	if( isset( $_POST['wpmem_admin_a'] ) && ( $_POST['wpmem_admin_a'] == 'update_mc' ) ) {
	
		global $did_update;
		
		//check nonce
		//check_admin_referer('wpmem-update-exp');
		
		$sub_field    = ( isset( $_POST['sub_field'] ) ) ? $_POST['sub_field'] : '';
		$wpmem_fields = get_option( 'wpmembers_fields' );
		
		for( $row = 0; $row < count($wpmem_fields); $row++ )
			if( $wpmem_fields[$row][2] == $sub_field )
				$sub_value = $wpmem_fields[$row][7];
		
		$wpmem_mc_settings_arr = array(
			'api_key'      => ( isset( $_POST['api_key'] ) ) ? $_POST['api_key'] : '',
			'list_id'      => ( isset( $_POST['list_id'] ) ) ? $_POST['list_id'] : '',
			'sub_field'    => $sub_field,
			'sub_value'    => $sub_value,
			'double_optin' => ( isset( $_POST['double_optin'] ) ) ? $_POST['double_optin'] : 0,
			'send_welcome' => ( isset( $_POST['send_welcome'] ) ) ? $_POST['send_welcome'] : false,
			'user_delete'  => ( isset( $_POST['user_delete']  ) ) ? $_POST['user_delete']  : 0
		);

		update_option( 'wpmembers_mailchimp', $wpmem_mc_settings_arr );

		$did_update = __( 'WP-Members MailChimp settings were updated', 'wp-members' );

		return $did_update;
	}
}


/**
 * Updates MailChimp on user delete.
 *
 * @since 1.1
 *
 * @param int $user_id The ID of user being deleted.
 */
function wpmem_a_mc_del_user( $user_id )
{
	/** get MC options and extract **/
	extract( wp_parse_args( get_option( 'wpmembers_mailchimp' ), array( 'user_delete' => 0 ) ) );

	/** Load the MCAPI class if it not already loaded */
	if( ! class_exists( 'MCAPI' ) )
		require_once WPMEM_MC_PATH . 'MCAPI.class.php';
	
	$api = new MCAPI( $api_key );

	/** get the user data **/
	$user = get_user_by( 'id', $user_id );
	
	/** handle user delete **/
	$delete_member = ''; $send_goodbye = '';
	if( $user_delete != 0 ) {
		switch( $user_delete ) {
			case 1:
				/** Delete the user from MailChimp **/
				$delete_member = true;
				$send_goodbye  = '';
				break;

				
			case 3:
				/** Unsubscribe the user from MailChimp **/
				$delete_member = '';
				$send_goodbye  = '';
				break;
				
			case 4:
				/** Unsubscribe the user from MailChimp and send notification to user **/
				$delete_member = '';
				$send_goodbye  = true;
				break;
		}
		
		/** remove the user based on settings **/
		$retval = $api->listUnsubscribe( $list_id, $user->user_email, $delete_member, $send_goodbye );
	}
	
	return;
}


/** End of File **/