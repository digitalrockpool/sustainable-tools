<?php ob_start();

/* Includes: OPERATIONS SNIPPETS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2021, Digital Rockpool LTD
@license	GPL-2.0+ */


// ADD SETTINGS
function operations_add_setting( $set_id, $cat_id, $title, $title_singular ) {

	global $wpdb;
	global $post;

	$site_url = get_site_url();
	$slug = $post->post_name;
	$setting_query = $_GET['setting'];

	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$entry_date = date( 'Y-m-d H:i:s' );

	if( $set_id == 7 ) : // plastics

		$utility_dropdowns = $wpdb->get_results( "SELECT master_tag.id, master_tag.tag FROM master_tag WHERE cat_id=$cat_id ORDER BY master_tag.tag ASC" );

	else :

		$utility_dropdowns = $wpdb->get_results( "SELECT master_tag.id AS id, master_tag.tag AS tag FROM master_tag WHERE cat_id=$cat_id AND NOT EXISTS (SELECT custom_tag.tag_id FROM custom_tag WHERE master_tag.id=custom_tag.tag_id AND loc_id=$master_loc) GROUP BY tag ORDER BY tag ASC" );

	endif;

	if( empty( $utility_dropdowns ) ) : ?>

		<p>All <?php echo strtolower( $title ); ?> have been added. If you require a new <?php echo strtolower( $title_singular ); ?> please email <a href="mailto:support@yardstick.co.uk" title="support@yardstick.co.uk">support@yardstick.co.uk</a></p> <?php

	else : ?>

		<form method="post" name="add-operation-settings" id="add-operation-settings" class="needs-validation" novalidate> <?php

			if( $set_id == 14 ) : // operation setting ?>

				<div class="form-row">

					<div class="col-md-4 mb-3">
						<label for="set-capacity">Capacity<sup class="text-danger">*</sup></label>
						<input type="number" class="form-control" id="set-capacity" name="set-operation-tag[]" min="1" step="1" required>
						<input type="hidden" name="set-operation-type[]" value="289"> <!-- value = master_tag.id -->
						<div class="invalid-feedback">Please enter a whole number greater than or equal to 1</div>

					</div>

					<div class="col-md-4 mb-3">
						<label for="set-days-open">Days open<sup class="text-danger">*</sup></label>
						<input type="number" class="form-control" id="set-days-open" name="set-operation-tag[]" min="1" max="365" step="1" required>
						<input type="hidden" name="set-operation-type[]" value="280"> <!-- value = master_tag.id -->
						<div class="invalid-feedback">Please enter a whole number between 1 and 365</div>
					</div>

					<div class="col-md-4 mb-3">
						<label for="set-total-area">Total area in m2<sup class="text-danger">*</sup></label>
						<input type="number" class="form-control" id="set-total-area" name="set-operation-tag[]" min="1" step="0.01" required>
						<input type="hidden" name="set-operation-type[]" value="288"> <!-- value = master_tag.id -->
						<div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
					</div>

				</div> <?php

			else : ?>
				<div id="repeater-field">
					<div class="entry form-row mb-1">
						<div class="<?php if( $set_id == 6 ) : echo 'col-10';  elseif( $set_id == 7 ) : echo 'col-3';  else : echo 'col-5'; endif; ?>">
							<select id="set-operation-type" name="set-operation-type[]" class="form-control set-utility-type" required>
								<option value="" selected disabled>Select <?php echo $title_singular ?> *</option> <?php

								foreach( $utility_dropdowns as $utility_dropdown ) : ?>
									<option value="<?php echo $utility_dropdown->id ?>"><?php echo $utility_dropdown->tag ?></option> <?php
								endforeach; ?>

							</select>
							<div class="invalid-feedback">Please select <?php echo strtolower( $title_singular ); ?> </div>
						</div> <?php

						if( $set_id == 7 ) : // plastics ?>

							<div class="col-3">
								<input type="text" class="form-control" id="set-operation-tag" name="set-operation-tag[]" placeholder="Description">
							</div> <?php

						endif;

						if( $set_id != 6 ) : // disposal methods ?>

							<div class="<?php if( $set_id == 7 ) : echo 'col-2';  else : echo 'col-5'; endif; ?>">
								<select id="set-operation-unit" name="set-operation-unit[]" class="form-control set-utility-unit" required>
									<option value="" selected disabled>Select Units *</option> <?php

									$unit_dropdowns = $wpdb->get_results( "SELECT master_tag.id, master_tag.tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE relation='$title_singular-unit' GROUP BY master_tag.tag" );

									foreach( $unit_dropdowns as $unit_dropdown ) : ?>
										<option value="<?php echo $unit_dropdown->id ?>"><?php echo $unit_dropdown->tag ?></option> <?php
									endforeach; ?>

								</select>
								<div class="invalid-feedback">Please select units</div>
							</div> <?php

						endif;

						if( $set_id == 7 ) : // plastics ?>

							<div class="col-2">
								<input type="number" class="form-control" id="set-operation-size" name="set-operation-size[]" min="0" step="0.01" placeholder="Quantity *" required>
								<div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
							</div> <?php

						endif; ?>

						<div class="col-2">
							<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
						</div>
					</div>
				</div> <?php

			endif; ?>

			<div class="form-row">
				<div class="col-2 offset-10 mb-3"><button class="btn btn-primary <?php if( $set_id !=14 ) : echo 'float-none'; endif; ?>" type="submit" name="add-operation-settings">Add</button></div>
			</div>
		</form> <?php

		$set_operation_type_array = $_POST['set-operation-type'];
		$set_operation_tag_array = $_POST['set-operation-tag'];
		$set_operation_unit_array = $_POST['set-operation-unit'];
		$set_operation_size_array = $_POST['set-operation-size'];

		if ( isset( $_POST['add-operation-settings'] ) ) :

			foreach( $set_operation_type_array as $index => $set_operation_type_array ) :

				$tag_id = $set_operation_type_array;
				if( empty( $set_operation_tag_array ) ) : $tag = NULL; else : $tag = $set_operation_tag_array[$index]; endif;
				if( empty( $set_operation_unit_array ) ) : $unit_id = NULL; else : $unit_id = $set_operation_unit_array[$index]; endif;
				if( empty( $set_operation_size_array ) ) : $size = NULL; else : $size = $set_operation_size_array[$index]; endif;

				if( $set_id == 7 ) : // plastic

					$tag_check = $wpdb->get_row( "SELECT tag_id FROM custom_tag WHERE tag='$tag' AND size=$size AND unit_id=$unit_id AND loc_id=$master_loc" );

				else :

					$tag_check = $wpdb->get_row( "SELECT tag_id FROM custom_tag WHERE tag_id=$tag_id AND loc_id=$master_loc" );

				endif;

				if( empty( $tag_check ) ) :

					$wpdb->insert( 'custom_tag',
						array(
							'entry_date' => $entry_date,
							'record_type' => 'entry',
							'tag' => $tag,
							'tag_id' => $tag_id,
							'size' => $size,
							'unit_id' => $unit_id,
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
	endif; ?>

	<script>
		$(document).on('change', '.set-utility-type', function(){
			var unitPOP = $(this).val();
			var dropDown = $(this).parent().parent().find(".set-utility-unit");
			dropDown.empty();
			$.ajax({
				url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
				type:'GET',
				data:'action=operations_unit_settings&utilityID=' + unitPOP,
				success:function(results) {
					dropDown.append(results);
				}
			});
		});
	</script> <?php
}


// LATEST ENTRIES
function operations_latest_entries( $add, $title, $extra_value ) {

	global $wpdb;

	$cat_id = $extra_value;
	$user_id = get_current_user_id();

	$add_rows = $wpdb->get_results( "SELECT measure_date, measure_start, master_tag.tag AS master_tag, custom_tag.tag AS custom_tag, amount FROM data_operations LEFT JOIN data_measure ON (data_operations.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) INNER JOIN custom_tag ON (data_operations.utility_id=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag ON master_tag.id=custom_tag.tag_id INNER JOIN relation_user ON data_operations.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND master_tag.cat_id=$cat_id AND data_operations.id IN (SELECT MAX(id) FROM data_operations GROUP BY parent_id) GROUP BY data_operations.id ORDER BY data_operations.id DESC LIMIT 5" );

	if( empty( $add_rows) ) :

		echo '<p>No '.strtolower( $title ).' data has been added.</p>';

	else : ?>

		<div class="table-responsive-xl mb-4">
			<table id="latest" class="table table-borderless">
				<thead>
					<tr>
						<th scope="col">Date</th>
						<th scope="col"><?php echo $title ?></th>
						<th scope="col">Amount</th>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $add_rows as $add_row ) :

						$latest_date = $add_row->measure_date;
						$latest_date_formatted = date_format( date_create( $latest_date ), 'd-M-Y' );
						$latest_measure_start = $add_row->measure_start;
						$latest_measure_start_formatted = date_format( date_create( $latest_measure_start ), 'd-M-Y' );
						$latest_master_tag = $add_row->master_tag;
						$latest_custom_tag_entry = $add_row->custom_tag;
						$latest_amount = $add_row->amount;

						if( !empty( $latest_custom_tag_entry ) ) : $latest_custom_tag = ' - '.$latest_custom_tag_entry; endif; ?>

						<tr>
						<td nowrap><?php if( empty( $latest_date ) ) : echo $latest_measure_start_formatted; else : echo $latest_date_formatted; endif; ?></td> <?php
						if( $loc_number > 1 ) : ?> <td scope="col"><?php echo substr( $latest_loc_name, 10 ) ?></td> <?php endif; ?>
						<td><?php echo $latest_master_tag.$latest_custom_tag ?></td>
						<td class="text-right" nowrap><?php echo number_format($latest_amount, 2) ?></td>
						</tr> <?php

					endforeach; ?>

				</tbody>
			</table>
		</div> <?php

	endif;

}


// OPERATIONS FORM



// OPERATIONS EDIT



// CHART FILTER
function chart_dropdown_master_tag() {

	global $wpdb;

	$master_loc = $_SESSION['master_loc'];
	$chart = str_replace( '-', ' ', $_GET['chart'] );
	$filter = str_replace( '   ', ' - ', str_replace( '-', ' ', $_GET['filter'] ) );

	$cat_id_lookup = $wpdb->get_row( "SELECT id FROM master_category WHERE category='$chart'");
	$cat_id = $cat_id_lookup->id;

	$dropdowns = $wpdb->get_results( "SELECT master_tag.tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id WHERE custom_tag.cat_id=$cat_id AND loc_id=$master_loc AND active=1 GROUP BY parent_id ORDER BY master_tag.tag ASC" );

	foreach( $dropdowns as $dropdown ) :
		$value_tag = $dropdown->tag;
		if( $filter == strtolower( $value_tag ) ) : $selected = 'selected'; else : $selected = ''; endif; ?>
		<option value="<?php echo $value_tag ?>" <?php echo $selected ?>><?php echo $value_tag ?></option> <?php
	endforeach;

}

function chart_dropdown_plastic_tag( ) {

	global $wpdb;

	$master_loc = $_SESSION['master_loc'];
	$chart = str_replace( '-', ' ', $_GET['chart'] );

	$cat_id_lookup = $wpdb->get_row( "SELECT id FROM master_category WHERE category='$chart'");
	$cat_id = $cat_id_lookup->id;

	$dropdowns = $wpdb->get_results( "SELECT master_tag.tag AS system_tag, custom_tag.tag AS custom_tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=$cat_id AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) GROUP BY custom_tag.parent_id ORDER BY master_tag.tag ASC" );

	foreach( $dropdowns as $dropdown ) : ?>
		<option value="<?php echo $dropdown->system_tag.'|'.$dropdown->custom_tag; ?>"><?php echo $dropdown->system_tag.' - '.$dropdown->custom_tag ?></option> <?php
	endforeach;

}





// CHART WATER MENU ITEM
function shortcode_chart_water_menu() {

	global $wpdb;

	$site_url = get_site_url();
	$master_loc = $_SESSION['master_loc'];

	$filter_row = $wpdb->get_row( "SELECT master_tag.tag as tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=18 AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 ORDER BY master_tag.tag ASC" );
	$filter = $filter = str_replace( ' ', '-', strtolower( $filter_row->tag ) );

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

	$string = '<a class="mega-menu-link" href="'.$site_url.'/charts/?chart=water&series=usage&filter='.$filter.'&frequency='.$measure.'&start='.$start.'&end='.$end.'">Water</a>';

	return $string;
}
add_shortcode('shortcode-chart-water-menu', 'shortcode_chart_water_menu');


// CHART WASTE MENU ITEM
function shortcode_chart_waste_menu() {

	global $wpdb;

	$site_url = get_site_url();
	$master_loc = $_SESSION['master_loc'];

	$filter_row = $wpdb->get_row( "SELECT master_tag.tag as tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=19 AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 ORDER BY master_tag.tag ASC" );
	$filter = $filter = str_replace( ' ', '-', strtolower( $filter_row->tag ) );

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

	$string = '<a class="mega-menu-link" href="'.$site_url.'/charts/?chart=water&series=usage&filter='.$filter.'&frequency='.$measure.'&start='.$start.'&end='.$end.'">Waste</a>';

	return $string;
}
add_shortcode('shortcode-chart-waste-menu', 'shortcode_chart_waste_menu');


// CHART PLASTIC MENU ITEM
function shortcode_chart_plastic_menu() {

	global $wpdb;

	$site_url = get_site_url();
	$master_loc = $_SESSION['master_loc'];

	$filter_row = $wpdb->get_row( "SELECT master_tag.tag AS system_tag, custom_tag.tag AS custom_tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=40 AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY master_tag.tag ASC" );
	$filter = str_replace( ' ', '-', strtolower( $filter_row->system_tag ) );
	$filter2 = str_replace( ' ', '-', strtolower( $filter_row->custom_tag ) );

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

	$string = '<a class="mega-menu-link" href="'.$site_url.'/charts/?chart=plastic&series=usage&filter='.$filter.'|'.$filter2.'&frequency='.$measure.'&start='.$start.'&end='.$end.'">Plastic</a>';

	return $string;
}
add_shortcode('shortcode-chart-plastic-menu', 'shortcode_chart_plastic_menu');?>
