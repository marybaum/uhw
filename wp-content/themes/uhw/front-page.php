<?php
/**
 * This file adds the Home Page to the uhw Pro Theme.
 *
 * @author StudioPress
 * @package uhw Pro
 * @subpackage Customizations
 */

add_action( 'genesis_meta', 'uhw_home_genesis_meta' );
/**
 * Add widget support for homepage. If no widgets active, display the default loop.
 *
 */
function uhw_home_genesis_meta() {

	if ( is_active_sidebar( 'home-top' ) || is_active_sidebar( 'home-middle' ) || is_active_sidebar( 'home-bottom-left' ) || is_active_sidebar( 'home-bottom-right' ) ) {

		// Force content-sidebar layout setting
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_content_sidebar' );

		// Add uhw-pro-home body class
		add_filter( 'body_class', 'uhw_body_class' );

		// Remove the default Genesis loop
		remove_action( 'genesis_loop', 'genesis_do_loop' );

		// Add homepage widgets
		add_action( 'genesis_loop', 'uhw_homepage_widgets' );

	}
}

function uhw_body_class( $classes ) {

	$classes[] = 'uhw-home';
	return $classes;
	
}


function uhw_homepage_widgets() {

	genesis_widget_area( 'home-top', array(
		'before' => '<div class="home-top widget-area">',
		'after'  => '</div>',
	) );
	
	 if ( is_active_sidebar( 'home-middle-left' ) || is_active_sidebar( 'home-middle-right' ) ) {
	   
    echo "<div class='home-middle'>";

    genesis_widget_area( 'home-middle-left', array(
      'before' => '<div class="home-middle-left widget-area">',
      'after'  => '</div>',
    ) );

    genesis_widget_area( 'home-middle-right', array(
      'before' => '<div class="home-middle-right widget-area">',
      'after'  => '</div>',
    ) );
    
    echo "</div>";

  }
	
	if ( is_active_sidebar( 'home-bottom-left' ) || is_active_sidebar( 'home-bottom-right' ) ) {

		genesis_widget_area( 'home-bottom-left', array(
			'before' => '<div class="home-bottom-left widget-area">',
			'after'  => '</div>',
		) );

		genesis_widget_area( 'home-bottom-right', array(
			'before' => '<div class="home-bottom-right widget-area">',
			'after'  => '</div>',
		) );
	
	}

}

genesis();
