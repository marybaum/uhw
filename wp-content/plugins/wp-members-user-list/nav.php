<?php
/**
 * WP-Members User List Shortcode Extension Navigation Functions
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
 * Create the navigation links (previous/next)
 *
 * @since 1.4
 *
 * @uses apply_filters Calls 'wpmem_ul_nav_args' filters the arguments to override defaults
 * @uses apply_filters Calls 'wpmem_ul_nav' filters the html of the navigation links
 *
 * @param  int    $page      The page we are on
 * @param  int    $num_pages The number of pages that are being displayed
 * @param  string $nav       The type of navigation to display (true=old style prev|next, page=paginated links)
 * @return string $str       The generated html string for previous/next navigation links
 */
function wpmem_do_ul_member_nav( $page, $num_pages, $nav )
{
	$defaults = array(
		
		// tags
		'nav_before'  => '<nav id="wpmem-ul-nav">',
		'nav_after'   => '</nav>',
		'prev_before' => '<span class="wpmem-ul-prev">',
		'prev_after'  => '</span>',
		'next_before' => '<span class="wpmem-ul-next">',
		'next_after'  => '</span>',
		
		// text
		'prev_text'   => '&laquo; ' . __( 'Previous' ),
		'next_text'   => __( 'Next' ) . ' &raquo;'
	
	);
	
	// filter $args
	$args = apply_filters( 'wpmem_ul_nav_args', '' );
	
	// merge $args with defaults and extract
	extract( wp_parse_args( $args, $defaults ) );

	/**
	 * What type of nav are we doing? The original nav ($nav=true) yields 
	 * basic prev/next links. The new style nav option ($nav=page) yields
	 * paginated links with prev/next and page numbers in between.
	 */
	if( $nav == "true" ) {
		
		$nav = '';
		
		/** if we are not on the first page, add a previous link */
		if( $page != 1 ) {
			$prev_qstring = ( get_option( 'permalink_structure' ) ) ? get_permalink() . 'page/' . ( $page - 1 ) . '/' : $add_qstring = get_permalink() . '&page=' . ( $page - 1 );
			
			if( isset( $_GET['sm'] ) && isset( $_GET['sb'] ) ) {
				$prev_qstring.= '?sm=' . $_GET['sm'] . '&amp;sb=' . $_GET['sb'];
			}
			$nav.= $prev_before . '<a rel="prev" href="' . $prev_qstring  . '">' . $prev_text . '</a>' . $prev_after;
		}
		
		/** if we are not on the last page, add a next link */
		if( $page < $num_pages ) {
			$next_qstring = ( get_option( 'permalink_structure' ) ) ? get_permalink() . 'page/' . ( $page + 1 ) . '/' : $add_qstring = get_permalink() . '&page=' . ( $page + 1 );
			
			if( isset( $_GET['sm'] ) && isset( $_GET['sb'] ) ) {
				$next_qstring.= '?sm=' . $_GET['sm'] . '&amp;sb=' . $_GET['sb'];
			}
			$nav.= $next_before . '<a rel="next" href="' . $next_qstring  . '">' . $next_text . '</a>' . $next_after;
		}
		
		// apply main wrapper
		$nav = $nav_before . $nav . $nav_after; 
	
	} else { 
	
		/** new nav **/
		$frag = ( isset( $_GET['sm'] ) && isset( $_GET['sb'] ) ) ? '/?sm=' . $_GET['sm'] . '&amp;sb=' . $_GET['sb'] : '';
		$base = get_permalink() . '%_%';
		$args = array( 
			'base'         => $base,
			'total'        => $num_pages,
			'current'      => $page,
			'format'       => 'page/%#%',
			'end_size'     => 1,
			'mid_size'     => 2,
			'prev_next'    => True,
			'prev_text'    => $prev_text,
			'next_text'    => $next_text,
			'add_fragment' => $frag
			
		);
		$nav = $nav_before . paginate_links( $args ) . $nav_after;
	
	}
	
	/** return the filtered result */
	return apply_filters( 'wpmem_ul_nav', $nav );
	
}