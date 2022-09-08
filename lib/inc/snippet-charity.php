<?php ob_start();

/* Includes: CHARITY SNIPPETS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
@license	GPL-2.0+ */






// CHART FILTER
function chart_dropdown_charity() {

	global $wpdb;

	$master_loc = $_SESSION['master_loc'];
	$filter = str_replace( '   ', ' - ', str_replace( '-', ' ', $_GET['filter'] ) ); // NEED TO GET THIS WORKING

	$dropdowns = $wpdb->get_results( "SELECT master_tag.tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE custom_tag.cat_id=4 AND loc_id=$master_loc AND active=1 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY master_tag.tag ASC" );

	foreach( $dropdowns as $dropdown ) :
		$value_tag = $dropdown->tag;
		if( $filter == strtolower( $value_tag ) ) : $selected = 'selected'; else : $selected = ''; endif; ?>
		<option value="<?php echo $value_tag ?>" <?php echo $selected ?>><?php echo $value_tag ?></option> <?php
	endforeach;

}


// CHART DONATION DISTANCE MENU ITEM
function shortcode_chart_donation_distance_menu() {

	global $wpdb;

	$site_url = get_site_url();
	$master_loc = $_SESSION['master_loc'];

	$filter_row = $wpdb->get_row( "SELECT master_tag.tag as tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=15 AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 ORDER BY master_tag.tag ASC" );
	$filter = str_replace( ' ', '-', strtolower( $filter_row->tag ) );

	$measure_toggle = $_SESSION['measure_toggle'];

	$today = date( 'Y-m-d' );
	$fy_day = $_SESSION['fy_day'];
	$fy_month = $_SESSION['fy_month'];
	$fy_year_current = date( 'Y' );
	$fy_date_convert = date_create( $fy_month.'/'.$fy_day.'/'.$fy_year_current );
	$fy_date = date_format( $fy_date_convert, 'Y-m-d' );

	if( $fy_date < $today  ) : $fy_year = date( 'Y', strtotime( '-1 year' ) ); else : $fy_year = date( 'Y', strtotime( '-2 year' ) ); endif;

	$year_fy_start_date = $fy_month.'/'.$fy_day.'/'.$fy_year;
	$year_fy_start = date( 'Y-m-d', strtotime( $year_fy_start_date ) );
	$year_fy_start_10 = date( 'Y-m-d', strtotime( '-9 years', strtotime( $year_fy_start_date ) ) );
	$year_fy_end = date( 'Y-m-d', strtotime( '1 year -1 day', strtotime( $year_fy_start_date ) ) );
	$month_end = date( 'Y-m-d', strtotime('last day of previous month'));
	$month_start = date( 'Y-m-01', strtotime('-12 months'));
	$day_number = date( 'N' );
	$week_start = date( 'Y-m-d', strtotime( '-'.( 90+$day_number ).' days') );
	$week_end = date( 'Y-m-d', strtotime( '-'.$day_number.' days' ) );
	$day_start = date( 'Y-m-d', strtotime( '-14 day') );
	$day_end = date( 'Y-m-d', strtotime( '-1 day' ) );

	if( $measure_toggle == 82 ) :

		$measure = 'day';
		$start = $day_start;
		$end = $day_end;

	elseif( $measure_toggle == 83 ) :

		$measure = 'week';
		$start = $week_start;
		$end = $week_end;

	elseif( $measure_toggle == 84 ) :

		$measure = 'month';
		$start = $month_start;
		$end = $month_end;

	else :

		$measure = 'year';
		$start = $year_fy_start_10;
		$end = $year_fy_end;

	endif;

	$string = '<a class="mega-menu-link" href="'.$site_url.'/charts/?chart=fuel&series=usage&filter='.$filter.'&frequency='.$measure.'&start='.$start.'&end='.$end.'">Fuel</a>';

	return $string;
}
add_shortcode('shortcode-chart-donation-distance-menu', 'shortcode_chart_donation_distance_menu');
