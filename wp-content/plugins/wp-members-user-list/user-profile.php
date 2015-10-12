<?php
/**
 * WP-Members User List Shortcode Extension Profile Functions
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2014  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2014
 */


/**
 * Profile shortcode
 *
 * @since 1.4
 *
 * @param array  $atts
 * @param string $content
 * @param string $tag
 */
function wpmem_do_ul_profile( $atts, $content = null, $tag )
{
	// set all default attributes to false
	$defaults = shortcode_atts( array(
		'fields'     => 'user_login,user_email,first_name,last_name,city,thestate',
		'id'         => ( isset( $_GET['uid'] ) ) ? $_GET['uid'] : '',
		'div'        => true,
		'div_id'     => '',
		'div_class'  => 'field-name',
		'span'       => false,
		'span_id'    => '',
		'span_class' => '',
		'avatar'     => '80',
		'labels'     => false,
		
		'main_div_before' => '<div id="user-list-profile">',
		'main_div_after'  => '</div>'
	), $atts, $tag );

	/**
	 * Filter to collect arguments to be merged.
	 *
	 * @since 1.4
	 *
	 * @param array An array of arguments to be merged with the defaults.
	 */
	$args = apply_filters( 'wpmem_ul_profile_args', '' );
	
	/** Extract the shortcode attributes */
	extract( wp_parse_args( $args, $defaults ) ); 
	
	// if there is no ID, there's no point in continuing
	if( ! $id ) {
		/**
		 * Filter the content for empty ID.
		 *
		 * @since 1.3
		 *
		 * @param string Defaults to null.
		 */
		return apply_filters( 'wpmem_ul_profile_empty', '' );
	}
	
	// array of labels, if used
	if( $labels == true ) {
		$label_arr = get_option( 'wpmembers_fields' );
		$the_labels = array();
		foreach( $label_arr as $label ) {
			$the_labels[$label[2]] = $label[1];
		}
	}
	
	// put fields into an array
	$fields = explode( ",", $fields );
	
	// get the user info
	$user_info = get_userdata( $id );
	
	// get avatar, if used
	$avatar = ( $avatar > 0 || $avatar == false ) ? get_avatar( $user_info->user_email, $avatar ) : '';
	array_unshift( $fields, 'avatar' );
	
	// start with an empty profile;
	$rows = array();
	
	// put profile data into an array of rows for filtering
	foreach( $fields as $field ) {
		$field = trim( $field );
		
		// if we are showing field labels, get the appropriate label
		if( $labels == true ) {
			$show_label = '';
			foreach( $the_labels as $label_key => $label ) {
				if( $label_key == $field ) { 
					$show_label = $label . ': '; 
				} elseif( $field == 'user_login' ) {
					$show_label = 'username: ';
				}
			}
		} else {
			$show_label = '';
		}

		// work the fieldname class specification for divs and spans
		$d_class = ( $div_class  == 'field-name' ) ? $field : $div_class;
		$s_class = ( $span_class == 'field-name' ) ? $field : $span_class;
		
		// build the row
		$rows[$field]['div_before']  = ( $div  ) ? '<div'  . ( ( $div_id )  ? ' id="' . $field . '"' : '' ) . ( ( $d_class ) ? ' class="' . $d_class . '"' : '' ) . '>' : '';
		$rows[$field]['span_before'] = ( $span ) ? '<span' . ( ( $span_id ) ? ' id="' . $field . '"' : '' ) . ( ( $s_class ) ? ' class="' . $s_class . '"' : '' ) . '>' : '';
		$rows[$field]['field']       = ( $field == 'avatar' ) ? $avatar : $show_label . htmlspecialchars( $user_info->$field );
		$rows[$field]['span_after']  = ( $span ) ? '</span>' : '';
		$rows[$field]['div_after']   = ( $div  ) ? '</div>'  : '';
	}
	
	/**
	 * Filter the profile rows.
	 *
	 * @since 1.4
	 *
	 * @param array $rows An array of the row contents.
	 */
	$rows = apply_filters( 'wpmem_ul_profile_rows', $rows );
	
	// assemble the profile
	$profile = '';
	foreach( $rows as $row_item ){
		$profile.= $row_item['div_before'] . $row_item['span_before'] . $row_item['field'] . $row_item['span_after'] . $row_item['div_after'] . "\n";
	}
	
	// apply wrapper
	$profile = $main_div_before . $profile . $main_div_after;
	
	// return the profile
	return ( $user_info ) ? $profile . do_shortcode( $content ) : do_shortcode( $content );
}