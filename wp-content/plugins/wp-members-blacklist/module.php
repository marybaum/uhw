<?php
/*
Plugin Name: WP-Members Blacklist Extension
Plugin URI:  http://rocketgeek.com
Description: Blacklists a group of IPs, emails, or usernames from registration via WP-Members.
Version:     1.1
Author:      Chad Butler
*/


/**
 * Define Constants
 */
define( 'WPMEM_BL_VERSION', '1.1' );


/**
 * Action and filter hooks
 */
add_action( 'wpmem_pre_register_data', 'wpmem_blacklist', 1 );
add_filter( 'wpmem_admin_tabs',        'wpmem_a_bl_tab' );
add_action( 'wpmem_admin_do_tab',      'wpmem_a_blacklist_tab', 1, 1 );
add_action( 'admin_init',              'wpmem_a_update_blacklist' );
add_filter( 'plugin_action_links',     'wpmem_bl_plugin_links', 10, 2 ); 


/**
 * The blacklist function
 *
 * @param array  $fields
 * @return array $fields
 */
function wpmem_blacklist( $fields )
{
	global $wpmem_themsg;
	
	$wpmem_blacklist = get_option( 'wpmembers_blacklist' );
	
	if( $wpmem_blacklist ) {
		
		extract( wp_parse_args( '',  $wpmem_blacklist ) );
		
		// check for blacklisted IPs
		if( $ip_blacklist ) {
			$ips = explode( ",", $ip_blacklist );
			foreach( $ips as $ip ) {
				$wpmem_themsg = ( $_SERVER['REMOTE_ADDR'] === trim( $ip ) ) ? $ip_blacklist_msg : '';
				if( $wpmem_themsg )
					return;
			}
		}
		
		// check for blacklisted emails
		if( $email_blacklist ) {
			$emails = explode( ",", $email_blacklist );
			foreach( $emails as $email ) {
				// check for wildcard domains
				if( strstr( $email, '*' ) ) {
					$domain = trim( $email, ' \*' );
					$wpmem_themsg = ( strstr( $fields['user_email'], $domain ) ) ? $email_blacklist_msg : '';
				} else {
					$wpmem_themsg = ( $fields['user_email'] === trim( $email ) ) ? $email_blacklist_msg : '';
				}
				if( $wpmem_themsg )
					return;
			}
		}
		
		// check for blacklisted usernames
		if( $username_blacklist ) {
			$usernames = explode( ",", $username_blacklist );
			foreach( $usernames as $username ) {
				$wpmem_themsg = ( $fields['username'] === trim( $username ) ) ? $username_blacklist_msg : '';
				if( $wpmem_themsg )
					return;
			}
		}
	}
	
	return $fields;
}


/**
 * Adds Blacklist to the admin tab array
 *
 * @since 1.0
 *
 * @param  array $tabs
 * @return array $tabs the updated array
 */
function wpmem_a_bl_tab( $tabs ) {
	return array_merge( $tabs, array( 'blacklist'  => 'Blacklist' ) );
}


/**
 * Builds the Blacklist tab in the admin
 *
 * @since 1.0
 *
 * @param string $tab
 */
function wpmem_a_blacklist_tab( $tab ) {
	if( $tab == 'blacklist' )
		wpmem_a_build_blacklist();
	return;
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
function wpmem_bl_plugin_links( $links, $file )
{
	static $wpmem_bl_plugin;
	if( !$wpmem_bl_plugin ) $wpmem_bl_plugin = plugin_basename( 'wp-members-blacklist/module.php' );
	if( $file == $wpmem_bl_plugin ) {
		$settings_link = '<a href="options-general.php?page=wpmem-settings&tab=blacklist">' . __( 'Settings' ) . '</a>';
		$links = array_merge( array( $settings_link ), $links );
	}
	return $links;
}


/**
 * Builds the settings tab
 *
 * @since 1.0
 */
function wpmem_a_build_blacklist()
{
	// set default error messages if needed:
	$defaults = array(
		'ip_blacklist'           => '',
		'ip_blacklist_msg'       => 'Sorry, that IP address is prevented from registration.  If you feel you have reached this message in error please contact us directly.',
		'email_blacklist'        => '',
		'email_blacklist_msg'    => 'Sorry, that email address is prevented from registration.  If you feel you have reached this message in error please contact us directly.',
		'username_blacklist'     => '',
		'username_blacklist_msg' => 'Sorry, that username is not allowed.  If you feel you have reached this message in error please contact us directly.'
	);

	extract( wp_parse_args( get_option( 'wpmembers_blacklist' ),  $defaults ) );
	
	global $did_update;
	
	if( $did_update ) { ?>
		<div id="message" class="updated fade"><p><strong><?php echo $did_update; ?></strong></p></div>
	<?php } ?>

		<div class="metabox-holder has-right-sidebar">

		<div class="inner-sidebar">
			<div class="postbox">
				<h3><span>WP-Members Blacklist Module</span></h3>
				<div class="inside">
					<p><strong><?php _e('Version:', 'wp-members'); ?> <?php echo ( defined( 'WPMEM_BL_VERSION' ) ) ? WPMEM_BL_VERSION : '?'; ?></strong><br /></p>
				</div>
			</div>
			<?php wpmem_a_meta_box(); ?>
		</div>	

		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
					<h3><span><?php _e( 'Blacklist Settings', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<form name="updatesettings" id="updatesettings" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
							<?php wp_nonce_field( 'wpmem-update-bl' ); ?>
							<table class="form-table">
								<tr><th colspan="2">Enter a list of IPs that you would like to prevent from registration. Any IP addresses
									in this list will receive an error message if they try to register.  Enter IP addresses individually followed
									by a comma: 192.168.1.1,120.0.0.1,192.168.0.1</th>
								</tr>
								<tr>
									<th>IP Blacklist:</td>
									<td><textarea name="ip_blacklist" rows="3" cols="80"><?php echo $ip_blacklist; ?></textarea></td>
								</tr>
								<tr>
									<th>IP Blacklist Error Message:</td>
									<td><textarea name="ip_blacklist_msg" rows="3" cols="80"><?php echo stripslashes( $ip_blacklist_msg ); ?></textarea></td>
								</tr>
								<tr><th colspan="2">Enter a list of email addresses that you would like to prevent from registration. Any email addresses
									in this list will receive an error message if they try to register.  Block a domain by indicating a wildcard: *@gmail.com.
									Enter emails individually followed by a comma: myemail@email.com,email@address.com,*@wildcard.com</th>
								</tr>
								<tr>
									<th>Email Blacklist:</td>
									<td><textarea name="email_blacklist" rows="3" cols="80"><?php echo $email_blacklist; ?></textarea></td>
								</tr>
								<tr>
									<th>IP Blacklist Error Message:</td>
									<td><textarea name="email_blacklist_msg" rows="3" cols="80"><?php echo stripslashes( $email_blacklist_msg ); ?></textarea></td>
								</tr>
								<tr><th colspan="2">Enter a list of usernames that you would like to prevent from registration. Any usernames
									in this list will receive an error message if they try to register.  Enter usernames individually followed
									by a comma: admin,administrator</th>
								</tr>
								<tr>
									<th>Username Blacklist:</td>
									<td><textarea name="username_blacklist" rows="3" cols="80"><?php echo $username_blacklist; ?></textarea></td>
								</tr>
								<tr>
									<th>Username Blacklist Error Message:</td>
									<td><textarea name="username_blacklist_msg" rows="3" cols="80"><?php echo stripslashes( $username_blacklist_msg ); ?></textarea></td>
								</tr>
								<tr>
									<th>&nbsp;</td>
									<td>
										<input type="hidden" name="wpmem_admin_a" value="update_bl" />
										<input type="submit" name="save"  class="button-primary" value="<?php _e( 'Update', 'wp-members' ); ?> &raquo;" />
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
 * Updates the Blacklist add-on settings
 *
 * @since 1.0
 *
 * @return string $did_update Contains the update message
 */
function wpmem_a_update_blacklist()
{
	if( isset( $_POST['wpmem_admin_a'] ) && ( $_POST['wpmem_admin_a'] == 'update_bl' ) ) {
		
		//check nonce
		if ( ! empty( $_POST ) && check_admin_referer( 'wpmem-update-bl' ) ) {

			global $did_update;

			$wpmem_bl_settings_arr = array(	
				'ip_blacklist'           => ( isset( $_POST['ip_blacklist']           ) ) ? $_POST['ip_blacklist'] : '',
				'ip_blacklist_msg'       => ( isset( $_POST['ip_blacklist_msg']       ) ) ? sanitize_text_field( $_POST['ip_blacklist_msg'] ) : '',
				'email_blacklist'        => ( isset( $_POST['email_blacklist']        ) ) ? $_POST['email_blacklist'] : '',
				'email_blacklist_msg'    => ( isset( $_POST['email_blacklist_msg']    ) ) ? sanitize_text_field( $_POST['email_blacklist_msg'] ) : '',
				'username_blacklist'     => ( isset( $_POST['username_blacklist']     ) ) ? $_POST['username_blacklist'] : '',
				'username_blacklist_msg' => ( isset( $_POST['username_blacklist_msg'] ) ) ? sanitize_text_field( $_POST['username_blacklist_msg'] ) : ''
			);

			update_option( 'wpmembers_blacklist', $wpmem_bl_settings_arr );

			$did_update = __( 'WP-Members Blacklist settings were updated', 'wp-members' );

			return $did_update;
		}
	}
}
?>