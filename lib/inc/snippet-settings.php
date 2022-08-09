<?php

/* Includes: Setting Snippets - Generic settings only.

@package	Yardstick
@author		Digital Rockpool
@link		https://www.yardstick.co.uk
@copyright	Copyright (c) 2021, Digital Rockpool LTD
@license	GPL-2.0+ */

// ADD DATA
function data_add_setting() {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$entry_date = date( 'Y-m-d H:i:s' ); ?>

	<form method="post" id="add-data-settings" name="add-data-settings">

		<h5>Module Visability</h5> <!-- swap out with toggle --> <?php

		$data_settings = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id='50'" );
		foreach( $data_settings as $data_setting ) :

			$switch_id = $data_setting->id;
			$switch_title = $data_setting->tag;
			$switch = strtolower($switch_title).'-switch-';

			$custom_data_settings = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag_id=$switch_id AND loc_id=$master_loc ORDER BY id DESC" );
			$switch_toggle = $custom_data_settings->tag;

			if( $switch_toggle === 'off'  ) : $switch_toggle = 'selected'; else : $switch_toggle = ''; endif; ?>

			<div class="form-group row">
    		<label for="staticEmail" class="col-sm-2 col-form-label"><?php echo $switch_title ?></label>
		    <div class="col-sm-10">
					<select class="form-control" name="set-data-setting[]" id="<?php echo $switch ?>on" >
	 					<option value="on">On</option>
		 				<option value="off"<?php echo $switch_toggle ?>>Off</option>
					</select>
		    	<input type="hidden" name="set-tag-id[]" value="<?php echo $switch_id ?>">
					<input type="hidden" name="set-cat-id[]" value="50">
				</div>
			</div> <?php

		endforeach;

		$custom_data_date = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE cat_id='48' AND loc_id=$master_loc ORDER BY id DESC" );
		$custom_data_table = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE cat_id='49' AND loc_id=$master_loc ORDER BY id DESC" );
		$data_date = $custom_data_date->tag ?: 1;
		$data_table = $custom_data_table->tag ?: 25; ?>

		<h5 class="border-top mt-4 pt-3">Edit Data Table Settings</h5>
		<div class="form-row">
			<div class="form-group col-6">
				<label for="set-default-date-range-number">Default Date Range<span class="text-danger"> *</span></label>
				<div class="input-group">
					<input type="number" class="form-control" id="set-default-date-range-number" name="set-data-setting[]" min="1" max="365" step="1" value="<?php echo $data_date ?>" required>
					<select class="form-control" id="set-default-date-range" name="set-tag-id[]">
						<option value="310">days</option>
						<option value="311" selected>month(s)</option>
					</select>
					<input type="hidden" name="set-cat-id[]" value="48">
				</div>
			</div>
			<div class="form-group col-6">
				<label for="set-default-table-rows">Default Table Rows<span class="text-danger"> *</span></label>
				<input type="number" class="form-control" id="set-default-table-rows" name="set-data-setting[]" min="1" step="1" value="<?php echo $data_table ?>" required>
				<input type="hidden" name="set-tag-id[]" value="312">
				<input type="hidden" name="set-cat-id[]" value="49">
			</div>
		</div>

		<div class="form-row">
			<div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="add-data-settings">Update</button></div>
		</div>

	</form><?php

	$set_data_setting_array = $_POST['set-data-setting'];
	$set_tag_id_array = $_POST['set-tag-id'];
	$set_cat_id_array = $_POST['set-cat-id'];

	if ( isset( $_POST['add-data-settings'] ) ) :

		foreach( $set_data_setting_array as $index => $set_data_setting_array ) :

			$tag = $set_data_setting_array;
			$tag_id = $set_tag_id_array[$index];
			$cat_id = $set_cat_id_array[$index];
			$tag_check = $wpdb->get_row( "SELECT parent_id FROM custom_tag WHERE tag_id=$tag_id AND loc_id=$master_loc" );

			if( empty( $tag_check ) ) :

				$wpdb->insert( 'custom_tag',
					array(
						'entry_date' => $entry_date,
						'record_type' => 'entry',
						'tag' => $tag,
						'tag_id' => $tag_id,
						'size' => NULL,
						'unit_id' => NULL,
						'custom_cat' => NULL,
						'cat_id' => $cat_id,
						'active' => 1,
						'parent_id' => 0,
						'user_id' => $user_id,
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

			else :

				$wpdb->insert( 'custom_tag',
					array(
						'entry_date' => $entry_date,
						'record_type' => 'entry_revision',
						'tag' => $tag,
						'tag_id' => $tag_id,
						'size' => NULL,
						'unit_id' => NULL,
						'custom_cat' => NULL,
						'cat_id' => $cat_id,
						'active' => 1,
						'parent_id' => $tag_check->parent_id,
						'user_id' => $user_id,
						'loc_id' => $master_loc
					)
				);

		endif;

		endforeach;

		header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
		ob_end_flush();

	endif;

}

// ADD REPORTING
function reporting_add_setting() {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$plan_id = $_SESSION['plan_id'];

	$entry_date = date( 'Y-m-d H:i:s' );

	$report_initiate_id = $_SESSION['report_initiate_id']; /* used to update first report settings submission */
	$report_active = $_SESSION['report_active']; /* used to identify first report settings submission - parent_id */

	$set_fy_day = $_SESSION['fy_day'];
	$set_fy_month = $_SESSION['fy_month'];
	$set_fy_date = $set_fy_day.'-'.$set_fy_month.'-2000';

	$set_calendar_id = $_SESSION['calendar_id'];
	$set_calendar = $_SESSION['calendar'];
	$set_currency = $_SESSION['currency'];
	$set_geo_type = $_SESSION['geo_type'];

	$set_local = $_SESSION['local'];
	$set_very_local = $_SESSION['very_local'];

	$set_local_location = $_SESSION['loc_county'];
	$set_very_local_location = $_SESSION['loc_city'];

	$set_industry_id = $_SESSION['industry_id'];
	$set_sector_id = $_SESSION['sector_id'];
	$set_subsector_id = $_SESSION['subsector_id'];
	$set_industry = $_SESSION['industry'];
	$set_sector = $_SESSION['sector'];
	$set_subsector = $_SESSION['subsector'];
	$set_other = $_SESSION['other'];

	$split = explode( "|", $set_other );
	$set_industry_other = $split[0];
	$set_sector_other = $split[1];
	$set_subsector_other = $split[2];

	if( !empty( $set_industry_other ) ) : $set_industry_separator = ': '; endif;
	if( !empty( $set_sector_other ) ) : $set_sector_separator = ': '; endif;
	if( !empty( $set_subsector_other ) ) : $set_subsector_separator = ': '; endif;?>

	<p><span class="text-danger"><i class="fas fa-exclamation-circle"></i></i></span> It is recommended that report settings are only entered once and not changed after submission.</p>

	<p class="small">Fields marked with an asterisk <span class="text-danger">*</span> are required.</p>


	<form method="post" name="set-reporting-form" id="set-reporting-update"> <?php

		if( $report_active == 0 ) :

			$display_none_reporting_off = 'display:none;';

		else :

			$display_none_reporting_on = 'display:none;'; ?>

			<div class="form-row">
				<div class="col-12"><label>Change settings <span class="text-danger">*</span></label>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="report-change-set">
						<label class="form-check-label" for="report-change-set">I acknowledge this may affect the annual reports</label>
					</div>
				</div>
			</div> <?php

		endif; ?>

		<h5 class="border-top mt-4 pt-3">Reporting Period</h5>
		<p class="form-text">Add the month and day you would like your reporting year to start. For example you may want to choose the same date as the start of your financial year.</p>

		<div class="form-row">
			<div class="form-group col-6">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text"><i class="far fa-calendar-alt"></i></div>
					</div>
					<input type="text" class="form-control date reporting-on" style="<?php echo $display_none_reporting_on ?>" name="set-reporting-date" id="set-reporting-date" aria-describedby="set-reporting-date" placeholder="dd-mmm" value="<?php echo date_format( date_create( $set_fy_date ),'d-M' ); ?>" required>

					<input type="text" class="form-control date reporting-off" style="<?php echo $display_none_reporting_off ?>" aria-describedby="set-reporting-date" value="<?php echo date_format( date_create( $set_fy_date ),'d-M' ); ?>" readonly>
				</div>
			</div>
		</div>

		<h5 class="border-top mt-4 pt-3">Currency and Calendar</h5>
		<p class="form-text">Only one currency and calendar is supported per property. If your business uses multiple currencies choose the most frequently used. Data is entered using the Gregorian - International calendar but reports are generated using the regional calendar, if selected</p>

		<div class="form-row">
			<div class="form-group col-6">
				<label for="set-reporting-currency-code">Currency Code<span class="text-danger"> *</span></label>
				<select class="form-control reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-currency-code" name="set-reporting-currency-code"> <!-- multi-select doesn't work with reporting-on/off -->
					<option value="" selected disabled>Select Currency</option> <?php

					$currencies = $wpdb->get_results( "SELECT currency_alpha FROM master_country GROUP BY currency_alpha ORDER BY currency_alpha ASC" );

					foreach( $currencies as $currency ) :

						$currency_code = $currency->currency_alpha;

						if( $currency_code == $set_currency ) : $selected = 'selected'; else : $selected = ''; endif; ?>

						<option value="<?php echo $currency_code ?>" <?php echo $selected ?>><?php echo $currency_code ?></option> <?php

					endforeach; ?>
				</select>
				<input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_currency ?>" readonly>
			</div>
			<div class="form-group col-6">
				<label for="set-reporting-calendar">Calendar<span class="text-danger"> *</span></label>
				<select class="form-control reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-calendar" name="set-reporting-calendar">
					<option value="" selected disabled>Select Calendar</option> <?php

					$calendars = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=37 ORDER BY tag ASC" );

					foreach( $calendars as $calendar ) :

						$calendar_id = $calendar->id;
						$calendar_name = $calendar->tag;

						if( $calendar_id == $set_calendar_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

						<option value="<?php echo $calendar_id ?>" <?php echo $selected ?>><?php echo $calendar_name ?></option> <?php

					endforeach; ?>
				</select>
				<input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_calendar ?>" readonly>
			</div>
		</div>

		<h5 class="border-top mt-4 pt-3">Geographical Boundaries</h5>
		<p class="form-text">Reporting can be based on your location or by distance. If you chose distance, please enter the distance in kilometres that you consider to be very local and local. These values will reflect the remoteness your business.</p> <?php

		if( empty( $_SESSION['loc_city'] ) ) : ?>

			<p><span class="text-danger"><i class="fas fa-exclamation-circle"></i></i></span> Please enter your location before setting your geographical boundaries.</p> <?php

		endif;

		if( $set_geo_type == 143 ) :

			$unit = ' (kms)';
			$set_distance_very_local = $set_very_local;
			$set_distance_local = $set_local;
			$checked_distance = 'checked';
			$reporting_on_distance ='reporting-on';

		else :

			$unit = '';
			$set_location_very_local = $set_very_local;
			$set_location_local = $set_local;
			$checked_location = 'checked';
			$reporting_on_location ='reporting-on';

		endif; ?>

		<div class="form-check form-check-inline reporting-on" style="<?php echo $display_none_reporting_on ?>">
			<input class="form-check-input set-geo-type" type="radio" name="set-reporting-geo-type" id="set-reporting-distance" value="143" <?php echo $checked_distance ?>>
			<label class="form-check-label" for="set-reporting-distance">By distance (km)</label>
		</div>

		<div class="form-check form-check-inline reporting-on" style="<?php echo $display_none_reporting_on ?>">
			<input class="form-check-input set-geo-type" type="radio" name="set-reporting-geo-type" id="set-reporting-location" value="144" <?php echo $checked_location ?>>
			<label class="form-check-label" for="set-reporting-distance">By location</label>
		</div>

		<div class="form-row mt-2 distance-on <?php echo $reporting_on_distance ?>" style="display:none;">
			<div class="form-group col-6">
				<label for="set-reporting-distance-very-local">Very Local<?php echo $unit ?><span class="text-danger"> *</span></label>
				<input type="number" class="form-control distance-on-required" name="set-reporting-distance-very-local" id="set-reporting-distance-very-local" aria-describedby="set-reporting-distance-very-local" min="1" step="0.01" value="<?php echo $set_distance_very_local ?>">
			</div>

			<div class="form-group col-6">
				<label for="set-reporting-distance-local">Local<?php echo $unit ?><span class="text-danger"> *</span></label>
				<input type="number" class="form-control distance-on-required" name="set-reporting-distance-local" id="set-reporting-distance-local" aria-describedby="set-reporting-distance-local" min="1" step="0.01" value="<?php echo $set_distance_local ?>">
			</div>
		</div>

		<div class="form-row mt-2 location-on <?php echo $reporting_on_location ?>" style="display:none;">
			<div class="form-group col-6">
				<label for="set-reporting-location-very-local">Very Local<span class="text-danger"> *</span></label>
				<input type="text" class="form-control location-on-required" name="set-reporting-location-very-local" id="set-reporting-location-very-local" aria-describedby="set-reporting-location-very-local" value="<?php echo $set_very_local_location ?>" readonly>
			</div>

			<div class="form-group col-6">
				<label for="set-reporting-location-local">Local<span class="text-danger"> *</span></label>
				<input type="text" class="form-control location-on-required" name="set-reporting-location-local" id="set-reporting-location-local" aria-describedby="set-reporting-location-local" value="<?php echo $set_local_location ?>" readonly>
			</div>
		</div>

		<div class="form-row reporting-off" style="<?php echo $display_none_reporting_off ?>">
			<div class="form-group col-6">
				<label>Very Local<?php echo $unit ?></label>
				<input type="text" class="form-control" value="<?php echo $set_very_local ?>" readonly>
			</div>

			<div class="form-group col-6">
				<label>Local<?php echo $unit ?></label>
				<input type="text" class="form-control" value="<?php echo $set_local ?>" readonly>
			</div>
		</div>

		<h5 class="border-top mt-4 pt-3">Benchmarking</h5>
		<p class="form-text">Benchmark your performance against other businesses in your industry, sector and subsector.</p>

		<div class="form-row">
			<div class="form-group col-4">
				<label for="set-reporting-industry">Industry<span class="text-danger"> *</span></label>
				<select class="form-control reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-industry" name="set-reporting-industry">
					<option value="" selected disabled>Select Industry</option> <?php

					$industries = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=1 ORDER BY tag ASC" );

					foreach( $industries as $industry ) :

						$industry_id = $industry->id;
						$industry_tag = $industry->tag;

						if( $industry_tag == $set_industry ) : $selected = 'selected'; else : $selected = ''; endif; ?>

						<option value="<?php echo $industry_id ?>" <?php echo $selected ?>><?php echo $industry_tag ?></option> <?php

					endforeach; ?>
				</select> <?php

				if( $set_industry_id == 145 ) : $industry_other_display = 'reporting-on'; endif; ?>

				<input type="text" class="form-control <?php echo $industry_other_display ?> industry-other-on mt-1" style="display: none;" name="set-reporting-industry-other" id="set-reporting-industry-other" aria-describedby="set-reporting-industry-other" value="<?php echo $set_industry_other ?>">
				<input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_industry.$set_industry_separator.$set_industry_other ?>" readonly>
			</div>
			<div class="form-group col-4">
				<label for="set-reporting-sector">Sector<span class="text-danger"> *</span></label>
				<select class="form-control reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-sector" name="set-reporting-sector">
					<option value="" selected disabled>Select Sector</option> <?php

					$sectors = $wpdb->get_results( "SELECT master_tag.id, tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE parent_id=$set_industry_id ORDER BY tag ASC" );

					foreach( $sectors as $sector ) :

						$sector_id = $sector->id;
						$sector_tag = $sector->tag;

						if( $sector_tag == $set_sector ) : $selected = 'selected'; else : $selected = ''; endif; ?>

						<option value="<?php echo $sector_id ?>" <?php echo $selected ?>><?php echo $sector_tag ?></option> <?php

					endforeach; ?>
				</select> <?php

				if( $set_sector_id == 146 ) : $sector_other_display = 'reporting-on'; endif; ?>

				<input type="text" class="form-control <?php echo $sector_other_display ?> sector-other-on mt-1" style="display: none;" name="set-reporting-sector-other" id="set-reporting-sector-other" aria-describedby="set-reporting-sector-other" value="<?php echo $set_sector_other ?>">
				<input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_sector.$set_sector_separator.$set_sector_other ?>" readonly>
			</div>
			<div class="form-group col-4">
				<label for="set-reporting-subsector">Subsector<span class="text-danger"> *</span></label>
				<select class="form-control reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-subsector" name="set-reporting-subsector">
					<option value="" selected disabled>Select Subsector</option> <?php

					$subsectors = $wpdb->get_results( "SELECT master_tag.id, tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE parent_id=$set_sector_id ORDER BY tag ASC" );

					foreach( $subsectors as $subsector ) :

						$subsector_id = $subsector->id;
						$subsector_tag = $subsector->tag;

						if( $subsector_tag == $set_subsector ) : $selected = 'selected'; else : $selected = ''; endif; ?>

						<option value="<?php echo $subsector_id ?>" <?php echo $selected ?>><?php echo $subsector_tag ?></option> <?php

					endforeach; ?>
				</select> <?php

				if( $set_subsector_id == 147 ) : $subsector_other_display = 'reporting-on'; endif; ?>

				<input type="text" class="form-control <?php echo $subsector_other_display ?> subsector-other-on mt-1" style="display: none;" name="set-reporting-subsector-other" id="set-reporting-subsector-other" aria-describedby="set-reporting-subsector-other" value="<?php echo $set_subsector_other ?>">
				<input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_subsector.$set_subsector_separator.$set_subsector_other ?>" readonly>
			</div>
		</div>

		<div class="form-row reporting-on" style="<?php echo $display_none_reporting_on ?>">
			<div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="set-reporting-update">Update</button></div>
		</div> <?php

		$update_set_reporting_date = $_POST['set-reporting-date'];
		$update_set_reporting_currency_code = $_POST['set-reporting-currency-code'];
		$update_set_reporting_calendar = $_POST['set-reporting-calendar'];
		$update_set_reporting_geo_type = $_POST['set-reporting-geo-type'];
		$update_set_reporting_distance_very_local = $_POST['set-reporting-distance-very-local'];
		$update_set_reporting_distance_local = $_POST['set-reporting-distance-local'];
		$update_set_reporting_location_very_local = $_POST['set-reporting-location-very-local'];
		$update_set_reporting_location_local = $_POST['set-reporting-location-local'];
		$update_set_reporting_industry = $_POST['set-reporting-industry'];
		$update_set_reporting_sector = $_POST['set-reporting-sector'];
		$update_set_reporting_subsector = $_POST['set-reporting-subsector'];
		$update_set_reporting_industry_other = $_POST['set-reporting-industry-other'];
		$update_set_reporting_sector_other = $_POST['set-reporting-sector-other'];
		$update_set_reporting_subsector_other = $_POST['set-reporting-subsector-other'];

		// if( $plan_id == 1 ) : $fy_month = 1; elseif( $plan_id == 4 && $is_master == 0 ) : $fy_month = NULL;  else : $fy_month = $fy_month_entry; endif;

		$update_fy_day = date( 'j', strtotime( $update_set_reporting_date ) );
		$update_fy_month = date( 'n', strtotime( $update_set_reporting_date ) );

		if( $update_set_reporting_geo_type == 143 ) : $update_very_local = $update_set_reporting_distance_very_local; $update_local = $update_set_reporting_distance_local; else : $update_very_local = NULL; $update_local = NULL; endif;

		if( empty( $update_set_reporting_industry_other ) && empty( $update_set_reporting_sector_other ) && empty( $update_set_reporting_subsector_other ) ) : $update_other = NULL; else : $update_other = $update_set_reporting_industry_other.'|'.$update_set_reporting_sector_other.'|'.$update_set_reporting_subsector_other; endif;

		if ( isset( $_POST['set-reporting-update'] ) ) :

			if( $report_active == 0 ) :

				$wpdb->update( 'profile_locationmeta',
					array(
						'entry_date' => $entry_date,
						'record_type' => 'entry_revision',
						'industry' => $update_set_reporting_industry,
						'sector' => $update_set_reporting_sector,
						'subsector' => $update_set_reporting_subsector,
						'other' => $update_other,
						'fy_day' => $update_fy_day,
						'fy_month' => $update_fy_month,
						'currency' => $update_set_reporting_currency_code,
						'geo_type' => $update_set_reporting_geo_type,
						'very_local' => $update_very_local,
						'local' => $update_local,
						'calendar' => $update_set_reporting_calendar,
						'active' => 1,
						'parent_id' => $report_initiate_id,
						'user_id' => $user_id,
						'loc_id' => $master_loc
					),
					array(
						'id' => $report_initiate_id
					)
				);

			else :

				$wpdb->insert( 'profile_locationmeta',
					array(
						'entry_date' => $entry_date,
						'record_type' => 'entry_revision',
						'industry' => $update_set_reporting_industry,
						'sector' => $update_set_reporting_sector,
						'subsector' => $update_set_reporting_subsector,
						'other' => $update_other,
						'fy_day' => $update_fy_day,
						'fy_month' => $update_fy_month,
						'currency' => $update_set_reporting_currency_code,
						'geo_type' => $update_set_reporting_geo_type,
						'very_local' => $update_very_local,
						'local' => $update_local,
						'calendar' => $update_set_reporting_calendar,
						'active' => 1,
						'parent_id' => $report_active,
						'user_id' => $user_id,
						'loc_id' => $master_loc
					)
				);

			endif;

			header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
			ob_end_flush();

		endif; ?>

	</form>

	<script>
		$.fn.datepicker.dates.en.titleFormat="MM"; /* day / month date picker */
		$(document).ready(function(){
    		var date_input=$('input[name="set_reporting_date"]');
    		date_input.datepicker({
				format: 'dd-M',
				autoclose: true,
				startView: 1,
				maxViewMode: "months",
				orientation: "bottom left",
    		})
		});
		$(document).ready(function(){
			$('#report-change-set').click(function() {
				if( $(this).is(':checked')) {
					$('.reporting-on').show();
					$('.reporting-off').hide();
				}
				else {
					$('.reporting-on').hide();
					$('.reporting-off').show();
				}
			});
			$('#set-reporting-distance').click(function() {
				if( $(this).is(':checked')) {
					$('.distance-on').show();
					$('.distance-on-required').attr('required', '');
					$('.location-on').hide();
					$('.location-on-required').removeAttr('required');
				}
			});
			$('#set-reporting-location').click(function() {
				if( $(this).is(':checked')) {
					$('.location-on').show();
					$('.location-on-required').attr('required', '');
					$('.distance-on').hide();
					$('.distance-on-required').removeAttr('required');
				}
			});
			$('#set-reporting-industry').change(function() {
				if ($(this).val() == 145) {
					$('.industry-other-on').show();
					$('.industry-other-on').attr('required', '');
				}
				else {
					$('.industry-other-on').hide();
					$('.industry-other-on').removeAttr('required');
				}
			});
			$('#set-reporting-industry').change(function(){
				var sectorPOP = $('#set-reporting-industry').val();

				$("#set-reporting-sector").empty();
				$.ajax({
					url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
					type:'GET',
					data:'action=report_settings_sector&industryID=' + sectorPOP,

					success:function(results) {
						$("#set-reporting-sector").append(results);
					}
				});
			});
			$('#set-reporting-sector').change(function() {
				if ($(this).val() == 146) {
					$('.sector-other-on').show();
					$('.sector-other-on').attr('required', '');
				}
				else {
					$('.sector-other-on').hide();
					$('.sector-other-on').removeAttr('required');
				}
			});
			$('#set-reporting-sector').change(function(){
				var subsectorPOP = $('#set-reporting-sector').val();

				$("#set-reporting-subsector").empty();
				$.ajax({
					url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
					type:'GET',
					data:'action=report_settings_subsector&sectorID=' + subsectorPOP,

					success:function(results) {
						$("#set-reporting-subsector").append(results);
					}
				});
			});
			$('#set-reporting-subsector').change(function() {
				if ($(this).val() == 147) {
					$('.subsector-other-on').show();
					$('.subsector-other-on').attr('required', '');
				}
				else {
					$('.subsector-other-on').hide();
					$('.subsector-other-on').removeAttr('required');
				}
			});
		});

	</script> <?php

}


// EDIT REPORT SETTINGS
function report_edit_setting( $title ) {

	global $wpdb;

	$master_loc = $_SESSION['master_loc']; ?>

	<section class="secondary-box p-3 mb-4 bg-white shadow-sm">

		<h2 class="h4-style">Revisions</h2> <?php

		$report_settings = $wpdb->get_results( "SELECT profile_locationmeta.id, entry_date, display_name, parent_id FROM profile_locationmeta INNER JOIN yard_users ON profile_locationmeta.user_id=yard_users.id WHERE loc_id=$master_loc AND parent_id<>0 ORDER BY entry_date DESC" );

		if( empty( $report_settings ) ) : ?>

			<p>Report settings have not been edited.</p> <?php

		else : ?>

			<div class="table-responsive-xl">
				<table id="tags" class="table table-borderless">
					<tbody> <?php

						foreach( $report_settings as $report_setting) :

							$view_id = $report_setting->id;
							$view_entry_date = date_create( $report_setting->entry_date );
							$view_display_name = $report_setting->display_name;
							$view_parent_id = $report_setting->parent_id; ?>

							<tr>
								<td>
									<button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $view_id ?>"><i class="far fa-eye"></i></button>

									<div class="modal fade text-left" id="modalRevisions-<?php echo $view_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $view_id ?>Title" aria-hidden="true">
										<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
											<div class="modal-content">

												<div class="modal-header">
													<h5 class="modal-title" id="modalRevisions-<?php echo $view_id ?>Title">Revisions</h5>
													<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
												</div>

												<div class="modal-body"> <?php

													$revision_rows = $wpdb->get_results( "SELECT profile_locationmeta.id, profile_locationmeta.entry_date, display_name, industry, master_tag.tag AS industry_tag, sector, sector.tag AS sector_tag, subsector, subsector.tag AS subsector_tag, other, fy_day, fy_month, currency, geo_type, very_local, local, county, city, calendar, profile_locationmeta.parent_id FROM profile_locationmeta INNER JOIN yard_users ON profile_locationmeta.user_id=yard_users.id INNER JOIN profile_location ON profile_locationmeta.loc_id=profile_location.parent_id LEFT JOIN master_tag ON profile_locationmeta.industry=master_tag.id LEFT JOIN master_tag sector ON profile_locationmeta.sector=sector.id LEFT JOIN master_tag subsector ON profile_locationmeta.subsector=subsector.id WHERE profile_locationmeta.id=$view_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)" );

													foreach( $revision_rows as $revision_row ) :

														$revision_id = $revision_row->id;
														$revision_entry_date = date_create( $revision_row->entry_date );
														$revision_display_name = $revision_row->display_name;
														$revision_industy_tag = $revision_row->industry_tag;
														$revision_sector_tag = $revision_row->sector_tag;
														$revision_subsector_tag = $revision_row->subsector_tag;
														$revision_other = $revision_row->other;
														$revision_fy_day = $revision_row->fy_day;
														$revision_fy_month = $revision_row->fy_month;
														$revision_currency = $revision_row->currency;
														$revision_geo_type = $revision_row->geo_type;
														$revision_parent_id = $revision_row->parent_id;

														$revision_fy_date = $revision_fy_day.'-'.$revision_fy_month.'-2000';

														if( $revision_geo_type == 143 ) : $revision_very_local = $revision_row->very_local.' km'; $revision_local = $revision_row->local.' km'; else : $revision_very_local = $revision_row->city; $revision_local = $revision_row->county; endif;

														$revision_split = explode( "|", $revision_other );
														if( $revision_industy_tag == 'Other' ) : $revision_other_industry = ' - '.$revision_split[0]; else : $revision_other_industry = ''; endif;
														if( $revision_sector_tag == 'Other' ) : $revision_other_sector = ' - '.$revision_split[1]; else : $revision_other_sector = ''; endif;
														if( $revision_subsector_tag == 'Other' ) : $revision_other_subsector = ' - '.$revision_split[2]; else : $revision_other_subsector = ''; endif;

														echo '<p><b>Reporting Year Start Date: </b>'.date_format( date_create( $revision_fy_date ),'d F' ).'<br />';
														echo '<b>Currency: </b>'.$revision_currency.'<br />';
														echo '<b>Geographical Boundaries </b><br /><span class="px-3">Local: '.$revision_local.'</span><br /><span class="px-3">Very Local: '.$revision_very_local.'</span><br />';
														echo '<b>Benchmarking </b><br /><span class="px-3">Industry: '.$revision_industy_tag.$revision_other_industry.'</span><br /><span class="px-3">Sector: '.$revision_sector_tag.$revision_other_sector.'</span><br /><span class="px-3">Subsector: '.$revision_subsector_tag.$revision_other_subsector.'</span></p>';

														if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; else : $active_action = 'Edited'; endif;

														echo '<p><b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_display_name.'</p>';

													endforeach; ?>

												</div>

											</div>
										</div>
									</div>

								</td>
								<td> <?php

									if( $view_id == $view_parent_id ) : $view_active_action = 'Added'; else : $view_active_action = 'Edited'; endif;

									echo $view_active_action.' on '.date_format( $view_entry_date, "d-M-Y" ).' by '.$view_display_name ?>

								</td>
							</tr> <?php

						endforeach; ?>

					</tbody>
				</table>
			</div> <?php

		endif; ?>

	</section> <?php

}


// ADD CATEGORY SETTING
function category_add_setting() {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$entry_date = date( 'Y-m-d H:i:s' ); ?>

	<form method="post" name="setting-categories-tags" id="setting-categories-tags">

		<div class="form-row">
			<div class="col-md-12 mb-3">
				<p>Tags are keywords that are attached to entries to give additional information. Categories lets you to label your groups of tags.</p>
				<label class="font-weight-normal align-top pt-1 pr-4">Change Categories and Tags:<sup class="text-danger">*</sup></label> <?php

			  	$categories_tags_selected = $wpdb->get_row( "SELECT parent_id, active FROM custom_tag WHERE loc_id=$master_loc AND tag IS NULL AND id IN (SELECT MAX(id) FROM custom_tag WHERE cat_id=22 GROUP BY parent_id)" );
			  	$categories_tags_selected_active = $categories_tags_selected->active;
			  	$categories_tags_selected_parent_id = $categories_tags_selected->parent_id;

				if( $categories_tags_selected_active == 1 ) : $checked_enabled = 'checked'; else : $checked_disabled = 'checked'; endif; ?>

				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="edit-categories-tags" id="edit-categories-tags-enable" value="1" <?php echo $checked_enabled ?>>
					<label class="form-check-label" for="edit-categories-tags-enable">Enable</label>
				</div>

				<div class="form-check form-check-inline">
					<input class="form-check-input" type="radio" name="edit-categories-tags" id="edit-categories-tags-disable" value="0" <?php echo $checked_disabled ?>>
					<label class="form-check-label" for="edit-categories-tags-disable">Disable</label>
				</div>

			</div>
		</div>

		<div class="form-row">
			<div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="setting-categories-tags">Update</button></div>
		</div>

	</form><?php

	$update_categories_tags = $_POST['edit-categories-tags'];

	if ( isset( $_POST['setting-categories-tags'] ) ) :

		$wpdb->insert( 'custom_tag',
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry_revision',
				'tag' => NULL,
				'tag_id' => NULL,
				'size' => NULL,
				'unit_id' => NULL,
				'cat_id' => 22,
				'active' => $update_categories_tags,
				'parent_id' => $categories_tags_selected_parent_id,
				'user_id' => $user_id,
				'loc_id' => $master_loc
			)
		);

		header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
		ob_end_flush();

	endif;

	if( $categories_tags_selected_active == 1 ) : ?>

		<h4 class="border-top pt-3 mt-4">Add Categories</h5>
		<form method="post" name="add-category-settings" id="add-category-settings" class="needs-validation" novalidate>

			<div id="repeater-field">
				<div class="entry form-row mb-1">
					<div class="col-10">
						<input type="text" class="form-control" id="set-category-name" name="set-category-name[]" placeholder="Category Name *" required>
						<div class="invalid-feedback">Enter a category name</div>
					</div>

					<div class="col-2">
						<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
					</div>
				</div>
			</div>

			<div class="form-row">
				<div class="col-2 offset-10 mb-3"><button class="btn btn-primary float-none" type="submit" name="add-category-settings">Add</button></div>
			</div>
		</form> <?php

		$set_category_name_array = $_POST['set-category-name'];

		if ( isset( $_POST['add-category-settings'] ) ) :

			foreach( $set_category_name_array as $index => $set_category_name_array ) :

				$category = $set_category_name_array;
				$category_check = $wpdb->get_row( "SELECT category FROM custom_category WHERE category='$category' AND loc_id=$master_loc" );

				if( empty( $category_check ) ) :

					$wpdb->insert( 'custom_category',
						array(
							'entry_date' => $entry_date,
							'record_type' => 'entry',
							'category' => $category,
							'active' => 1,
							'parent_id' => 0,
							'user_id' => $user_id,
							'loc_id' => $master_loc
						)
					);

					$parent_id = $wpdb->insert_id;

					$wpdb->update( 'custom_category',
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


// EDIT CATEGORIES SETTINGS
function category_edit_setting( $title, $title_singular ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$entry_date = date( 'Y-m-d H:i:s' );

	$edit_rows  = $wpdb->get_results( "SELECT id, category, parent_id, active FROM custom_category WHERE loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_category GROUP BY parent_id) ORDER BY active DESC, category ASC" );

	if( empty( $edit_rows ) ) :

		echo 'Please add the '.strtolower( $title ).' used by your business.';

	else : ?>

		<div class="table-responsive-xl">
			<table id="tags" class="table table-borderless">
				<thead>
					<tr>
						<th scope="col" class="no-sort">View | Delete | Edit </th>
						<th scope="col">Sort <?php echo $title ?></th>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $edit_rows as $edit_row ) :

						$edit_id = $edit_row->id;
						$edit_category = $edit_row->category;
						$edit_parent_id = $edit_row->parent_id;
						$edit_active = $edit_row->active;
						$edit_update = 'update-'.$edit_id;
						$edit_archive = 'archive-'.$edit_id; ?>

						<tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
							<td class="align-top strikeout-buttons">

								<button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $edit_id ?>"><i class="far fa-eye"></i></button>

								<div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
									<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
										<div class="modal-content">

											<div class="modal-header">
												<h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $edit_category ?></h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
											</div>

											<div class="modal-body"> <?php

												$revision_rows = $wpdb->get_results( "SELECT custom_category.id, entry_date, category, parent_id, display_name, active FROM custom_category INNER JOIN yard_users ON custom_category.user_id=yard_users.id WHERE parent_id=$edit_parent_id ORDER BY custom_category.id DESC" );

												foreach( $revision_rows as $revision_row ) :

													$revision_id = $revision_row->id;
													$revision_entry_date = date_create( $revision_row->entry_date );
													$revision_catergory = $revision_row->category;
													$revision_parent_id = $revision_row->parent_id;
													$revision_active = $revision_row->active;
													$revision_username = $revision_row->display_name;

													if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

													echo '<b>'.$title_singular.':</b> '.$revision_catergory.'<br />';
													echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';
													echo '<b>Entry ID:</b> '.$revision_id.'<br />';

													if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

												endforeach; ?>

											</div>

										</div>
									</div>
								</div> <?php

								if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="fas fa-trash-alt"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1; $btn_style = 'btn-success'; $edit_value = '<i class="fas fa-trash-restore-alt"></i>'; endif; ?>

								<form method="post" name="archive" id="<?php echo $edit_archive ?>" class="d-inline-block">
									<button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $edit_archive ?>"><?php echo $edit_value ?></button>
								</form> <?php

								if ( isset( $_POST[$edit_archive] ) ) :

									$wpdb->insert( 'custom_category',
										array(
											'entry_date' => $entry_date,
											'record_type' => 'entry_revision',
											'category' => $edit_category,
											'active' => $edit_active_update,
											'parent_id' => $edit_parent_id,
											'user_id' => $user_id,
											'loc_id' => $master_loc
										)
									);

									header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
									ob_end_flush();

								endif;

								if( $edit_active == 1 ) : ?>

									<button type="button" class="btn btn-light d-inline-block" data-toggle="modal" data-target="#modal-<?php echo $edit_id ?>"><i class="fas fa-pencil"></i></button>

									<div class="modal fade" id="modal-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-<?php echo $edit_id ?>Title" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered" role="document">
											<div class="modal-content">

												<div class="modal-header">
													<h5 class="modal-title" id="modal-<?php echo $edit_id ?>Title"><?php echo $edit_category ?></h5>
													<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
												</div>

												<div class="modal-body">
													<form method="post" name="update" id="<?php echo $edit_update ?>">

														<div class="input-group mb-3">
															<input type="text" class="form-control" value="<?php echo $edit_category ?>" aria-label="Category Update" aria-describedby="categoryUpdate" name="update_category">

															<div class="input-group-append"><input type="submit" class="btn btn-primary d-inline-block" aria-describedby="categoryUpdate" name="<?php echo $edit_update ?>" value="Update" /></div>
														</div>

													</form>

												</div>

											</div>
										</div>
									</div> <?php

								endif;

								$update_category = $_POST['update_category'];

								if ( isset( $_POST[$edit_update] ) ) :

									$wpdb->insert( 'custom_category',
										array(
											'entry_date' => $entry_date,
											'record_type' => 'entry_revision',
											'category' => $update_category,
											'active' => 1,
											'parent_id' => $edit_parent_id,
											'user_id' => $user_id,
											'loc_id' => $master_loc
										)
									);

									header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
									ob_end_flush();

								endif; ?>

							</td>
							<td><?php echo $edit_category; ?></td>
						</tr> <?php

					endforeach; ?>

				</tbody>
			</table>
		</div> <?php

	endif;
}


//ADD TAG SETTING
function tag_add_setting( $cat_id ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$tag_toggle = $_SESSION['tag_toggle'];

	$entry_date = date( 'Y-m-d H:i:s' );

	if( $tag_toggle == 0 ) : ?>

		<p>Please enable and add at least one category before adding tags.</p> <?php

	else : ?>

		<form method="post" name="add-tag-settings" id="add-tag-settings" class="needs-validation" novalidate>

			<div id="repeater-field">
				<div class="entry form-row mb-1">
					<div class="col-5">
						<select id="set-category" name="set-category[]" class="form-control" required>
							<option value="" selected disabled>Select Category *</option> <?php

							$category_dropdowns = $wpdb->get_results( "SELECT parent_id, category FROM custom_category WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_category GROUP BY parent_id) ORDER BY category ASC" );

							foreach( $category_dropdowns as $category_dropdown ) : ?>
								<option value="<?php echo $category_dropdown->parent_id ?>"><?php echo $category_dropdown->category ?></option> <?php
							endforeach; ?>
						</select>
						<div class="invalid-feedback">Select a category</div>
					</div>

					<div class="col-5">
						<input type="text" class="form-control" id="set-tag-name" name="set-tag-name[]" placeholder="Tag Name *" required>
						<div class="invalid-feedback">Enter a tag</div>
					</div>

					<div class="col-2">
						<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
					</div>
				</div>
			</div>

			<div class="form-row">
				<div class="col-2 offset-10 mb-3"><button class="btn btn-primary float-none" type="submit" name="add-tag-settings">Add</button></div>
			</div>
		</form> <?php

		$set_category_array = $_POST['set-category'];
		$set_tag_name_array = $_POST['set-tag-name'];

		if ( isset( $_POST['add-tag-settings'] ) ) :

			foreach( $set_category_array as $index => $set_category_array ) :

				$custom_cat = $set_category_array;
				$tag = $set_tag_name_array[$index];
				$tag_check = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag='$tag' AND loc_id=$master_loc" );

				if( empty( $tag_check ) ) :

					$wpdb->insert( 'custom_tag',
						array(
							'entry_date' => $entry_date,
							'record_type' => 'entry',
							'tag' => $tag,
							'tag_id' => NULL,
							'size' => NULL,
							'unit_id' => NULL,
							'custom_cat' => $custom_cat,
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


// ADD LOCATION SETTINGS
add_action( 'gform_after_submission_83', 'submit_settings_location', 10, 2 );
function submit_settings_location( $entry, $form ) {

  	global $wpdb;
	$master_loc = $_SESSION['master_loc'];
	$user_id = get_current_user_id();

	$entry_date = date( 'Y-m-d H:i:s' );

	$location = $entry[3];
	$street_entry = $entry[20];
	$city = $entry[21];
	$county = $entry[22];
	$country = $entry[18];
	$coords = maybe_unserialize( $entry[6] );
	$latitude  = $coords['latitude'];
    $longitude = $coords['longitude'];

	if( empty( $street_entry) ) : $street = NULL; else : $street = $street_entry; endif;


	$location_cleanse = str_replace( "'", "\'", $location );
	$loc_check = $wpdb->get_row( "SELECT location FROM custom_location WHERE location='$location_cleanse' AND loc_id=$master_loc" );

	if( empty( $loc_check ) ) :

		$wpdb->insert( 'custom_location',
			array(
				'entry_date' => $entry_date,
				'record_type' => 'entry',
				'location' => $location,
				'street' => $street,
				'city' => $city,
				'county' => $county,
				'country' => $country,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'parent_id' => 0,
				'user_id' => $user_id,
				'active' => 1,
				'loc_id' => $master_loc
			)
		);

		$parent_id = $wpdb->insert_id;

		$wpdb->update( 'custom_location',
			array(
				'parent_id' => $parent_id,
			),
			array(
				'id' => $parent_id
			)
		);

	endif;
}


// EDIT LOCATION SETTINGS
function location_edit_setting( $title ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$entry_date = date( 'Y-m-d H:i:s' );

	$tags  = $wpdb->get_results( "SELECT custom_location.id, location, street, city, county, country, latitude, longitude, parent_id, active FROM custom_location WHERE loc_id=$master_loc AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY active DESC, custom_location.location ASC" );

	if( empty( $tags ) ) :

		echo 'Please add the '.strtolower( $title ).' used by your business.';

	else : ?>

		<div class="table-responsive-xl">
			<table id="tags" class="table table-borderless">
				<thead>
					<tr>
						<th scope="col" class="no-sort">View | Delete | Edit</th>
						<th scope="col">Sort <?php echo $title ?></th>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $tags as $tag ) :

						$edit_id = $tag->id;
						$edit_location = $tag->location;
						$edit_street = $tag->street;
						$edit_city = $tag->city;
						$edit_county = $tag->county;
						$edit_country = $tag->country;
						$edit_latitude = $tag->latitude;
						$edit_longitude = $tag->longitude;
						$edit_parent_id = $tag->parent_id;
						$edit_active = $tag->active;
						$edit_update = 'update-'.$edit_id;
						$edit_archive = 'archive-'.$edit_id;

						if( !empty( $edit_street ) ) : $edit_street_row = $edit_street.', '; else:  $edit_street_row = ''; endif;

						$row_item = $edit_location.'<br />'.$edit_street_row.$edit_city.', '.$edit_county.', '.$edit_country; ?>

						<tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
							<td class="align-top strikeout-buttons">

								<button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $edit_id ?>"><i class="far fa-eye"></i></button>

								<div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
									<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
										<div class="modal-content">

											<div class="modal-header">
												<h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $edit_location ?></h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
											</div>

											<div class="modal-body"> <?php

												$revision_rows = $wpdb->get_results( "SELECT custom_location.id, entry_date, location, street, city, county, country, parent_id, display_name, active FROM custom_location INNER JOIN yard_users ON custom_location.user_id=yard_users.id WHERE parent_id=$edit_parent_id ORDER BY custom_location.id DESC" );

												foreach( $revision_rows as $revision_row ) :

													$revision_id = $revision_row->id;
													$revision_entry_date = date_create( $revision_row->entry_date );
													$revision_location = $revision_row->location;
													$revision_street = $revision_row->street;
													$revision_city = $revision_row->city;
													$revision_county = $revision_row->county;
													$revision_country = $revision_row->country;
													$revision_parent_id = $revision_row->parent_id;
													$revision_username = $revision_row->display_name;
													$revision_active = $revision_row->active;

													if( !empty( $revision_street ) ) : $revision_street_row = $revision_street.', '; endif;

													echo $revision_location.'<br />'.$revision_street_row.$revision_city.', '.$revision_county.', '.$revision_country.'<br />';

													if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

													echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';

													if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

												endforeach; ?>

											</div>

										</div>
									</div>
								</div> <?php

								if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="fas fa-trash-alt"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1; $btn_style = 'btn-success'; $edit_value = '<i class="fas fa-trash-restore-alt"></i>'; endif; ?>

								<form method="post" name="archive" id="<?php echo $edit_archive ?>" class="d-inline-block">
									<button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $edit_archive ?>"><?php echo $edit_value ?></button>
								</form> <?php

								if ( isset( $_POST[$edit_archive] ) ) :

									$wpdb->insert( 'custom_location',
										array(
											'entry_date' => $entry_date,
											'record_type' => 'entry_revision',
											'location' => $edit_location,
											'street' => $edit_street,
											'city' => $edit_city,
											'county' => $edit_county,
											'country' => $edit_country,
											'latitude' => $edit_latitude,
											'longitude' => $edit_longitude,
											'parent_id' => $edit_parent_id,
											'user_id' => $user_id,
											'active' => $edit_active_update,
											'loc_id' => $master_loc
										)
									);

									header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
									ob_end_flush();

								endif;

								if( $edit_active == 1 ) : ?>

									<button type="button" class="btn btn-light d-inline-block" data-toggle="modal" data-target="#modal-<?php echo $edit_id ?>"><i class="fas fa-pencil"></i></button>

									<div class="modal fade" id="modal-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-<?php echo $tag_id ?>Title" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
											<div class="modal-content">

												<div class="modal-header">
													<h5 class="modal-title" id="modal-<?php echo $edit_id ?>Title"><?php echo $revision_location ?></h5>
													<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
												</div>

												<div class="modal-body"> <?php echo do_shortcode( '[gravityform id=131 title="false" description=false field_values="tag_parent_id='.$edit_parent_id.'&tag_location='.$edit_location.'&tag_street='.$edit_street.'&tag_city='.$edit_city.'&tag_county='.$edit_county.'&tag_country='.$edit_country.'&tag_coordinates='.$edit_latitude.'|'.$edit_longitude.'"]' ); ?> </div>

											</div>
										</div>
									</div> <?php

								endif; ?>

							</td>
							<td><?php echo $row_item; ?></td>
						</tr> <?php

					endforeach; ?>

				</tbody>
			</table>
		</div> <?php

	endif;

} ?>
