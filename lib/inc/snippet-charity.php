<?php ob_start();

/* Includes: CHARITY SNIPPETS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
@license	GPL-2.0+ */


// SETTINGS
function charity_add_setting(  $set_id, $cat_id, $title, $title_singular ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$entry_date = date( 'Y-m-d H:i:s' );

	$charity_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=$cat_id AND NOT EXISTS (SELECT tag FROM custom_tag WHERE master_tag.id=custom_tag.tag_id AND cat_id=$cat_id AND loc_id=$master_loc) ORDER BY tag ASC" );

	if( empty( $charity_dropdowns ) ) : ?>

		<p>All <?php echo strtolower( $title ); ?> have been added. If you require a new <?php echo strtolower( $title_singular ); ?> please email <a href="mailto:support@yardstick.co.uk" title="support@yardstick.co.uk">support@yardstick.co.uk</a>.</p> <?php

	else : ?>

		<form method="post" id="add-charity-settings" name="add-charity-settings" class="needs-validation" novalidate>
			<div id="repeater-field">
				<div class="entry form-row mb-1">
					<div class="col-10">
						<select class="form-control" id="set-donation-type" name="set-donation-type[]" required>
							<option value="" selected disabled>Select <?php echo $title_singular ?> *</option> <?php

							foreach( $charity_dropdowns as $charity_dropdown ) : ?>
								<option value="<?php echo $charity_dropdown->id ?>"><?php echo $charity_dropdown->tag ?></option> <?php
							endforeach; ?>

						</select>
						<div class="invalid-feedback">Please select charity type</div>
					</div>

					<div class="col-2">
						<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
					</div>
				</div>
			</div>

			<div class="form-row">
				<div class="col-2 offset-10 mb-3"><button class="btn btn-primary float-none" type="submit" name="add-charity-settings">Add</button></div>
			</div>
		</form> <?php

		$set_donation_type_array = $_POST['set-donation-type'];

		if ( isset( $_POST['add-charity-settings'] ) ) :

			foreach( $set_donation_type_array as $index => $set_donation_type_array ) :

				$tag_id = $set_donation_type_array;

				$tag_check = $wpdb->get_row( "SELECT tag_id FROM custom_tag WHERE tag_id=$tag_id AND loc_id=$master_loc" );
				if( empty( $tag_check ) ) :

					$wpdb->insert( 'custom_tag',
						array(
							'entry_date' => $entry_date,
							'record_type' => 'entry',
							'tag' => NULL,
							'tag_id' => $tag_id,
							'size' => NULL,
							'unit_id' => NULL,
							'custom_cat' => NULL,
							'cat_id' => $cat_id,
							'parent_id' => 0,
							'user_id' => $user_id,
							'active' => 1,
							'loc_id' => $master_loc
						)
					);

					$parent_id = $wpdb->insert_id;

					$wpdb->update( 'custom_tag',
						array(
							'parent_id' => $parent_id,
						),
						array(
							'id' => $parent_id
						)
					);

				endif;

			endforeach;

			header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
			ob_end_flush();

		endif;
	endif;
}




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
