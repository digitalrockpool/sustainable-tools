<?php
/**
 * Sustainable Tools functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Sustainable Tools
 */

if ( ! function_exists( 'sustainable_tools_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function sustainable_tools_setup() {
		load_theme_textdomain( 'sustainable-tools', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		register_nav_menus( array(
			'menu-1' => esc_html__( 'Sustainable Tools', 'sustainable-tools' ),
			'menu-3' => esc_html__( 'Footer', 'sustainable-tools' ),
			'my-account' => esc_html__( 'My Account', 'sustainable-tools' )
		) );

		register_sidebar( array(
        	'name' => __( 'Chart Sidebar', 'sustainable-tools' ),
			'id' => 'chart-sidebar',
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'yardstick_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );

		// Add theme support for gutenberg color palette
		add_theme_support( 'editor-color-palette', array(
			array(
				'name'  => esc_html__( 'Orange', 'sustainable-tools' ),
				'slug' => 'orange',
				'color' => '#ff5421',
			),
			array(
				'name'  => esc_html__( 'Red', 'sustainable-tools' ),
				'slug' => 'red',
				'color' => '#ce211b',
			),
			array(
				'name'  => esc_html__( 'Yellow', 'sustainable-tools' ),
				'slug' => 'yellow',
				'color' => '#ff9a21',
			),
			array(
				'name'  => esc_html__( 'Dark Grey', 'sustainable-tools' ),
				'slug' => 'dark-grey',
				'color' => '#263238',
			),
			array(
				'name'  => esc_html__( 'Light Grey', 'sustainable-tools' ),
				'slug' => 'light-grey',
				'color' => '#e9ecef',
			),
			array(
				'name'  => esc_html__( 'White', 'sustainable-tools' ),
				'slug' => 'white',
				'color' => '#ffffff',
			),
		) );
		add_theme_support( 'disable-custom-colors' );
	}
endif;
add_action( 'after_setup_theme', 'sustainable_tools_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function sustainable_tools_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'sustainable_tools_content_width', 640 );
}
add_action( 'after_setup_theme', 'sustainable_tools_content_width', 0 );

/*** Enqueue custom jquery */
function custom_jquery() {

	wp_deregister_script('jquery');
	wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js', array(), null, true);

}
add_action('wp_enqueue_scripts', 'custom_jquery');


/*** Enqueue scripts and styles */
function sustainable_tools_scripts() {
	wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800' );
  wp_enqueue_style( 'datatables-style', '//cdn.datatables.net/1.10.21/css/jquery.dataTables.css' );
	wp_enqueue_style( 'bootstrap-styles', '//cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css' );
	wp_enqueue_style( 'bootstrap-datepicker-styles', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.standalone.min.css' );
	wp_enqueue_style( 'bootstrap-select-styles', '//cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css' );
	wp_enqueue_style( 'base-style', get_stylesheet_uri() );

	wp_enqueue_script( 'yardstick-skip-link-focus-fix', get_template_directory_uri().'/lib/js/skip-link-focus-fix.js', array(), '20151215', true );
	wp_enqueue_script( 'datatables', '//cdn.datatables.net/1.10.21/js/jquery.dataTables.js' );
	wp_enqueue_script( 'highcharts', '//code.highcharts.com/highcharts.js' );
	wp_enqueue_script( 'highcharts', '//code.highcharts.com/modules/no-data-to-display.js' ); /* display message when no data visible */
	wp_enqueue_script( 'bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.2.1', true );
	wp_enqueue_script( 'bootstrap-datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js', array('jquery'), '1.9.0' );
	wp_enqueue_script( 'bootstrap-select', '//cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js', array('jquery'), '1.13.9', true );
	// wp_enqueue_script( 'popper', '//unpkg.com/@popperjs/core@2', array('jquery'), '1.16.0', true ); /* DELETE IF NEW ONE WORKS */
	wp_enqueue_script( 'popper', '//cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js', array('jquery'), '2.9.2', true );
	wp_enqueue_script( 'font-awesome', '//kit.fontawesome.com/c5289195f2.js' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'sustainable_tools_scripts' );


/*** Add library includes */
require get_template_directory().'/lib/inc/custom-posts.php';
require get_template_directory().'/lib/inc/csv_uploads.php';
require get_template_directory().'/lib/inc/form-dropdown.php';
require get_template_directory().'/lib/inc/form-dynamic.php';
require get_template_directory().'/lib/inc/form-extras.php';
require get_template_directory().'/lib/inc/form-submission.php';
require get_template_directory().'/lib/inc/snippet-registration.php';
require get_template_directory().'/lib/inc/snippet-operations.php';
require get_template_directory().'/lib/inc/snippet-charity.php';
require get_template_directory() . '/lib/inc/shortcode.php';
require get_template_directory() . '/lib/inc/user-sessions.php';
//require get_template_directory() . '/lib/inc/widget-areas.php';


/*** Disable Admin Bar */
add_filter('show_admin_bar', '__return_false');


/*** Hide Admin Dashboard */
add_action( 'admin_init', 'redirect_non_admin_users' );
function redirect_non_admin_users() {
	if ( ! current_user_can( 'manage_options' ) && ('/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF']) ) :
		wp_redirect( home_url('/dashboard/') );
		exit;
	endif;
}

/*** Login Redirect */
function login_redirect( $redirect_to, $request, $user ){

	global $wpdb;

	$licence = $_SESSION['licence'];
	$registration_date = $_SESSION['registration_date'];

	if ( $licence == -1 && strtotime( $registration_date ) < strtotime( '-15 days' ) ) :
		return home_url('subscription');
	else :
    	return home_url('dashboard');
	endif;
}
add_filter( 'login_redirect', 'login_redirect', 10, 3 );



/*** Logout Redirect */
function logout_redirect(){
  	wp_redirect( home_url('/sign-in/') );
  	exit();
}
add_action( 'wp_logout', 'logout_redirect');

/*** Logged out user redirect */
add_action('template_redirect', 'logged_out_redirect');
function logged_out_redirect() {
	if ( !is_user_logged_in() && ( is_page_template( 'templates/charts.php' ) || is_page_template( 'templates/dashboard.php' ) || is_page_template( 'templates/data.php' ) || is_page_template( 'templates/settings.php' ) || is_page_template( 'templates/standard.php' ) || is_page_template( 'templates/yardstick.php' ) || is_singular( 'help' ) ) ) :
		wp_redirect( home_url('/sign-in/') );
		exit();
	endif;
}

/*** Custom Login Screen */
function custom_login() {
	echo '<link rel="stylesheet" type="text/css" href="'.get_stylesheet_directory_uri().'/lib/css/login.css" />';
}
add_action('login_head', 'custom_login');

function login_logo_url() {
	return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'login_logo_url' );

function login_logo_url_title() {
	return 'Yardstick. Monitor. Measure. Report';
}
add_filter( 'login_headertitle', 'login_logo_url_title' );

function custom_register_url( $register_url ) {
    $register_url = get_permalink( $register_page_id = 1496 );
    return $register_url;
}
add_filter( 'register_url', 'custom_register_url' );
add_filter( 'login_display_language_dropdown' , '__return_false' ); 