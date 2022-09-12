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
			'menu-1' => esc_html__( 'Primary', 'sustainable-tools' ),
			'menu-2' => esc_html__( 'Secondary', 'sustainable-tools' ),
			'menu-3' => esc_html__( 'Footer', 'sustainable-tools' ),
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
	wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js', array(), null, true);

}
add_action('wp_enqueue_scripts', 'custom_jquery');


/*** Enqueue scripts and styles */
function sustainable_tools_scripts() {
	wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Roboto:100,200,300,400,500,600,700,800' );
	wp_enqueue_style( 'font-awesome', '//pro.fontawesome.com/releases/v5.10.1/css/all.css">' );
  wp_enqueue_style( 'datatables-style', '//cdn.datatables.net/1.10.21/css/jquery.dataTables.css' );
	wp_enqueue_style( 'bootstrap-styles', '//stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css' );
	wp_enqueue_style( 'datepicker-styles', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker3.standalone.min.css' );
	wp_enqueue_style( 'select-styles', '//cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css' );
	wp_enqueue_style( 'base-style', get_stylesheet_uri() );

	wp_enqueue_script( 'yardstick-skip-link-focus-fix', get_template_directory_uri().'/lib/js/skip-link-focus-fix.js', array(), '20151215', true );
	wp_enqueue_script( 'datatables', '//cdn.datatables.net/1.10.21/js/jquery.dataTables.js' );
	wp_enqueue_script( 'highcharts', '//code.highcharts.com/highcharts.js' );
	wp_enqueue_script( 'highcharts', '//code.highcharts.com/modules/no-data-to-display.js' ); /* display message when no data visible */
	wp_enqueue_script( 'popper', '//cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js', array('jquery'), '1.16.0', true );
	wp_enqueue_script( 'bootstrap', '//stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js', array('jquery'), '4.5.0', true );
	wp_enqueue_script( 'datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js', array('jquery'), '1.8.0' );
	wp_enqueue_script( 'select', '//cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js', array('jquery'), '1.13.9', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'sustainable_tools_scripts' );


/*** Add library includes */
require get_template_directory().'/lib/inc/csv_uploads.php';
require get_template_directory().'/lib/inc/form-dropdown.php';
require get_template_directory().'/lib/inc/form-dynamic.php';
require get_template_directory().'/lib/inc/form-extras.php';
require get_template_directory().'/lib/inc/form-submission.php';
require get_template_directory().'/lib/inc/snippet-registration.php';
require get_template_directory().'/lib/inc/snippet-account.php';
require get_template_directory().'/lib/inc/snippet-operations.php';
require get_template_directory().'/lib/inc/snippet-charity.php';
require get_template_directory() . '/lib/inc/shortcode.php';
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


// SESSIONS
if ( is_user_logged_in() ) :

	session_start();

	global $wpdb;

	$user_id = get_current_user_id();

	$master_locs = $wpdb->get_row( "SELECT master_loc FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.id WHERE relation_user.user_id=$user_id" );
	$_SESSION['master_loc'] = $master_locs->master_loc;
	$master_loc = $_SESSION['master_loc'];

	$user_role = $wpdb->get_row( "SELECT role_id, tag FROM relation_user INNER JOIN master_tag ON relation_user.role_id=master_tag.id WHERE user_id=$user_id AND active=1 ORDER BY relation_user.id DESC" ) ;
	$_SESSION['user_role'] = $user_role->role_id;
	$_SESSION['user_role_tag'] = $user_role->tag;

	$wp_user = wp_get_current_user();
	$wp_user_roles = ( array ) $wp_user->roles;
	if( in_array( 'contributor', $wp_user_roles ) || in_array( 'administrator', $wp_user_roles ) ) : $_SESSION['wp_user_role'] = 'has_account'; else : $_SESSION['wp_user_role'] = 'not_subscribed'; endif;

	$plan = $wpdb->get_row( "SELECT plan_id, licence, city, county, profile_location.country, discount FROM profile_location INNER JOIN master_country ON profile_location.country=master_country.country WHERE master_loc=$master_loc ORDER BY profile_location.id DESC" );
	$_SESSION['plan_id'] = $plan->plan_id;
	$_SESSION['licence'] = $plan->licence;
	$_SESSION['loc_city'] = $plan->city;
	$_SESSION['loc_county'] = $plan->county;
	$_SESSION['loc_country'] = $plan->country;
	$_SESSION['discount'] = $plan->discount;

	$registration_dates = $wpdb->get_row( "SELECT profile_location.entry_date FROM profile_location INNER JOIN relation_user ON relation_user.loc_id=profile_location.id WHERE relation_user.user_id=$user_id" );
	$_SESSION['registration_date'] = $registration_dates->entry_date;

	/* $micro_value = $wpdb->get_row( "SELECT monthly_gbp, annual_gbp FROM master_plan WHERE id=2" );
	$business_value = $wpdb->get_row( "SELECT monthly_gbp, annual_gbp FROM master_plan WHERE id=3" );
	$enterprise_value = $wpdb->get_row( "SELECT monthly_gbp, annual_gbp FROM master_plan WHERE id=4" );
	$organisation_value = $wpdb->get_row( "SELECT annual_gbp FROM master_plan WHERE id=5" );

	$_SESSION['micro_month_value'] = $micro_value->monthly_gbp;
	$_SESSION['micro_annual_value'] = $micro_value->annual_gbp;
	/* $_SESSION['business_month_value'] = $business_value->monthly_gbp;
	$_SESSION['business_annual_value'] = $business_value->annual_gbp;
	$_SESSION['enterprise_month_value'] = $enterprise_value->monthly_gbp;
	$_SESSION['enterprise_annual_value'] = $enterprise_value->annual_gbp;
	$_SESSION['organisation_annual_value'] = $organisation_value->annual_gbp;

	$GBPUSD_exchange = $wpdb->get_row( "SELECT usd_exchange FROM master_country WHERE id=77" );
	$_SESSION['GBPUSD'] = $GBPUSD_exchange->usd_exchange;
	$GBPUSD = $_SESSION['GBPUSD'];

	$subscription_country = $_SESSION['loc_country'];
    $subscription_conversions = $wpdb->get_row( "SELECT discount, coupon FROM master_country WHERE country='$subscription_country'" );
	$_SESSION['subscription_discount'] = $subscription_conversions->discount;
	$_SESSION['subscription_coupon'] = $subscription_conversions->coupon;

	$micro_month_GBP = $_SESSION['micro_month_value'];
	$micro_annual_GBP = $_SESSION['micro_annual_value'];
	$micro_month = ($micro_month_GBP) / 100 * (100 - $discount);
	$micro_month_cap = $micro_month=( $micro_month <= $micro_month_GBP )?$micro_month:$micro_month_GBP;
	$micro_annual = ($micro_annual_GBP) / 100 * (100 - $discount);
	$micro_annual_cap = $micro_annual=( $micro_annual <= $micro_annual_GBP )?$micro_annual:$micro_annual_GBP;
	$_SESSION['subscription_micro_month_value'] = $micro_month_cap;
	$_SESSION['subscription_micro_annual_value'] = $micro_annual_cap; */

	$standards_assigned = $wpdb->get_var( "SELECT COUNT(DISTINCT parent_id) FROM relation_standard WHERE user_id=$user_id AND active=1" );
	$_SESSION['standards_assigned'] = $standards_assigned;

	$loc_preferences = $wpdb->get_row( "SELECT profile_locationmeta.id, industry, master_tag.tag AS industry_tag, sector, sector.tag AS sector_tag, subsector, subsector.tag AS subsector_tag, other, fy_day, fy_month, currency, geo_type, very_local, local, calendar, calendar.tag AS calendar_tag, parent_id FROM profile_locationmeta LEFT JOIN master_tag ON profile_locationmeta.industry=master_tag.id LEFT JOIN master_tag sector ON profile_locationmeta.sector=sector.id LEFT JOIN master_tag subsector ON profile_locationmeta.subsector=subsector.id LEFT JOIN master_tag calendar ON profile_locationmeta.calendar=calendar.id WHERE profile_locationmeta.id IN (SELECT MAX(id) FROM profile_locationmeta WHERE loc_id=$master_loc)" );

	$_SESSION['report_active'] = $loc_preferences->parent_id; /* used to identify first report settings submission */
	$_SESSION['report_initiate_id'] = $loc_preferences->id; /* used to update first report settings submission */
	$_SESSION['industry_id'] = $loc_preferences->industry;
	$_SESSION['sector_id'] = $loc_preferences->sector;
	$_SESSION['subsector_id'] = $loc_preferences->subsector;
	$_SESSION['industry'] = $loc_preferences->industry_tag;
	$_SESSION['sector'] = $loc_preferences->sector_tag;
	$_SESSION['subsector'] = $loc_preferences->subsector_tag;
	$_SESSION['other'] = $loc_preferences->other;
	$_SESSION['fy_day'] = $loc_preferences->fy_day;
	$_SESSION['fy_month'] = $loc_preferences->fy_month;
	$_SESSION['currency'] = $loc_preferences->currency;
	$_SESSION['geo_type'] = $loc_preferences->geo_type;

	if( $_SESSION['geo_type'] == 143  ) :

		$_SESSION['very_local'] = $loc_preferences->very_local;
		$_SESSION['local'] = $loc_preferences->local;

	else :

		$_SESSION['very_local'] = $plan->city;
		$_SESSION['local'] = $plan->county;

	endif;

	$_SESSION['calendar'] = $loc_preferences->calendar_tag;
	$_SESSION['calendar_id'] = $loc_preferences->calendar;

	$measure_toggle = $wpdb->get_row( "SELECT master_tag.tag, tag_id FROM custom_tag INNER JOIN master_tag ON custom_tag.tag_id=master_tag.id WHERE loc_id=$master_loc AND custom_tag.cat_id=13 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" );
	$_SESSION['measure_toggle'] = $measure_toggle->tag_id;
	$_SESSION['measure_toggle_name'] = $measure_toggle->tag;

	$tag_toggle = $wpdb->get_row( "SELECT active FROM custom_tag WHERE loc_id=$master_loc AND cat_id=22 AND tag IS NULL AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" );
	$_SESSION['tag_toggle'] = $tag_toggle->active;

	$measure_count = $wpdb->get_var( "SELECT COUNT(id) FROM data_measure WHERE active=1 AND loc_id=$master_loc" );
	$_SESSION['measure_count'] = $measure_count;

else :

	$_SESSION['fy_day'] = 1;
	$_SESSION['fy_month'] = 1;

endif;
