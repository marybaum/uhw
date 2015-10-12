<?php
/*
Plugin Name: WP-Members User List Extension
Plugin URI:  http://rocketgeek.com
Description: Adds a shortcode to create a list of members on a page.  
Author:      Chad Butler
Version:     1.4
Author URI:  http://butlerblog.com
*/


/*  
	Copyright (c) 2006-2014  Chad Butler (email : plugins@butlerblog.com)

	The name WP-Members(tm) is a trademark of rocketgeek.com
*/


/** initial constants **/
define( 'WPMEM_UL_VERSION', '1.4' );

/** initialize the plugin **/
add_action( 'init', 'wpmem_ul_init' );

/** install the pluign **/
register_activation_hook( __FILE__, 'wpmem_ul_install' );


/**
 * Initializes the plugin
 *
 * @since 1.4
 */
function wpmem_ul_init() 
{
	// load translation, if needed
	load_plugin_textdomain( 'wp-members-ul', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	
	// load admin, if needed
	if( current_user_can( 'manage_options' ) ) {
		require_once( 'admin/admin.php' );
	}
	
	add_action( 'wp_print_styles', 'wpmem_ul_enqueue_style' );
	add_action( 'admin_enqueue_scripts', 'wpmem_a_ul_css' );

	add_shortcode( 'wp-members-user-list', 'wpmem_list_users' );
	add_shortcode( 'wpmem_ul',             'wpmem_list_users' );
	add_shortcode( 'wpmem_ul_profile',     'wpmem_ul_profile' );
}


/**
 * Creates the list of users
 *
 * @since 1.0
 *
 * @param  array  $atts    The shortcode attributes.
 * @param  string $content The shortcode content.
 * @param  string $tag     The shortcode tag.
 * @return string $content
 */
function wpmem_list_users( $atts, $content, $tag = 'wpmem_ul' ) 
{	
	/** Get default settings */
	$defaults = get_option( 'wpmembers_ul_settings' );
	$atts = ( ! $atts ) ? array() : $atts;
	
	/** Set up attributes to be merged with defaults */
	$defaults = shortcode_atts( array(
		'role'         => $defaults['role'],
		'exclude'      => $defaults['exclude'],
		'number'       => $defaults['number'],
		'search'       => $defaults['search'],
		'search_by'    => $defaults['search_by'],
		'nav'          => $defaults['nav'],
		'fields'       => 'user_login,member_since',
		'avatar'       => $defaults['avatar'],
		'h2'           => $defaults['h2'],
		'order_by'     => $defaults['order_by'],
		'order'        => $defaults['order'],
		'show_titles'  => $defaults['show_titles'],
		'show_empty'   => $defaults['show_empty'],
		'meta_key'     => $defaults['meta_key'],
		'meta_val'     => '',
		'profile_page' => $defaults['profile_page'], // @todo determine if this is the final nomenclature
	), $atts, $tag );
	
	/**
	 * Filter to collect arguments to be merged.
	 *
	 * @since 1.4
	 *
	 * @param array An array of arguments to be merged with the defaults.
	 */
	$args = apply_filters( 'wpmem_ul_settings_args', '' );
	
	/** Extract the shortcode attributes and sanitize */
	extract( wp_parse_args( $args, $defaults ) ); 

	$role = sanitize_text_field( $role );
	$num  = sanitize_text_field( $number );
	
	/**
	 * Determine displayed fields in this order of priority:
	 * (1) field array passed with shortcode attributes
	 * (2) field array stored in plugin settings
	 * (3) default field array (user_login,member_since)
	 */
	$fields = explode( ',', $fields );
	$fields_temp = get_option( 'wpmembers_ul_fields' );
	$fields = ( $fields_temp && ( ! array_key_exists( 'fields', $atts ) ) ) ? $fields_temp : $fields;
	
	$fields['avatar']      = ( $avatar < 0 || $avatar == "false" ) ? "false" : $avatar;
	$fields['h2']          = $h2;
	$fields['show_titles'] = $show_titles;
	$fields['show_empty']  = $show_empty;
	
	// @todo profile_page - finish this
	$fields['profile_page'] = $profile_page;
	
	/**
	 * Determine search_by terms in this order of priority:
	 * (1) field array passed with shortcode attributes
	 * (2) field array stored in plugin settings
	 */	
	$search_by = ( $search_by ) ? explode( ',', $search_by ) : null;
	$search_by_temp = get_option( 'wpmembers_ul_search' );
	$search_by = ( $search_by_temp && ( ! array_key_exists( 'search_by', $atts ) ) ) ? $search_by_temp : $search_by;
	
	/** if there is a search term, get it */
	$search_term = ( isset( $_GET['sm'] ) ) ? sanitize_text_field( $_GET['sm'] ) : false ;
	$search_b    = ( isset( $_GET['sb'] ) ) ? sanitize_text_field( $_GET['sb'] ) : false ;
	
	/** get the page, use get_query_var */
	if ( get_option('permalink_structure') ) { 
		$page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	} else {
		$page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;
	}
	
	/** calculate any navigation offset */
	$offset = ( $page - 1 ) * $num;

	/** the user query */
	$the_user_query = array( 
		'role'       => $role,
		'offset'     => $offset,
		'number'     => $num,
		'orderby'    => $order_by,
		'exclude'    => $exclude
	);
	
	/** add search/meta elements to the user query */
	if( ! $search_by ) {
		$the_user_query['search']         = $search_term;
		$the_user_query['search_columns'] = array( 'user_login', 'user_nicename', 'user_email' );
	} elseif( $search_b == 'user_login' || $search_b == 'user_email' ) {
		$the_user_query['search']         = $search_term;
		$the_user_query['search_columns'] = $search_b;
	} else {
 		$the_user_query['meta_key']   = ( $search_b )    ? $search_b    : ( ( $meta_key ) ? $meta_key : '' );
		$the_user_query['meta_value'] = ( $search_term ) ? $search_term : ( ( $meta_val ) ? $meta_val : '' );
	}
	
	if( $order_by == 'meta_value' ) {
		$the_user_query['meta_key'] = $meta_key;
	}
	
	if( $order ) {
		$the_user_query['order'] = $order;
	}

	/**
	 * Filter the user query.
	 *
	 * @since 1.4
	 *
	 * @param array $the_user_query.
	 */
	$the_user_query = apply_filters( 'wpmem_ul_user_query', $the_user_query );
	
	/** get the results of the query */
	$the_users = new WP_User_Query( $the_user_query );

	/** get total users */ 
	$total_users = $the_users->total_users;

	/** calculate number of pages */
	$num_pages = intval( $total_users / $num ) + 1;

	/** put the users in an array */
	$users = $the_users->get_results();
	
	/** if we are displaying the search form, generate the search form */
	$search_form = ( $search == "true" ) ? wpmem_ul_member_search_form( $search_term, $search_by ) : '';
	
	/** generate the user list */
	$user_list = wpmem_do_member_list( $users, $fields );

	/** if we are displaying the nav links, generate the html */
	$nav_links = ( $nav ) ? wpmem_ul_member_nav( $page, $num_pages, $nav ) : '';
	
	/** put elements together in $content */
	$content = $search_form . $user_list . $nav_links;
	
	/** 
	 * Filter applies texturize fix.
	 * 
	 * @since 1.2
	 *
	 * @param bool true|false boolean defaults to false.
	 */
	$content = ( apply_filters( 'wpmem_ul_wpmem_txt', false ) ) ? '[wpmem_txt]' . $content . '[/wpmem_txt]' : $content;
	
	/** 
	 * Filter strips line breaks from list html.
	 * 
	 * @since 1.2
	 *
	 * @param bool true|false boolean defaults to false.
	 */
	$content = ( apply_filters( 'wpmem_ul_strip_breaks', false ) ) ? str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $content ) : $content;

	/** return result */
	return $content;
}


/**
 * Create the member list
 *
 * @since 1.0
 *
 * @param  array  $users  An array of the users to be displayed in the list
 * @param  array  $fields An array of the fields that are to be displayed for each user
 * @return string $str    The list of users|The error message if search string is empty
 */
function wpmem_do_member_list( $users, $fields )
{
	$defaults = array(

		// wrappers
		'list_before'       => '<div id="wpmem-ul-list">',
		'list_after'        => '</div>',
		'heading_before'    => '<h2>',
		'heading_after'     => '</h2>',
		'user_even_before'  => '<div class="wpmem-ul-user">',
		'user_even_after'   => '</div>',
		'user_odd_before'   => '<div class="wpmem-ul-user-odd">',
		'user_odd_after'    => '</div>',
		'avatar_before'     => '<div class="avatar">',
		'avatar_after'      => '</div>',
		'field_tag'         => 'div',
		'empty_list_before' => '<div align="center" id="wpmem_msg"><p>',
		'empty_list_after'  => '</p></div>',
		
		// other
		'member_since_label'  => __( 'Member since: ', 'wp-members-ul' ),
		'member_since_format' => "F j, Y, g:i a",
		'empty_list_text'     => __( 'No members found', 'wp-members-ul' ) 
		
	);
	
	/**
	 * Filter accepts overrides for defaults.
	 *
	 * @since 1.2
	 *
	 * @param array An array of the new defaults.
	 */
	$args = apply_filters( 'wpmem_ul_list_args', '' );

	// merge $args with defaults
	$args = wp_parse_args( $args, $defaults );
		
	if( ! empty( $users ) ) {
		
		/**
		 * If showing titles, we need to build an array of Field Labels.
		 * Doing it here means we don't have to run "get_option" a hundred times...
		 */
		if( $fields['show_titles'] == true ) {
			$label_arr = get_option( 'wpmembers_fields' );
			$labels = array();
			foreach( $label_arr as $label ) {
				$labels[$label[2]] = $label[1];
			}
		}
		
		// set the row class for alternating row backgrounds
		$odd = 0;
		
		// start with an empty string
		$str = '';
		
		// loop through the users
		foreach( $users as $user ){

			// generate the user, add it to the total string $str
			$str .= wpmem_ul_user( $user, $odd, $fields, $labels, $args );
			
			// toggle the alternating row color
			$odd = ( $odd == 0 ) ? 1 : 0;
	
		}
		
		// apply list wrapper
		$str = $args['list_before'] . $str . $args['list_after'];
		
		/**
		 * Filter the HTML of the user list.
		 *
		 * @since 1.0
		 *
		 * @param string $str The HTML string.
		 */
		$str = apply_filters( 'wpmem_ul_user_list', $str );
	
	} else {
	
		/** if we have an empty list, show an error message */
		$str = $args['empty_list_before'] . $args['empty_list_text'] . $args['empty_list_after'];
		/**
		 * Filter the search error message.
		 *
		 * @since 1.0
		 *
		 * @param string $str The error message.
		 */
		$str = apply_filters( 'wpmem_ul_error_msg', $str );
	
	}
	
	return $str;
}


/**
 * Loads the stylesheet
 *
 * @since 1.0
 *
 * @uses wp_register_style
 * @uses wp_enqueue_style
 * @uses apply_filters Calls the wpmem_ul_style_path filter
 */
function wpmem_ul_enqueue_style()
{	
	switch( WPMEM_CSSURL ) {
			
		case( strpos( WPMEM_CSSURL, 'wp-members-2011.css' ) !== false ):
			$stylesheet = 'wp-members-2011-ul.css';
			break;
		case( strpos( WPMEM_CSSURL, 'wp-members.css' ) !== false ):
			$stylesheet = 'wp-members-ul.css';
			break;
		case( strpos( WPMEM_CSSURL, 'wp-members-kubrick.css' ) !== false ):
			$stylesheet = 'wp-members-kubrick-ul.css';
			break;
		case( strpos( WPMEM_CSSURL, 'wp-members-2013.css' ) !== false ):
			$stylesheet = 'wp-members-2013-ul.css';
			break;
		case( strpos( WPMEM_CSSURL, 'wp-members-2014' ) !== false ):
			$stylesheet = 'wp-members-2014-ul.css';
			break;
		case( strpos( WPMEM_CSSURL, 'generic' ) !== false ):
			$stylesheet = 'wp-members-generic-ul.css';
			break;
		default:
			$stylesheet = 'wp-members-2012-ul.css';
			break;
	}
	
	$css_path = apply_filters( 'wpmem_ul_style_path', WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . 'css/' . $stylesheet );
	
	wp_register_style( 'wp-members-ul', $css_path );
	wp_enqueue_style ( 'wp-members-ul');
}


if( ! function_exists( 'wpmem_ul_user' ) ):
/**
 * Displays the individual user
 *
 * @since 1.0
 * 
 * @user apply_filters Calls 'wpmem_ul_empty_h2' filters an empty h2 value
 * @uses apply_filters Calls 'wpmem_ul_h2' filters the h2 content
 * @uses apply_filters Calls 'wpmem_ul_empty_field' filters empty fields
 * @uses apply_filters Calls 'wpmem_ul_user' filters the user content
 *
 * @param  int    $user      The user ID
 * @param  int    $odd       Determines if this is an odd or even, for determining row backgrounds
 * @param  array  $fields    An array of the fields to be displayed
 * @param  array  $labels    An array of field label names
 * @return string $the_user  The html string of content for the individual user
 */
function wpmem_ul_user( $user, $odd, $fields, $labels = false, $args )
{
	extract( $args );
	
	$user_info = get_userdata( $user->ID );

	switch( $fields['h2'] ) {
	
		case( 'first_last' ):
			$h2 = $heading_before . "$user_info->first_name $user_info->last_name" . $heading_after;
			break;
			
		case( 'last_first' ):
			$h2 = $heading_before . "$user_info->last_name, $user_info->first_name" . $heading_after;
			break;

		case( 'display_name' ):
			$h2 = $heading_before . "$user_info->display_name" . $heading_after;
			break;

		case( 'user_login' ):
		case( 'username' ):
			$h2 = $heading_before . "$user_info->user_login" . $heading_after;
			break;
		
		case( '' );
			$h2 = "";
			break;
		
		default:
			$h2 = $heading_before . $user_info->{$fields['h2']} . $heading_after;
			break;
	}
	
	if( $h2 == $heading_before . $heading_after || $h2 == $heading_before . ' ' . $heading_after ) {
		$h2 = $heading_before . apply_filters( 'wpmem_ul_empty_h2', '&nbsp;' ) . $heading_after;
	}
	
	// if there is a profile page set in the options, create a link on the H2 to the profile page,
	// otherwise, the H2 value is just the H2
	$h2 = ( $fields['profile_page'] ) ? '<a href="' . wpmem_chk_qstr( $fields['profile_page'] ) . 'uid=' . $user_info->ID . '">' . $h2 . '</a>' : $h2;
	
	/**
	 * Filter the H2 value
	 *
	 * @since ?.?
	 */
	$h2 = apply_filters( 'wpmem_ul_h2', $h2 );
	
	$avatar = ( $fields['avatar'] != 'false' ) ? get_avatar( $user_info->ID, $fields['avatar'] ) : false;
	$member_since = date( "n/j/Y", strtotime( $user_info->user_registered ) );
	
	// the heading
	$the_user_arr['h2'] = $h2;
	
	// the avatar
	$the_user_arr['avatar'] = ( $avatar ) ? $avatar_before . $avatar . $avatar_after : '';
	
	// the rest of the fields...
	foreach( $fields as $key => $val ) {
		if( is_int( $key ) ) {
			switch( $val ) { 
			case( 'user_login' ):
				$the_field = $user_info->user_login;
				break;
			case( 'user_email' ):
				$the_field = $user_info->user_email;
				break;
			case( 'user_url' ):
				$the_field = $user_info->user_url;
				break;
			case( 'user_registered' ):
				$the_field = $user_info->user_registered;
				break;
			case( 'member_since' ):
				$the_field = $member_since_label . date( $member_since_format, strtotime( $user_info->user_registered ) );
				break;
			case( 'display_name' ):
				$the_field = $user_info->display_name;
				break;
			default:
				$the_field = ( $val ) ? get_user_meta( $user_info->ID, trim( $val ), true ) : '';
				break;
			}

			// if the field is empty and we are not showing empty fields, move on
			if( $the_field == '' && $fields['show_empty'] == "false" ) { continue; }
			
			// if the field is empty, make it an encoded space (or filtered value)
			$the_field = ( $the_field == '' ) ? apply_filters( 'wpmem_ul_empty_field', '&nbsp;' ) : $the_field;
			
			// if we are showing field labels, get the appropriate label
			if( $fields['show_titles'] == 'true' ) {
				$show_label = '';
				foreach( $labels as $label_key => $label ) {
					if( $label_key == $val ) { 
						$show_label = $label . ': '; 
					} elseif( $val == 'user_login' ) {
						$show_label = __( 'Username: ', 'wp-members-ul' );
					}
				}
				// $the_user .= '<' . $field_tag . ' class="' . trim( $val ) . '">' . "$show_label$the_field</" . $field_tag . ">\n";
				$the_user_arr[$val] = '<' . $field_tag . ' class="' . trim( $val ) . '">' . "$show_label$the_field</" . $field_tag . ">";
			} elseif( $fields['show_titles'] == 'meta' ) {
				$the_user_arr[$val] = '<' . $field_tag . ' class="' . trim( $val ) . '">' . "$val: $the_field</" . $field_tag . ">";
				// $the_user .= '<' . $field_tag . ' class="' . trim( $val ) . '">' . "$val: $the_field</" . $field_tag . ">\n";
			} else {
				$the_user_arr[$val] = '<' . $field_tag . ' class="' . trim( $val ) . '">' . "$the_field</" . $field_tag . ">";
				// $the_user .= '<' . $field_tag . ' class="' . trim( $val ) . '">' . "$the_field</" . $field_tag . ">\n";
			}
		}
	}
	
	// apply the appropriate wrapper
	if( $odd == 1 ) {
		// $the_user = $user_odd_before . "\n" . $the_user . $user_odd_after . "\n";
		$the_user_arr['wrapper_before'] = $user_odd_before;
		$the_user_arr['wrapper_after']  = $user_odd_after;
	} else { 
		// $the_user = $user_even_before . "\n" . $the_user . $user_even_after . "\n";
		$the_user_arr['wrapper_before'] = $user_even_before;
		$the_user_arr['wrapper_after']  = $user_even_after;
	}
	
	$the_user_arr['ID'] = $user_info->ID;
	
	$the_user_arr = apply_filters( 'wpmem_ul_user_arr', $the_user_arr );
	
	$wrapper_before = $the_user_arr['wrapper_before'];
	$wrapper_after  = $the_user_arr['wrapper_after'];
	
	unset( $the_user_arr['ID'] );
	unset( $the_user_arr['wrapper_before'] );
	unset( $the_user_arr['wrapper_after'] );
	
	// put the filtered array values into a string
	$the_user = implode( "\n", $the_user_arr ) . "\n";
	$the_user = $wrapper_before . "\n" . $the_user . $wrapper_after . "\n";
	
	/**
	 * Filter the individual user record
	 *
	 * @since ?.?
	 */
	$the_user = apply_filters( 'wpmem_ul_user', $the_user, $user_info->ID );
	
	return $the_user;
}
endif;


/**
 * Installation function
 * @since 1.2
 */
function wpmem_ul_install() {
	require_once( 'ul-install.php' );
	wpmem_ul_do_install();
}


/**
 * Loads admin css
 * @since 1.2
 *
 * @todo fix so that it only loads with the wp-members user list tab
 */
function wpmem_a_ul_css( $hook ){
	if( isset( $_GET['tab'] ) && $_GET['tab'] == 'userlist' ) {
		wp_enqueue_style( 'wp-members-user-list', plugin_dir_url( __FILE__ ) . '/css/admin.css' );
	}
}


/**
 * Create the search form
 *
 * @since 1.0
 *
 * @param  string $search_term
 * @return string $search_form $search_results_heading
 */
function wpmem_ul_member_search_form( $search_term, $search_by_arr ) {
	require_once( 'search-form.php' );
	return wpmem_do_ul_member_search_form( $search_term, $search_by_arr );
}


/**
 * Create the navigation links (previous/next)
 *
 * @since 1.0
 *
 * @param  int    $page      The page we are on
 * @param  int    $num_pages The number of pages that are being displayed
 * @param  string $nav       The type of navigation to display (true=old style prev|next, page=paginated links)
 * @return string $str       The generated html string for previous/next navigation links
 */
function wpmem_ul_member_nav( $page, $num_pages, $nav ) {
	require_once( 'nav.php' );
	return wpmem_do_ul_member_nav( $page, $num_pages, $nav );
}


/**
 * Profile shortcode
 *
 * @since 1.3
 *
 * @param array  $atts
 * @param string $content
 * @param string $tag
 */
function wpmem_ul_profile( $atts, $content = null, $tag ) {
	require_once( 'user-profile.php' );
	return wpmem_do_ul_profile( $atts, $content, $tag );
}


/** End of File **/