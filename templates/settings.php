<?php ob_start();

/* Template Name: Settings

Template Post Type: Page

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/yardstick
@copyright	Copyright (c) 2022, Digital Rockpool LTD
@license	GPL-2.0+ */

get_header('tool');

global $wpdb;
global $post;


$site_url = get_site_url().'/yardstick';
$slug = $post->post_name;

$master_loc = $_SESSION['master_loc'];

$user_id = get_current_user_id();
$user_role = $_SESSION['user_role'];
$user_role_tag = $_SESSION['user_role_tag'];

$plan_id = $_SESSION['plan_id'];

$entry_date = date( 'Y-m-d H:i:s' );

$measure_toggle = $_SESSION['measure_toggle'];
$measure_toggle_name = $_SESSION['measure_toggle_name'];
$tag_toggle = $_SESSION['tag_toggle'];

$setting_query = $_GET['setting'];
$setting = str_replace( '_', ' ', $setting_query );
$setting_query_edits = $setting_query.'-edits';
$setting_query_revisions = $setting_query.'-revisions';
$setting_setup = $wpdb->get_row( "SELECT master_setting.id, module, title, title_singular, cat_id, category, help_id FROM master_setting LEFT JOIN master_module ON master_setting.mod_id=master_module.id LEFT JOIN master_category ON master_setting.cat_id=master_category.id WHERE title='$setting'" );
$set_id = $setting_setup->id;
$module = $setting_setup->module;
$module_strip = str_replace( ' ', '_', strtolower($module) );
$title = $setting_setup->title;
$title_singular = $setting_setup->title_singular;
$cat_id = $setting_setup->cat_id;
$category = $setting_setup->category;
$help_id = $setting_setup->help_id;

if( $user_role == 222 || $user_role == 223 ) : /* super-admin || admin */ ?>

	<article class="col-xl-8 px-3">
		<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
			<header class="header-flexbox">
				<h1 class="h4-style">Settings <?php if( !empty( $module ) ) : echo '<i class="fa-solid fa-chevrons-right"></i> '.$module; endif; ?> <i class="fa-solid fa-chevrons-right"></i> <?php echo $title; ?></h1> <?php

				if( !empty( $help_id ) ) : ?> <a href="<?php echo $site_url.'/help/?p='.$help_id ?>" class="h4-style"> <i class="fa-duotone fa-circle-question" aria-hidden="true"></i></a> <?php endif; ?>
			</header> <?php

			$args = array(
					'cat_id' => $cat_id,
					'title'	=> $title,
					'title_singular' => $title_singular
				);
	
				get_template_part('/parts/settings/setting', $setting_query, $args ); ?>

		</section> <?php

		if( $setting != 'report settings' && $setting != 'data settings' && ( $set_id != 1 || $measure_toggle == 86 ) && ( $set_id !=2 || $tag_toggle == 1 ) && ( $set_id !=17 || $tag_toggle == 1 ) ) :  ?>
			<section class="dark-box p-3 mb-4 bg-white shadow-sm">

				<h2 class="h4-style"> <?php echo $title ?></h2> <?php

				if( $set_id == 2 || $set_id == 12 ) : // categories || locations

					$args = array(
						'title'	=> $title,
						'title_singular' => $title_singular
					);
		
					get_template_part('/parts/settings/setting', $setting_query_edits, $args );

					// category_edit_setting( $title, $title_singular )

					// location_edit_setting( $title );

				else :

					if( $set_id == 17 ) : // Tag

						$edit_rows  = $wpdb->get_results( "SELECT custom_tag.id, custom_tag.tag AS custom_tag, category, custom_category.parent_id AS custom_cat, custom_tag.parent_id, custom_tag.active FROM custom_tag LEFT JOIN custom_category ON custom_tag.custom_cat=custom_category.parent_id WHERE custom_tag.cat_id=$cat_id AND custom_tag.loc_id=$master_loc AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) AND custom_category.id IN (SELECT MAX(id) FROM custom_category GROUP BY parent_id) ORDER BY active DESC, custom_tag.tag ASC" );

					else :

						$edit_rows  = $wpdb->get_results( "SELECT custom_tag.id, custom_tag.tag_id, custom_tag.tag AS custom_tag, system_tag.tag AS system_tag, unit_tag.tag AS unit_tag, size, unit_tag.id AS unit_id, custom_tag.parent_id, custom_tag.active FROM custom_tag LEFT JOIN master_tag system_tag ON custom_tag.tag_id=system_tag.id LEFT JOIN master_tag unit_tag ON custom_tag.unit_id=unit_tag.id WHERE custom_tag.cat_id=$cat_id AND custom_tag.loc_id=$master_loc AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY active DESC, custom_tag.tag ASC, system_tag.tag ASC" );

					endif;

					if( empty( $edit_rows ) ) :

						echo 'Please add the '.strtolower( $title ).' used by your business.';

					else : ?>

						<div class="table-responsive-xl">
							<table id="tags" class="table table-borderless">
								<thead>
									<tr>
										<th scope="col" class="no-sort">View<?php if( $set_id != 13 && $set_id != 14 /* labour setting && operation setting */ ) : echo ' | Delete'; endif; if( $set_id != 6 && $set_id != 8 && $set_id != 11 /* disposal method && employee type && donation type */ ) :  echo ' | Edit'; endif; ?></th>
										<th scope="col">Sort <?php echo $title ?></th> <?php
										if( $set_id == 17 ) : ?><th scope="col">Sort Category</th> <?php endif; ?>
									</tr>
								</thead>

								<tbody> <?php

									foreach ( $edit_rows as $edit_row ) :

										$edit_id = $edit_row->id;
										$edit_entry_system_id = $edit_row->tag_id;
										$edit_system = $edit_row->system_tag;
										$edit_entry_custom = $edit_row->custom_tag;
										$edit_entry_size = $edit_row->size;
										$edit_unit = $edit_row->unit_tag;
										$edit_entry_unit_id = $edit_row->unit_id;
										$edit_category = $edit_row->category;
										$edit_entry_custom_cat = $edit_row->custom_cat;
										$edit_parent_id = $edit_row->parent_id;
										$edit_active = $edit_row->active;
										$edit_update = 'update-'.$edit_id;
										$edit_archive = 'archive-'.$edit_id;

										if( empty( $edit_entry_system_id ) ) : $edit_system_id = NULL; else : $edit_system_id = $edit_entry_system_id; endif;
										if( empty( $edit_entry_custom) ) : $edit_custom = NULL; elseif( is_numeric( $edit_entry_custom ) || is_float( $edit_entry_custom ) ) : $edit_custom_decimal_clean = rtrim( number_format( $edit_entry_custom, 2 ) , '0' ); $edit_custom = rtrim( $edit_custom_decimal_clean, '.' ); else : $edit_custom = $edit_entry_custom; endif;
										if( empty( $edit_entry_size ) ) : $edit_size = NULL; else : $edit_size_decimal_clean = rtrim( number_format( $edit_entry_size, 2 ) , '0' ); $edit_size = rtrim( $edit_size_decimal_clean, '.' ); endif;
										if( empty( $edit_entry_unit_id ) ) : $edit_unit_id = NULL; else : $edit_unit_id = $edit_entry_unit_id; endif;
										if( empty( $edit_entry_custom_cat ) ) : $edit_custom_cat = NULL; else : $edit_custom_cat = $edit_entry_custom_cat; endif;

										if( !empty( $edit_system ) && empty( $edit_size ) && empty( $edit_unit ) ) : $row_item = $edit_system; endif;
										if( !empty( $edit_custom ) && empty( $edit_size ) && empty( $edit_unit ) ) : $row_item = $edit_custom; $col_title = $title; endif;
										if( !empty( $edit_system ) && !empty( $edit_unit ) && empty( $edit_size ) ) : $row_item = $edit_system.' ('.$edit_unit.')'; $col_title = 'Unit'; endif;
										if( !empty( $edit_system ) && !empty( $edit_custom ) && empty( $edit_size ) && empty( $edit_unit) ) : $row_item = $edit_system.' - '.$edit_custom; $col_title = $title; endif;
										if( !empty( $edit_system ) && empty( $edit_custom ) && !empty( $edit_size ) && !empty( $edit_unit) ) : $row_item = $edit_system.' ('.$edit_size.' '.$edit_unit.')'; $col_title = 'Size / Unit'; endif;
										if( !empty( $edit_system ) && !empty( $edit_custom ) && !empty( $edit_size ) && !empty( $edit_unit) ) : $row_item = $edit_system.' - '.$edit_custom.' ('.$edit_size.' '.$edit_unit.')'; $col_title = $edit_system; endif; ?>

										<tr <?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
											<td class="align-top strikeout-buttons">

												<button type="button" class="btn btn-dark d-inline-block" data-bs-toggle="modal" data-bs-target="#modalRevisions-<?php echo $edit_id ?>"><i class="fa-regular fa-eye"></i></button>

												<div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
													<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
														<div class="modal-content">

															<div class="modal-header">
																<h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $row_item ?></h5>
																<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa-regular fa-circle-xmark"></i></span></button>
															</div>

															<div class="modal-body"> <?php

																if( $set_id == 17 ) : // Tag

																	$revision_rows = $wpdb->get_results( "SELECT custom_tag.id, custom_tag.entry_date, custom_tag.tag AS custom_tag, category, custom_tag.parent_id, display_name, custom_tag.active FROM custom_tag INNER JOIN wp_users ON custom_tag.user_id=wp_users.id LEFT JOIN custom_category ON custom_tag.custom_cat=custom_category.parent_id AND custom_category.id IN (SELECT MAX(id) FROM custom_category GROUP BY parent_id) WHERE custom_tag.parent_id=$edit_parent_id ORDER BY custom_tag.id DESC" );

																else :

																	$revision_rows = $wpdb->get_results( "SELECT custom_tag.id, entry_date, custom_tag.tag AS custom_tag, system_tag.tag AS system_tag, size, master_tag.tag AS unit_tag, parent_id, display_name, active FROM custom_tag INNER JOIN wp_users ON custom_tag.user_id=wp_users.id LEFT JOIN master_tag system_tag ON custom_tag.tag_id=system_tag.id LEFT JOIN master_tag ON custom_tag.unit_id=master_tag.id WHERE parent_id=$edit_parent_id ORDER BY custom_tag.id DESC" );

																endif;

																foreach( $revision_rows as $revision_row ) :

																	$revision_id = $revision_row->id;
																	$revision_entry_date = date_create( $revision_row->entry_date );
																	$revision_custom_tag_type = $revision_row->custom_tag;
																	$revision_system_tag = $revision_row->system_tag;
																	$revision_size = $revision_row->size;
																	$revision_unit = $revision_row->unit_tag;
																	$revision_category = $revision_row->category;
																	$revision_parent_id = $revision_row->parent_id;
																	$revision_active = $revision_row->active;
																	$revision_username = $revision_row->display_name;

																	if( is_numeric( $revision_custom_tag_type ) ) :

																		$revision_custom_tag_decimal_clean = rtrim( number_format( $revision_custom_tag_type, 2 ) , '0' );
																		$revision_custom_tag = rtrim( $revision_custom_tag_decimal_clean, '.' );

																	else :

																		$revision_custom_tag = $revision_custom_tag_type;

																	endif;

																	if( !empty( $edit_custom ) && !empty( $edit_size ) ) :

																		$revision_row_item = '<b>'.$title_singular.':</b> '.$revision_system_tag.' - '.$revision_custom_tag.' ('.$revision_size.' '.$revision_unit.')<br />';

																	elseif( !empty( $edit_system ) && !empty( $edit_custom ) && empty( $edit_size ) && empty( $edit_unit ) ) :
																		$revision_row_item = '<b>'.$revision_system_tag.':</b> '.$revision_custom_tag.'<br />';

																	elseif( !empty( $edit_system ) && empty( $edit_custom ) && !empty( $edit_size ) ) :

																		$revision_row_item = '<b>'.$title_singular.':</b> '.$revision_system_tag.' ('.$revision_size.' '.$revision_unit.')<br />';

																	elseif( !empty( $edit_system ) && empty( $edit_custom ) && empty( $edit_size ) && !empty( $edit_unit ) ) :

																		$revision_row_item = '<b>'.$title_singular.':</b> '.$revision_system_tag.' ('.$revision_unit.')<br />';

																	elseif( !empty( $edit_category ) ) :

																		$revision_row_item = '<b>'.$title_singular.':</b> '.$revision_category.' - '.$revision_custom_tag.'<br />';

																	else :

																		$revision_row_item = '<b>'.$title_singular.'d:</b> '.$revision_system_tag.$revision_custom_tag.'<br />';

																	endif;

																	if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

																	echo $revision_row_item;
																	echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';
																	echo '<b>Entry ID:</b> '.$revision_id.'<br />';

																	if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

																endforeach; ?>

															</div>

														</div>
													</div>
												</div> <?php

												if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="fa-solid fa-trash-can"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1; $btn_style = 'btn-success'; $edit_value = '<i class="fa-solid fa-trash-can-arrow-up"></i>'; endif;

												if( $set_id != 13 && $set_id != 14 ) : /* labour setting && operation setting */ ?>

													<form method="post" name="archive" id="<?php echo $edit_archive ?>" class="d-inline-block">
													<button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $edit_archive ?>"><?php echo $edit_value ?></button>
													</form> <?php

												endif;

												if ( isset( $_POST[$edit_archive] ) ) :

													$wpdb->insert( 'custom_tag',
														array(
															'entry_date' => $entry_date,
															'record_type' => 'entry_revision',
															'tag' => $edit_custom,
															'tag_id' => $edit_system_id,
															'size' => $edit_size,
															'unit_id' => $edit_unit_id,
															'custom_cat' => $edit_custom_cat,
															'cat_id' => $cat_id,
															'parent_id' => $edit_parent_id,
															'user_id' => $user_id,
															'active' => $edit_active_update,
															'loc_id' => $master_loc
														)
													);

													header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
													ob_end_flush();

												endif;

												if( !empty( $col_title ) && $edit_active == 1 ) : ?>

													<button type="button" class="btn btn-light d-inline-block" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $edit_id ?>"><i class="fa-solid fa-pencil"></i></button>

													<div class="modal fade" id="modal-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-<?php echo $edit_id ?>Title" aria-hidden="true">
														<div class="modal-dialog modal-dialog-centered" role="document">
															<div class="modal-content">

																<div class="modal-header">
																	<h5 class="modal-title" id="modal-<?php echo $edit_id ?>Title"><?php echo $row_item ?></h5>
																	<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa-regular fa-circle-xmark"></i></span></button>
																</div>

																<div class="modal-body">
																	<form method="post" name="update" id="<?php echo $edit_update ?>">

																		<div class="input-group mb-3"> <?php

																			if( !empty( $edit_size ) ) : ?>

																				<input type="text" class="form-control" value="<?php echo $edit_custom ?>" aria-label="Tag Update" aria-describedby="tagUpdate" name="update_tag" placeholder="description">
																				<input type="number" class="form-control" value="<?php echo preg_replace( '/[^\d.]/', '', $edit_size ) ?>" aria-label="Tag Update" aria-describedby="tagUpdate" name="update_size" placeholder="size" min="1" step="0.01"> <?php

																			endif;

																			if( !empty( $edit_unit_id ) ) :

																				$dropdowns = $wpdb->get_results( "SELECT master_tag.id, master_tag.tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE parent_id=$edit_system_id AND relation <> 'waste-disposal'" );  ?>

																				<select class="form-select d-inline-block" id="unitUpdate" aria-label="Unit Update" name="update_unit"><?php

																					foreach( $dropdowns as $dropdown ) :

																						$edit_dropdown_unit_id = $dropdown->id;
																						$edit_dropdown_unit = $dropdown->tag;

																						if( $edit_dropdown_unit == $edit_unit ) : $selected = 'selected'; else : $selected = ''; endif;

																						echo '<option value="'.$edit_dropdown_unit_id.'" '.$selected.'>'.$edit_dropdown_unit.'</option>';

																					endforeach; ?>

																				</select> <?php

																			elseif( !empty( $edit_custom_cat ) ) :

																				$dropdowns = $wpdb->get_results( "SELECT parent_id, category FROM custom_category WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_category GROUP BY parent_id) ORDER BY category ASC" );  ?>

																				<select class="form-select d-inline-block" id="categoryUpdate" aria-label="Category Update" name="update_category"><?php

																					foreach( $dropdowns as $dropdown ) :

																						$edit_dropdown_custom_cat = $dropdown->parent_id;
																						$edit_dropdown_category = $dropdown->category;

																						if( $edit_dropdown_custom_cat == $edit_custom_cat ) : $selected = 'selected'; else : $selected = ''; endif;

																						echo '<option value="'.$edit_dropdown_custom_cat.'" '.$selected.'>'.$edit_dropdown_category.'</option>';

																					endforeach; ?>

																				</select>

																				<input type="text" class="form-control" value="<?php echo $edit_custom ?>" aria-label="Tag Update" aria-describedby="tagUpdate" name="update_tag"><?php

																			elseif ( is_numeric( $edit_entry_custom ) || is_float( $edit_entry_custom ) ) :  ?>

																				<input type="number" class="form-control" value="<?php echo $edit_entry_custom ?>" aria-label="Tag Update" aria-describedby="tagUpdate" name="update_tag" min="1" <?php if( $edit_system_id == 280 /* days open */ ) : echo 'max="365"'; elseif( $edit_system_id == 281 /* contracted days per week */ ) : echo 'max="7"'; elseif($edit_system_id == 282 /* contracted weeks per year */ ) : echo 'max="52"'; elseif ($edit_system_id == 283 /* annual leave */ ) : echo 'max="365"'; endif; ?> <?php if( $set_id == 13 /* labour settings */ ) : echo 'step="0.1"'; elseif( $edit_system_id == 288 /* total area in m2 */ ) : echo 'step="0.01"'; else : echo 'step="1"'; endif; ?>> <?php

																			else : ?>

																				<input type="text" class="form-control" value="<?php echo $edit_custom ?>" aria-label="Tag Update" aria-describedby="tagUpdate" name="update_tag"> <?php

																			endif; ?>

																			<input type="submit" class="btn btn-primary d-inline-block" aria-describedby="tagUpdate" name="<?php echo $edit_update ?>" value="Update" />
																		</div>

																	</form>

																</div>

															</div>
														</div>
													</div> <?php

												endif;

												$update_tag = $_POST['update_tag'];
												$update_size = $_POST['update_size'];
												$update_unit = $_POST['update_unit'];
												$update_category = $_POST['update_category'];

												if( empty( $update_tag ) ) : $update_custom_tag = NULL; else : $update_custom_tag = $update_tag; endif;
												if( empty( $update_size ) ) : $update_size_tag = NULL; else : $update_size_tag = $update_size; endif;
												if( empty( $update_unit ) ) : $update_unit_id = NULL; else : $update_unit_id = $update_unit; endif;
												if( empty( $update_category ) ) : $update_custom_cat = NULL; else : $update_custom_cat = $update_category; endif;

												if ( isset( $_POST[$edit_update] ) ) :

													$wpdb->insert( 'custom_tag',
														array(
															'entry_date' => $entry_date,
															'record_type' => 'entry_revision',
															'tag' => $update_custom_tag,
															'tag_id' => $edit_system_id,
															'size' => $update_size_tag,
															'unit_id' => $update_unit_id,
															'custom_cat' => $update_custom_cat,
															'cat_id' => $cat_id,
															'parent_id' => $edit_parent_id,
															'user_id' => $user_id,
															'active' => 1,
															'loc_id' => $master_loc
														)
													);

													header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
													ob_end_flush();

												endif; ?>

											</td>
											<td><?php echo $row_item; ?></td> <?php
											if( $set_id == 17 ) : /* tags */ ?><td><?php echo $edit_category; ?></td> <?php endif; ?>
										</tr> <?php

									endforeach; ?>

								</tbody>
							</table>
						</div> <?php

					endif;

				endif; ?>

			</section> <?php
		endif; ?>

	</article>

	<aside class="col-xl-4 pr-3"> <?php

		if( $set_id == 15 ) :

			$args = array(
				'title'	=> $title
			);

			get_template_part('/parts/settings/setting', $setting_query_revisions, $args );

		endif; ?>

	</aside> <?php

else : ?>

	<article class="col-xl-8 px-3">
		<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
			<p>You have been assigned the <?php echo strtolower( $user_role_tag ); ?> user role that does not have access to this section. Please contact your adminstrator.</p>
		</section>
	</article> <?php

endif; ?>

<!-- date picker -->
<script>
	$('.date').datepicker({
		format: 'd-M',
		autoclose: true
 	});
</script>

<!-- datatables -->
<script>
	$(document).ready(function() {
    	$('#tags').DataTable({
			columnDefs: [
				{ width: "145px", targets: 0 },
				{ orderable: false, targets: 0 }
			],
			dom: '<"top"f>rt<"bottom"lip><"clear">',
			pageLength: 10,
			order: [[ 1, 'asc' ]],
        	/* scrollX: true, causing strange formatting */
			language: {
    			search: "Filter <?php echo $title; ?> ",
				paginate: {
      				first:    '<i class="fad fa-fast-backward"></i>',
					previous: '<i class="fad fa-step-backward"></i>',
					next:     '<i class="fad fa-step-forward"></i>',
					last:     '<i class="fad fa-fast-forward"></i>'
    			}
			}
		});
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
			newEntry.find('select').val('');
			newEntry.find('input').val('');
			controlForm.find('.entry:not(:last) .btn-add')
			.removeClass('btn-add').addClass('btn-remove')
			.removeClass('btn-success').addClass('btn-danger')
			.html('<i class="fa-solid fa-minus"></i>');
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
</script>

<?php

get_footer('tool'); ?>
