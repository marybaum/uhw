<?php
/**
 * WP-Members User List Shortcode Extension Search Form Functions
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
 * Create the search form
 *
 * @since 1.4
 *
 * @uses apply_filters Calls 'wpmem_ul_search_args'
 * @uses apply_filters Calls 'wpmem_ul_search_form'
 * @uses apply_filters Calls 'wpmem_ul_results_heading'
 *
 * @param  string $search_term
 * @return string $search_form $search_results_heading
 */
function wpmem_do_ul_member_search_form( $search_term, $search_by_arr )
{
	$defaults = array(

		// wrappers
		'heading_before'  => '<legend>',
		'heading_after'   => '</legend>',
		'fieldset_before' => '<fieldset>',
		'fieldset_after'  => '</fieldset>',
		'main_div_before' => '<div id="wpmem_ul_search">',
		'main_div_after'  => '</div>',
		'row_before'      => '',
		'row_after'       => '',
		'buttons_before'  => '<div class="button_div">',
		'buttons_after'   => '</div>',
		'results_before'  => '<h2>',
		'results_after'   => '</h2>',
		'link_before'     => '<p>',
		'link_after'      => '</p>',

		// classes & ids
		'form_id'         => 'wpmem_search_form',
		'form_class'      => 'form',
		'button_id'       => 'wpmem_searchsubmit',
		'button_class'    => 'buttons',
		'results_term'    => 'results-term',
		
		// labels
		'heading'         => __( 'Search Members', 'wp-members-ul' ),
		'search_for'      => __( 'Search for:', 'wp-members-ul' ),
		'search_by'       => __( 'Search by:', 'wp-members-ul' ),
		'button_text'     => __( 'Search', 'wp-members-ul' ),
		'results_heading' => __( 'Search Results For:', 'wp-members-ul' ),
		'results_link'    => __( 'Back To Member Listing', 'wp-members-ul' ),

		// other
		'strip_breaks'     => true,
		'wrap_inputs'      => true

	);

	// filter $args
	$args = apply_filters( 'wpmem_ul_search_args', '' );
	
	// merge $args with defaults and extract
	extract( wp_parse_args( $args, $defaults ) );

	/** input 1 (search string) */
	// label
	$form = '<label class="text" for="search">' . $search_for . '</label>';
	// start wrapper if used
	$form.= ( $wrap_inputs ) ? '<div class="div_text">' : '';
	// input field
	$form.= '<input type="text" class="textbox" name="sm" id="sm" />';
	// close wrapper if used
	$form.= ( $wrap_inputs ) ? '</div>' : '';

	/** input 2 (search by, if there are parameters) */
	if( ! empty( $search_by_arr ) ) {
		// label
		$form.= '<label class="select" for="search_by">' . $search_by . '</label>';
		// start wrapper if used
		$form.= ( $wrap_inputs ) ? '<div class="div_text">' : '';
		// build the dropdown for input 2
		$form.= wpmem_create_formfield( 'sb', 'select', $search_by_arr );	
		// close wrapper if used
		$form.= ( $wrap_inputs ) ? '</div>' : '';
	}
	
	// BUTTONS
	$buttons = '<input type="submit" name="Submit" value="' . $button_text . '" class="' . $button_class . '" id="' . $button_id . '" />';
	
	// apply buttons
	$form = $form . $buttons_before . $buttons . $buttons_after;

	// apply the heading
	$form = $heading_before . $heading . $heading_after . $form;
	
	// apply fieldset wrapper
	$form = $fieldset_before . $form . $fieldset_after;
	
	// apply form wrapper
	$form = '<form method="get" id="' . $form_id . '" class="' . $form_class . '" action="' . get_permalink() . '">' . $form . '</form>';
	
	// apply anchor
	$form = '<a name="user-search"></a>' . $form;
	
	// apply main wrapper
	$form = $main_div_before . $form . $main_div_after;
	
	// remove line breaks
	$form = ( $strip_breaks ) ? str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form ) : $form;
	
	$form = apply_filters( 'wpmem_ul_search_form', $form );
	
	/**
	 * default search results heading
	 * can be filtered with wpmem_ul_results_heading
 	 */
	if( $search_term ){
		$search_results_heading = $results_before . $results_heading . ' <span class="' . $results_term . '">' . $search_term . '</span>' . $results_after .
		$link_before . '<a href="' . get_permalink() . '">' . $results_link . '</a>' . $link_after;
	}
	
	$search_results_heading = ( isset( $search_results_heading ) ) ? apply_filters( 'wpmem_ul_results_heading', $search_results_heading ) : '';
	
	/** return the form and, if applicable, the search results heading */
	return $form . $search_results_heading;
}