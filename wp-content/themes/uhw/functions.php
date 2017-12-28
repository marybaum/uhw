<?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Set Localization (do not remove)
load_child_theme_textdomain( 'uhw', apply_filters( 'child_theme_textdomain', get_stylesheet_directory() . '/languages', 'uhw' ) );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', __( 'University Hills Women', 'uhw' ) );
define( 'CHILD_THEME_URL', 'http://uhillswomen.org/wp-content/themes/uhw' );
define( 'CHILD_THEME_VERSION', '0.1' );

//enqueue styles and type
add_action( 'wp_enqueue_scripts' , 'uhw_enqueue_scripts' );

function uhw_enqueue_scripts() {

wp_register_style( 'uhw-pauline', get_stylesheet_directory_uri() . '/type/pauline/paulinestyles.css', array(), '2' );
wp_register_style( 'uhw-quatiecond', get_stylesheet_directory_uri() . '/type/QuatieCond/quatiecond.css' , array(), '2');
wp_register_style( 'uhw-quatienorm', get_stylesheet_directory_uri() . '/type/QuatieNorm/quatienorm.css', array(), '2');

    wp_enqueue_style( 'uhw-pauline' );
		wp_enqueue_style( 'uhw-quatiecond' );
		wp_enqueue_style( 'uhw-quatienorm' );
}

//* Add HTML5 markup structure
add_theme_support( 'html5' );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Add new image sizes
add_image_size( 'featured-image', 900, 440, true );
add_image_size( 'welcome-image', 753, 480, true );
add_image_size( 'widget-image', 375, 275, true );

//* Add support for custom background
add_theme_support( 'custom-background' );

//* Add support for custom header
add_theme_support( 'custom-header', array(
	'width' 	=> 340,
	'height' 	=> 70,
	'header_image'  => '',
	'header-selector' => '.site-header .title-area',
	'header-text'  => false
) );

//* Add support for structural wraps
add_theme_support( 'genesis-structural-wraps', array(
	'header',
	'nav',
	'subnav',
	'inner',
	'footer-widgets',
	'footer'
) );



//* Modify breadcrumb arguments.
add_filter( 'genesis_breadcrumb_args', 'sp_breadcrumb_args' );
function sp_breadcrumb_args( $args ) {
  $args['home'] = 'Home';
  $args['sep'] = ' / ';
  $args['list_sep'] = ', '; // Genesis 1.5 and later
  $args['prefix'] = '<div class="breadcrumb">';
  $args['suffix'] = '</div>';
  $args['heirarchial_attachments'] = true; // Genesis 1.5 and later
  $args['heirarchial_categories'] = true; // Genesis 1.5 and later
  $args['display'] = true;
  $args['labels']['prefix'] = 'You are here: ';
  $args['labels']['author'] = 'Stories by ';
  $args['labels']['category'] = ' '; // Genesis 1.6 and later
  $args['labels']['tag'] = ' ';
  $args['labels']['date'] = ' ';
  $args['labels']['search'] = 'Find ';
  $args['labels']['tax'] = ' ';
  $args['labels']['post_type'] = ' ';
  $args['labels']['404'] = 'Not found: '; // Genesis 1.5 and later
return $args;
}

//* Set content width for Jetpack galleries.
if ( ! isset( $content_width ) )
    $content_width = 734;

//* Add support for 3-column footer widgets
add_theme_support( 'genesis-footer-widgets', 3 );

//* Unregister layout settings
genesis_unregister_layout( 'content-sidebar-sidebar' );
genesis_unregister_layout( 'sidebar-content-sidebar' );
genesis_unregister_layout( 'sidebar-sidebar-content' );

//* Unregister secondary sidebar
unregister_sidebar( 'sidebar-alt' );

//* Reposition the navigation
remove_action( 'genesis_after_header', 'genesis_do_nav' );
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
add_action( 'genesis_before_header', 'genesis_do_nav' );
add_action( 'genesis_before_header', 'genesis_do_subnav' );

//* Customize search form input box text
add_filter( 'genesis_search_text', 'uhw_search_text' );
function uhw_search_text( $text ) {
  return esc_attr( 'Search: enter text here.' );
}

//* Customize search form input button text
add_filter( 'genesis_search_button_text', 'uhw_search_button_text' );
function uhw_search_button_text( $text ) {
  return esc_attr( 'Go!' );
	}


//* Require and validate user passwords
add_action( 'wpmem_pre_register_data', 'my_pwd_validation', 10, 1 );
	function my_pwd_validation( $fields ) {

// passwords don't match error message:
	$pwdmatch_msg = "Sorry - your passwords did not match.";

// password doesn't meet criteria error message:
	$criteria_msg = "Your password needs at least one uppercase
letter, a lowercase letter and a number.";

$min_upper = 1; // minimum uppercase characters
$min_lower = 1; // minimum lowercase characters
$min_num   = 1; // minimum numbers
$min_len   = 8; // minimum password length

/**
* make sure your password fields option names are set:
* $fields['option_name']
*/
$pass1 = $fields['password'];
$pass2 = $fields['pwd_confirm'];

/**
* Unless you are removing criteria tests for password criteria,
* you don't need to change anything below here.
*/

global $wpmem_themsg;

if ( $pass1 != $pass2 ) { // make sure passwords match

// return passwords don't match message
$wpmem_themsg = $pwdmatch_msg;
return;

} else {

/**
* Here is the check for password minimum criteria.
* If there are any criteria you do not wish to use,
* you can remove the entire line for that case.
*/
switch( $pass1 ) {

// test for minimum number of uppercase characters
case(preg_match_all( '/[A-Z]/', $pass1, $o ) < $min_upper ):

// test for minimum number of lowercase characters
case(preg_match_all( '/[a-z]/', $pass1, $o ) < $min_lower ):

// test for minimum number of numbers
case(preg_match_all( '/[0-9]/', $pass1, $o ) < $min_num   ):

// test for minimum length
case( strlen( $pass1 ) < $min_len ):

// return password criteria not met message
$wpmem_themsg = $criteria_msg;

return;

break;

}

// everything passed

return;

}
}

//* Verify U Hills residence
add_action( 'wpmem_pre_register_data', 'uhw_street_validation', 10, 1 );
	function uhw_street_validation( $fields ) {

		// wrong-street error message:
		$street_msg = "Oops. Your address is not in University Hills.";

		// allowed streets as array
		$allowed_streets = array(

		'Bedford' || 'Bedford Avenue'||'Bedford Ave',

		'Creveling' || 'Creveling Drive'||'Creveling Dr',

		'Greenway' || 'Greenway Avenue' || 'Greenway Ave',

		'Kingsbury' || 'Kingsbury Boulevard'|| 'Kingsbury Blvd',

		'Midvale' || 'Midvale Avenue' || 'Midvale Ave',

		'Norwood' || 'Norwood Avenue' || 'Norwood Ave',

		'Overhill' || 'Overhill Drive'  || 'Overhill Dr',

		'Stratford' || 'Stratford Avenue'  || 'Stratford Ave',

		'Teasdale' ||  'Teasdale Avenue' || 'Teasdale Ave',

		'Warren' ||  'Warren Avenue' ||  'Warren Ave',

		);

		// globalize $wpmem_themsg so we can throw the error

		global $wpmem_themsg;

		//loop through the streets array

		$street_found = false;
		foreach ($allowed_streets as $street) {

			if (strstr($fields['addr1'] , $street)) {

				// the street is in the Hills
				$street_found = true;
				break;

			}

		}


	// if street is not in the Hills, set error message and end registration.
	$wpmem_themsg = ( ! $street_found ) ? $error_msg : '';

	return $street_msg;

	}

add_action( 'genesis_before_entry', 'uhw_postimg_above_title' );
/**
* Display Featured Image above Post Titles regardless of Content Archives settings
*
* Scope: Posts page (index)
*
* @author Sridhar Katakam
* @link   http://sridharkatakam.com/display-featured-images-post-titles-posts-page-genesis/
*/
function uhw_postimg_above_title() {

	if (is_page) {

		remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );

		add_action( 'genesis_entry_header', 'uhw_postimg', 9 );
}

else {
	return;
}

}

function uhw_postimg() {
echo '<a href="' . get_permalink() . '">' . genesis_get_image( array( 'size' => 'thumbnail' ) ). '</a>';
}

//* Customize the entry meta in the entry header (requires HTML5 theme support)
add_filter( 'genesis_post_info', 'sp_post_info_filter' );
function sp_post_info_filter($post_info) {
  $post_info = '[post_date] [post_comments] [post_edit]';
  return $post_info;
}

//* Customize the post meta function
add_filter( 'genesis_post_meta', 'post_meta_filter' );
function post_meta_filter($post_meta) {
	if (!is_page()) {
		$post_meta = '[post_categories before=""] [post_tags before="' . __( '', 'uhw' ) . '"]';
		return $post_meta;
	}
}

//* Customize the return to top of page text
add_filter( 'genesis_footer_backtotop_text', 'uhw_footer_backtotop_text' );
function uhw_footer_backtotop_text() {
  echo '<div class="footer-widgets-1"><p>Top</p></div>';
}

//* Change the footer text
add_filter('genesis_footer_creds_text', 'uhw_footer_creds_filter');
function uhw_footer_creds_filter( ) {
  return '<div class="footer-widgets-3"><p>Branding and design [footer_copyright] <a href="http://marybaum.com">Mary Baum.</a></p>
  <p>Content by the University Hills Women and <a href="mailto:pamela@uhillswomen.org">Pamela Forbes.</a></p>
  <p>Built on the <a href="http://www.studiopress.com/themes/genesis" title="Genesis Framework">Genesis Framework.</a></p></div>';

}

//* Modify the speak your mind title in comments
add_filter( 'comment_form_defaults', 'sp_comment_form_defaults' );
function sp_comment_form_defaults( $defaults ) {

  $defaults['title_reply'] = __( 'Talk to us!' );
  return $defaults;

}

// register widget areas
genesis_register_sidebar( array(
  'id'          => 'home-top',
  'name'        => __( 'Home - Top', 'uhw' ),
  'description' => __( 'This is the top section of the homepage.', 'uhw' ),
) );
genesis_register_sidebar( array(
  'id'          => 'home-middle-left',
  'name'        => __( 'Home - Middle Left', 'uhw' ),
  'description' => __( 'This is the middle left section of the homepage.', 'uhw' ),
) );
genesis_register_sidebar( array(
  'id'          => 'home-middle-right',
  'name'        => __( 'Home - Middle Right', 'uhw' ),
  'description' => __( 'This is the middle right section of the homepage.', 'uhw' ),
) );
genesis_register_sidebar( array(
  'id'          => 'home-bottom-left',
  'name'        => __( 'Home - Bottom Left', 'uhw' ),
  'description' => __( 'This is the bottom left section of the homepage.', 'uhw' ),
) );
genesis_register_sidebar( array(
  'id'          => 'home-bottom-right',
  'name'        => __( 'Home - Bottom Right', 'uhw' ),
  'description' => __( 'This is the bottom right section of the homepage.', 'uhw' ),
) );
