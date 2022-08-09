<?php

/* Includes: SHORTCODE

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */

function shortcode_dates ( $measure, $start, $end ) {

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

}

// CHART MENU ITEM
function shortcode_chart_menu( $atts ) {
	
	$master_chart = $atts['chart'];
	
	global $wpdb;
	
	$site_url = get_site_url();
	$master_loc = $_SESSION['master_loc'];
	
	$master_chart_row = $wpdb->get_row( "SELECT chart, frequency, filter_dropdown FROM master_chart WHERE id=$master_chart" );
	$chart_title = $master_chart_row->chart;
	$chart_frequency = $master_chart_row->frequency;
	$chart_filter = $master_chart_row->filter_dropdown;
	
	if( $chart_filter == 'chart_dropdown_master_tag' ) :
	
		$cat_id_lookup = $wpdb->get_row( "SELECT id FROM master_category WHERE category='$chart_title'");
		$cat_id = $cat_id_lookup->id;
	
		$filter_row = $wpdb->get_row( "SELECT master_tag.tag as tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=$cat_id AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 ORDER BY master_tag.tag ASC" );
		$filter = '&filter='.str_replace( ' ', '-', strtolower( $filter_row->tag ) );
	
	endif;
	
	$measure_toggle = $_SESSION['measure_toggle'];
	
	if(  $measure_toggle == 82 ) :
	
		$measure = 'day';
		$start_date = date( 'd/m/Y', strtotime('-8 days'));
		$end_date = date('d/m/Y',strtotime('-1 days'));
		
	elseif( $measure_toggle == 83 ) :
	
		$measure = 'week';
		$day_number = date('N');
		$start_date = date('d/m/Y',strtotime('-'.( 90+$day_number ).' days'));
		$end_date = date('d/m/Y', strtotime('-'.$day_number.' days'));
		
	elseif( $measure_toggle == 84 ) :
		
		$measure = 'month';
		$start_date = date('01/m/Y', strtotime('-12 months')); 
		$end_date = date('d/m/Y', strtotime('last day of previous month'));
		
	else :

		$measure = 'year';
		$fy_day = $_SESSION['fy_day'];
		$fy_month = $_SESSION['fy_month'];
		$year = date('Y');
		$last_year = date('Y',strtotime('-1 year'));
		$fy_date_create = strtotime("$year-$fy_month-$fy_day");
		$fy_date = date('Y-m-d', $fy_date_create);
		$today = date('Y-m-d');

		if( $fy_date >= $today ) :
			$end_date_create = strtotime("$last_year-$fy_month-$fy_day");
		else :
			$end_date_create = strtotime("$year-$fy_month-$fy_day");
		endif;

		$end_date = date('d/m/Y', $end_date_create);
		$minus_ten_calc = "$last_year-$fy_month-$fy_day";
		$start_date = date( 'd/m/Y', strtotime('-10 years', strtotime($minus_ten_calc)));  

	endif;
	
	$chart = str_replace( ' ', '-', strtolower( $chart_title ) );
	
	if( !empty( $chart_frequency ) ) : $frequency = '&frequency='.$measure; endif;
	
	$string = $site_url.'/charts/?chart='.$chart.$frequency.$filter.'&wdt_column_filter[1]='.$start_date.'|'.$end_date;
	
	return $string;
}
add_shortcode('shortcode-chart-menu', 'shortcode_chart_menu');