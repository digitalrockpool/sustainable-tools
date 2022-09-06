<?php ob_start();

/* Template Name: Data

Template Post Type: Page

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/yardstick
@copyright	Copyright (c) 2022, Digital Rockpool LTD
@license	GPL-2.0+ */

get_header();

global $wpdb;
global $post;

$site_url = get_site_url().'/yardstick';
$slug = $post->post_name;

$master_loc = $_SESSION['master_loc'];

$user_id = get_current_user_id();
$user_role = $_SESSION['user_role'];
$user_role_tag = $_SESSION['user_role_tag'];
$wp_user_role = $_SESSION['wp_user_role'];

$entry_date = date( 'Y-m-d H:i:s' );

$plan_id = $_SESSION['plan_id'];
$measure_toggle = $_SESSION['measure_toggle'];
$calendar = $_SESSION['calendar'];
$tag_toggle = $_SESSION['tag_toggle'];

$add_url = $_GET['add'];
$add = str_replace( '-', ' ', $add_url );
$edit_url = $_GET['edit'];
$edit = str_replace( '-', ' ', $edit_url );

$data_setup = $wpdb->get_row( "SELECT master_data.id, mod_id, module, module_db, title, cat_id, tag_id, category, help_id, add_id FROM master_data INNER JOIN master_module ON master_data.mod_id=master_module.id INNER JOIN master_category ON master_data.cat_id=master_category.id WHERE title='$add' OR title='$edit'" );
$data_id = $data_setup->id;
$mod_id = $data_setup->mod_id;
$module = $data_setup->module;
$module_strip = str_replace( ' ', '_', strtolower($module) );
$module_db = $data_setup->module_db;
$title = $data_setup->title;
$cat_id = $data_setup->cat_id;
$tag_id = $data_setup->tag_id;
$category = $data_setup->category;
$help_id = $data_setup->help_id;
$form_add_id = $data_setup->add_id;

if( $mod_id == 1 ) : // measures

	$latest_measure_date = $wpdb->get_row( "SELECT measure_start AS measure_date FROM data_measure INNER JOIN relation_user ON data_measure.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id) ORDER BY measure_start DESC" );

elseif( $mod_id == 2 ) : // operations

	$latest_measure_date = $wpdb->get_row( "SELECT measure_date FROM data_operations INNER JOIN relation_user ON data_operations.loc_id=relation_user.loc_id INNER JOIN custom_tag ON data_operations.utility_id=custom_tag.id WHERE relation_user.user_id=$user_id AND cat_id=$cat_id AND data_operations.id IN (SELECT MAX(id) FROM data_operations GROUP BY parent_id) ORDER BY measure_date DESC" );

elseif( $mod_id == 3 ) : // labour

	$latest_measure_date = $wpdb->get_row( "SELECT measure_date FROM data_labour INNER JOIN relation_user ON data_labour.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND employee_type=$tag_id AND data_labour.id IN (SELECT MAX(id) FROM data_labour GROUP BY parent_id) ORDER BY measure_date DESC" );

elseif( $mod_id == 4 ) : // charity

	$latest_measure_date = $wpdb->get_row( "SELECT measure_date FROM data_charity INNER JOIN relation_user ON data_charity.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND donation_type=$tag_id AND data_charity.id IN (SELECT MAX(id) FROM data_charity GROUP BY parent_id) ORDER BY measure_date DESC" );

elseif( $mod_id == 5 ) : // supply chain

	$latest_measure_date = $wpdb->get_row( "SELECT measure_date FROM data_supply INNER JOIN relation_user ON data_supply.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND data_supply.id IN (SELECT MAX(id) FROM data_supply GROUP BY parent_id) ORDER BY measure_date DESC" );

endif;

$custom_date_range = $wpdb->get_row( "SELECT custom_tag.tag AS customtag, master_tag.tag AS mastertag FROM custom_tag INNER JOIN master_tag ON custom_tag.tag_id=master_tag.id WHERE loc_id=$master_loc and master_tag.cat_id=48 ORDER BY custom_tag.id DESC");
$date_range_step = $custom_date_range->customtag ?: '1';
$date_range_unit = str_replace( array( '(', ')' ), '', $custom_date_range->mastertag ) ?: 'months';
$date_range = '-'.$date_range_step.' '.$date_range_unit;

$latest_end = $latest_measure_date->measure_date;
$latest_start = date( 'Y-m-d', strtotime( "$latest_end $date_range" ) );
$month_end = date_format( date_create( $_GET['end'] ), 'd-M-Y' );
$month_start = date_format( date_create( $_GET['start'] ), 'd-M-Y' );

if( $measure_toggle == 86 ) : $measure_query = '=86'; elseif( $mod_query == 1 && $calendar == 231 ) : $measure_query = '=231'; else : $measure_query = ' IS NULL'; endif;

if( !empty( $add_url ) && $user_role != 225 ) : /* subscriber */ ?>

	<article class="col-xl-8 px-3">
		<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">

			<header class="header-flexbox">
				<h1 class="h4-style">Data <i class="fal fa-chevron-double-right small"></i> <?php echo $module; ?> <i class="fal fa-chevron-double-right small"></i> Add <?php echo $title;

				if( !empty( $help_id ) ) : ?> <a href="<?php echo $site_url.'/help/?p='.$help_id ?>" class="h4-style"> <i class="far fa-question-circle" aria-hidden="true"></i></a> <?php endif; ?> </h1>

			</header>

			<small>Fields marked with an asterisk<sup class="text-danger">*</sup> are required</small> <?php

			$args = array(
				'cat_id' => $cat_id,
				'tag_id' => $tag_id,
				// 'extra_value' => $cat_id,
				// ' ' => $latest_start,
				// 'latest_end' => $latest_end,
				// 'title'		=> $title /* required? */
			);

			get_template_part('/parts/forms/form', $module_strip, $args );

		

// measures_data_form( $latest_start, $latest_end, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_measure_end_formatted, $edit_bednight, $edit_roomnight, $edit_client, $edit_staff, $edit_area, $edit_note, $edit_parent_id );
// operations_form( $cat_id, $latest_start, $latest_end, $edit_operations, $edit_id, $employee_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_utility_id, $edit_amount, $edit_cost, $edit_disposal, $edit_disposal_id, $edit_note, $edit_parent_id );
// labour_form( $edit_labour, $latest_start, $latest_end, $edit_id, $employee_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_hometown_id, $edit_gender_id, $edit_ethnicity_id, $edit_disability_id, $edit_level_id, $edit_role_id, $edit_part_time_id, $edit_promoted_id, $edit_under16_id, $edit_start_date, $edit_start_date_formatted, $edit_leave_date, $edit_leave_date_formatted, $edit_days_worked, $edit_time_mentored, $edit_contract_dpw, $edit_contract_wpy, $edit_annual_leave, $edit_salary, $edit_overtime, $edit_bonuses, $edit_gratuities, $edit_benefits, $edit_cost_training, $edit_training_days, $edit_note, $edit_parent_id );
// charity_form( $edit_charity, $latest_start, $latest_end, $edit_id, $donation_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_donee_location_id, $edit_value_type_id, $edit_amount, $edit_duration, $edit_note, $edit_parent_id );
// supply_chain_form( $edit_supply, $latest_start, $latest_end, $edit_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_source_id, $edit_amount, $edit_tax, $edit_note, $edit_parent_id );
?>

		</section>
	</article>

	<aside class="col-xl-4 pr-3">
		<section class="secondary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
			<h2 class="h4-style">Latest Entries</h2> <?php

			if( $mod_id == 2 ) : /* snippet-operations */ 
				$extra_value = $cat_id;
			
			elseif( $mod_id == 3 ) : /* snippet-labour */
				$extra_value = $tag_id;
			
			endif;

			$args = array(
				'extra_value' => $extra_value,
				'title'		=> $title
			);

			get_template_part('/parts/latest-entries/latest-entry', $module_strip, $args ); ?>

			<a href="<?php echo $site_url.'/'.$slug.'/?edit='.$add_url.'&start='.$latest_start.'&end='.$latest_end ?>" class="btn btn-secondary">Edit <?php echo $add ?></a>
		</section> <?php

		$uploads = $wpdb->get_row( "SELECT upload FROM master_upload INNER JOIN master_tag ON master_upload.tag_id=master_tag.id WHERE mod_id=$mod_id AND measure$measure_query AND tag_toggle=$tag_toggle AND tag='$title'" );

		if( ( $plan_id == 3 || $plan_id == 4) && !empty( $uploads ) ) : ?>

			<section class="dark-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
				<h2 class="h4-style">Upload Entries</h2> <?php

				echo do_shortcode( '[gravityform id="36" title="false" description="false" ajax="true"]' );

					/* if( $_FILES['csv']['size'] > 0 && $_FILES['csv']['type'] == 'text/csv' ) :

							$file = $_FILES['csv']['tmp_name'];
							$fileHandle = fopen($file, "r");
							$i=0;

							$loc_name = $_POST['loc_name'];
							$utility_type = (int)$_POST['utility_type']; // Get these from page
							$employee_type = (int)$_POST['employee_type'];
							$donation_type = (int)$_POST['donation_type'];

							while( ( $cell = fgetcsv( $fileHandle, 0, "," ) ) !== FALSE ) :

								$i++;
								$cell0_check = $cell[0];
								$cell1_check = $cell[1];

								if( $measure_toggle == 86 && $employee_type != 69 && $employee_type != 70 && $employee_type != 71 && $employee_type != 228 && ( empty( $cell0_check ) || empty( $cell1_check ) ) ) :

									$cell_check = 0;

								elseif( $measure_toggle == 84 && $mod_query == 1 && $calendar == 231 && ( empty( $cell0_check ) || empty( $cell1_check ) ) ) :

									$cell_check = 0;

								elseif( $measure_toggle == 86 && $employee_type != 72 && $employee_type != 73 && empty( $cell0_check ) ) :

									$cell_check = 0;

								elseif( $measure_toggle != 86 && empty( $cell0_check ) ) :

									$cell_check = 0;

								else :

									$cell_check = 1;

								endif;

								if( $i>1 && !empty( $cell_check ) ) :

									csv_upload( $cell, $mod_query, $utility_type, $employee_type, $donation_type, $loc_name );

								endif;

							endwhile;

							header ( "Location: $site_url/$slug/?mod=$mod_query" );

						elseif( $_FILES['csv']['size'] > 0 ) :

							echo 'The file you tried to upload is empty';

						elseif( $_FILES['csv']['type'] == 'text/csv' ) :

							echo 'The file you tried to upload is not a csv';

						endif; */ ?>

				<div class="clearfix"></div>

				<div class="d-flex align-items-center my-3">

					<div>
						<h5>Download File Template</h5>
						<p>Please use this template for uploading your data.</p>
					</div><?php

					$filename = $uploads->upload; ?>

					<div class="text-center" style="width:60%;">
						<a href="<?php echo $site_url.'/wp-content/themes/yardstick/downloads/'.$filename ?>"><i class="fad fa-file-excel" style="font-size:32px;" aria-hidden="true"></i><br /><?php echo $filename ?></a>
					</div>

				</div>

				<p><strong class="text-danger">! PLEASE NOTE ! </strong><br />Uploaded <?php echo strtolower( $title ) ?> takes up to 24 hours to appear in the system.</p>
			</section> <?php
		endif; ?>
	</aside> <?php

elseif( !empty( $edit ) && $user_role != 225 ) : /* subscriber */ ?>

	<article class="col-xl-12 px-3">
		<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">

			<header class="header-flexbox">
				<h1 class="h4-style">Data <i class="fal fa-chevron-double-right small"></i> <?php echo $module; ?> <i class="fal fa-chevron-double-right small"></i> Edit <?php echo $title;

				if( !empty( $help_id ) ) : ?> <a href="<?php echo $site_url.'/help/?p='.$help_id ?>" class="h4-style"> <i class="far fa-question-circle" aria-hidden="true"></i></a> <?php endif; ?> </h1>

				<form method="post" name="change-date-range" id="change-date-range">
					<div class="form-group">
						<div class="input-group mb-2">
							<div class="input-group-prepend"><div class="input-group-text">SELECT DATE RANGE</div></div>
							<input type="text" class="form-control date" name="edit-date-range-start" aria-describedby="edit_date_range_start" placeholder="dd-mmm-yyyy" value="<?php echo $month_start ?>" data-date-end-date="0d" required>
							<input type="text" class="form-control date" name="edit-date-range-end" aria-describedby="edit-date-range-end" placeholder="dd-mmm-yyyy" value="<?php echo $month_end ?>" data-date-end-date="0d" required>
							<div class="input-group-append"><button type="submit" class="btn btn-primary" name="change-date-range"><i class="far fa-calendar-alt"></i></button></div>
						</div>
						<small class="form-text text-muted text-right">Large date ranges will cause the page to load slowly</small>
					</div>

				</form> <?php

				if( isset( $_POST['change-date-range'] ) ) :
					$change_date_range_start = date( 'Y-m-d', strtotime( $_POST['edit-date-range-start'] ) );
					$change_date_range_end = date( 'Y-m-d', strtotime( $_POST['edit-date-range-end'] ) );

					header ('Location:'.$site_url.'/'.$slug.'/?edit='.$edit_url.'&start='.$change_date_range_start.'&end='.$change_date_range_end);
					ob_end_flush();
				endif; ?>

			</header>  <?php

			$edit_function = $module_strip.'_edit';

			if( $mod_id == 2 ) : /* snippet-operations */ $extra_value = $cat_id; elseif( $mod_id == 3 || $mod_id == 4 ) : /* snippet-labour */ $extra_value = $tag_id; endif;

				$args = array(
					'edit' => $edit,
					'extra_value' => $cat_id,
					'module_strip' => $module_strip,
					'title'		=> $title
				);
	
				get_template_part('/parts/tables/table', $module_strip, $args ); 

				// $edit_function( $edit, $latest_start, $latest_end, $title, $extra_value ); ?>

		</section>
	</article> <?php

else : ?>

	<article class="col-xl-12 px-3">
		<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
			<p>You have been assigned the <?php echo strtolower( $user_role_tag ); ?> user role that does not have access to this section. Please contact your adminstrator.</p>
		</section>
	</article> <?php

endif;

$custom_page_length = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE cat_id='49' AND loc_id=$master_loc ORDER BY id DESC" );
$page_length = $custom_page_length->tag ?: 25; ?>

<!-- datatables -->
<script>
	$(document).ready(function() {
  	$('#latest').DataTable({
			searching: false,
			paging: false,
			info: false,
			columnDefs: [{ orderable: false }],
			bSort: false
		});

		$('#edit').DataTable( {
			columnDefs: [
				{ targets: 0, width: "145px" },
				{ targets: 0, orderable: false },
				{ targets: [ 0, 1 ], searchable: false }
			],
			dom: '<"top"fB>rt<"bottom"lip><"clear">',
			buttons: [ {
				extend: 'excel',
				text: '<i class="far fa-file-excel"></i> Export',
				title: "Data-<?php echo $title; ?>",
				exportOptions: { columns: '1, 2, 3, 4, 5, 6' }
			} ],
			pageLength: <?php echo $page_length; ?>,
			order: [[ 1, 'desc' ]],
      scrollX: true,
			language: {
    		search: "Search <?php echo $title; ?> Data ",
				paginate: {
      		first:    '<i class="fad fa-fast-backward"></i>',
					previous: '<i class="fad fa-step-backward"></i>',
					next:     '<i class="fad fa-step-forward"></i>',
					last:     '<i class="fad fa-fast-forward"></i>'
    		}
			},

			initComplete: function () {
			this.api().columns('.filter-column').every( function () {

          var column = this;
          var select = $('<select><option value="">All</option></select>')
            .appendTo( $(column.footer()).empty() )
            .on( 'change', function () {
              var val = $.fn.dataTable.util.escapeRegex(
                $(this).val()
              );

                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    } );

                column.data().unique().sort().each( function ( d, j ) {
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                } );
            } );
        }
    } );



	});
</script>

<!-- date picker -->
<script type="text/javascript" src="<?php get_template_directory_uri(); ?>/lib/js/date-picker.js"></script>
<script>
	$('.date').datepicker({
		format: 'd-M-yyyy',
		endDate: '+0d',
		autoclose: true
	});
</script>

<!-- repeater fields -->
<script>
	$(function() {
		$(document).on('click', '.btn-add', function(e) {
			e.preventDefault();
			var controlForm = $('#repeater-field:first'),
			currentEntry = $(this).parents('.entry:first'),
			newEntry = $(currentEntry.clone()).appendTo(controlForm);
			newEntry.find('input').val('');
			controlForm.find('.entry:not(:last) .btn-add')
			.removeClass('btn-add').addClass('btn-remove')
			.removeClass('btn-success').addClass('btn-danger')
			.html('<i class="fas fa-minus"></i>');
		}).on('click', '.btn-remove', function(e) {
			e.preventDefault();
			$(this).parents('.entry:first').remove();
			return false;
		});
	});
</script>

<!-- form validation -->
<script>
	(function() {
	  'use strict';
	  window.addEventListener('load', function() {
		var forms = document.getElementsByClassName('needs-validation');
		var validation = Array.prototype.filter.call(forms, function(form) {
		  form.addEventListener('submit', function(event) {
			if (form.checkValidity() === false) {
			  event.preventDefault();
			  event.stopPropagation();
			}
			form.classList.add('was-validated');
		  }, false);
		});
	  }, false);
	})();
</script> <?php

get_footer(); ?>
