<?php ob_start();

/* Template Name: Settings

Template Post Type: Page

@package	Logstock
@author		Digital Rockpool
@link		https://logstock.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
@license	GPL-2.0+ */


get_header();

global $wpdb;
global $post;

$site_url = get_site_url();
$slug = $post->post_name;

$user_id = get_current_user_id();
$master_loc = $_SESSION['master_loc'];
$entry_date = date( 'Y-m-d H:i:s' );

$title = ucfirst( $_GET['setting'] );
$title_singular =  rtrim( $title,'s' );
$record_type = strtolower( $title_singular );
$record_type_revision = strtolower( $title_singular.'_revision' );
$show_help = get_field('show_help'); ?>

<article class="col-xl-12 px-3">
	<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		<header class="header-flexbox">

			<h1 class="h4-style">Add <?php echo $title ?></h1> <?php

			if( !empty( $show_help ) ) : ?> <a href="<?php echo $show_help ?>" class="h4-style"> <i class="fa-duotone fa-circle-question" aria-hidden="true"></i></a> <?php endif; ?>

		</header> <?php

		if( $record_type == 'location' ) :

			echo do_shortcode( '[gravityform id="2" title="false" description="false" ajax="false"]' ); ?>

			</section> <?php

			location_add_setting( $title );

		else :

			$edit_rows = $wpdb->get_results( "SELECT custom_record.id, custom_record.entry_date, custom_record.entry_name as item, card_record.entry_name as card, custom_record.size, custom_record.unit_id, master_tag.tag, custom_record.card_id, custom_record.active, custom_record.parent_id, display_name FROM custom_record LEFT JOIN master_tag ON custom_record.unit_id=master_tag.id INNER JOIN log_users ON custom_record.user_id=log_users.id LEFT JOIN custom_record card_record ON (custom_record.card_id=card_record.parent_id) AND card_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) WHERE (custom_record.record_type='$record_type' OR custom_record.record_type='$record_type_revision') AND custom_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) AND custom_record.loc_id=$master_loc ORDER BY custom_record.active DESC, custom_record.entry_name ASC" ); ?>

			<form method="post" name="add-entry-name"> <?php

				if( $record_type == 'item' ) : ?>
					<div class="entry form-row mb-1">
						<div class="col-10"> <?php
							$dropdowns = $wpdb->get_results( "SELECT parent_id, entry_name FROM custom_record WHERE (record_type='card' OR record_type='card_revision') AND active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) ORDER BY entry_name ASC" );  ?>

							<select class="custom-select" name="set-card-name" required>
								<option value="" disabled selected>Select Card *</option><?php

								foreach( $dropdowns as $dropdown ) :

									$dropdown_card_id = $dropdown->parent_id;
									$dropdown_card = $dropdown->entry_name;

									echo '<option value="'.$dropdown_card_id.'">'.$dropdown_card.'</option>';

								endforeach; ?>

							</select>

						</div>
					</div> <?php
				endif; ?>

				<div id="repeater-field">
					<div class="entry form-row mb-1">
						<div class="col-10<?php if( $record_type == 'item' ) : echo ' input-group'; endif; ?>">
							<input type="text" name="set-entry-name[]" class="form-control" placeholder="Enter <?php echo $title_singular ?> Name *" required> <?php

							if( $record_type == 'item' ) :

								$unit_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=1 ORDER BY tag ASC" );  ?>

								<select class="custom-select" id="set-entry-unit" name="set-entry-unit[]">
									<option value="no-unit" selected>Select Unit (optional)</option><?php

									foreach( $unit_dropdowns as $unit_dropdown ) :

										$dropdown_unit_id = $unit_dropdown->id;
										$dropdown_unit = $unit_dropdown->tag;

										echo '<option value="'.$dropdown_unit_id.'">'.$dropdown_unit.'</option>';

									endforeach; ?>

								</select>

								<input type="number" id="entry-size" name="set-entry-size[]" class="form-control" step="0.01" placeholder="Size (optional)"> <?php

							endif; ?>

						</div>

						<div class="col-2">
							<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fa-solid fa-plus"></i></button></span>
						</div>
					</div>
				</div>

				<div class="form-row">
					<div class="col-2 offset-10 mb-3"><button class="btn btn-primary" type="submit" name="add-entry-name">Add</button></div>
				</div>
			</form> <?php

			if( $record_type == 'item' ) : $set_card_name = $_POST['set-card-name']; else : $set_card_name = NULL; endif;

			$set_entry_name_array = $_POST['set-entry-name'];
			$set_entry_size_array = $_POST['set-entry-size'];
			$set_entry_unit_array = $_POST['set-entry-unit'];

			if ( isset( $_POST['add-entry-name'] ) ) :

				foreach( $set_entry_name_array as $index => $set_entry_name_array ) :

					$set_entry_name = $set_entry_name_array;
					$set_entry_size_null = $set_entry_size_array[$index];
					$set_entry_unit_null = $set_entry_unit_array[$index];

					if( $set_entry_unit_null == "no-unit") :
						$set_entry_unit = NULL;
						$set_entry_size = NULL;

					elseif( !empty( $set_entry_unit_null ) && empty( $set_entry_size_null )) :
							$set_entry_unit = $set_entry_unit_null;
							$set_entry_size = 1;

					else :
						$set_entry_unit = $set_entry_unit_null;
						$set_entry_size = $set_entry_size_null;

					endif;

					$wpdb->insert(
						'custom_record',
						array(
							'entry_date' => $entry_date,
							'entry_name' => stripslashes( $set_entry_name ),
							'record_type' => $record_type,
							'size' => $set_entry_size,
							'unit_id' => $set_entry_unit,
							'card_id' => $set_card_name,
							'active' => 1,
							'parent_id' => 0,
							'user_id' => $user_id,
							'loc_id' => $master_loc
						)
					);

					$parent_id = $wpdb->insert_id;

					$wpdb->update(
						'custom_record',
						array(
							'entry_date' => $entry_date,
							'entry_name' => stripslashes( $set_entry_name ),
							'record_type' => $record_type,
							'size' => $set_entry_size,
							'unit_id' => $set_entry_unit,
							'card_id' => $set_card_name,
							'active' => 1,
							'parent_id' => $parent_id,
							'user_id' => $user_id,
							'loc_id' => $master_loc
						),
						array(
							'id' => $parent_id
						)
					);

				endforeach;

				header ('Location:'.$site_url.'/'.$slug.'?setting='.strtolower( $title ));
				ob_end_flush();

			endif; ?>

		</section>

		<section class="dark-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">

			<div class="table-responsive-xl mb-3">
				<table id="edit" class="table table-borderless nowrap" style="width:100%;">
					<thead>
						<tr>
							<th scope="col" class="no-sort"><div class="edit-button-block d-inline-block">View</div><div class="edit-button-block d-inline-block">Edit</div><div class="edit-button-block d-inline-block">Delete</div></th>
							<th scope="col">Sort <?php echo $title ?></th> <?php
							if( $record_type == 'item' ) : ?> <th scope="col">Sort Cards</th> <?php endif; ?>
						</tr>
					</thead>

					<tbody> <?php

						foreach ( $edit_rows as $edit_row ) :

							$edit_id = $edit_row->id;
							$edit_entry_name = $edit_row->item;
							$edit_entry_size = $edit_row->size;
							$edit_entry_unit_id = $edit_row->unit_id;
							$edit_entry_unit = $edit_row->tag;
							$edit_card_name = $edit_row->card;
							$edit_card_id = $edit_row->card_id;
							$edit_active = $edit_row->active;
							$edit_parent_id = $edit_row->parent_id;
							$edit_update = 'update-'.$edit_id;
							$edit_archive = 'archive-'.$edit_id; ?>

							<tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
								<td class="align-top strikeout-buttons"> <?php // start of view revisions ?>

									<button type="button" class="btn btn-dark d-inline-block edit-button-block" data-bs-toggle="modal" data-bs-target="#modalRevisions-<?php echo $edit_id ?>"><i class="fa-regular fa-eye"></i></button>

									<div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
										<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
											<div class="modal-content">

												<div class="modal-header">
													<h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $edit_entry_name ?></h5>
													<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa-regular fa-circle-xmark"></i></span></button>
												</div>

												<div class="modal-body"> <?php

													$revision_rows = $wpdb->get_results( "SELECT custom_record.id, custom_record.entry_date, custom_record.entry_name as item, card_record.entry_name as card, custom_record.size, master_tag.tag, custom_record.parent_id, display_name, custom_record.active FROM custom_record LEFT JOIN master_tag ON custom_record.unit_id=master_tag.id LEFT JOIN custom_record card_record ON (custom_record.card_id=card_record.parent_id) AND card_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) INNER JOIN log_users ON custom_record.user_id=log_users.id WHERE custom_record.parent_id=$edit_parent_id ORDER BY custom_record.id DESC" );

													foreach( $revision_rows as $revision_row ) :

														$revision_id = $revision_row->id;
														$revision_entry_date = date_create( $revision_row->entry_date );
														$revision_card_name = $revision_row->card;
														$revision_item_name = $revision_row->item; // shows card name when card only
														$revision_entry_size = $revision_row->size;
														$revision_entry_unit = $revision_row->tag;
														$revision_parent_id = $revision_row->parent_id;
														$revision_active = $revision_row->active;
														$revision_username = $revision_row->display_name;

														if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

														echo '<b>Card:</b> '.$revision_item_name.'<br />';
														if( $record_type == 'item' ) :
															echo '<b>Item:</b> '.$revision_item_name.'<br />';
															if( !empty( $revision_entry_size ) ) : echo '<b>Size:</b> '.$revision_entry_size.' '.$revision_entry_unit.'<br />'; endif;
														endif;
														echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';
														echo '<b>Entry ID:</b> '.$revision_id.'<br />';

														if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

													endforeach; ?>

												</div>


											</div>
										</div>
									</div> <?php // end of view revisions

									if( $edit_active == 1 ) : // start of edit ?>

										<button type="button" class="btn btn-light d-inline-block edit-button-block" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $edit_id ?>"><i class="fa-solid fa-pencil"></i></button>

										<div class="modal fade" id="modal-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-<?php echo $edit_id ?>Title" aria-hidden="true">
											<div class="modal-dialog modal-dialog-centered <?php if( $record_type == 'item' ) : echo 'modal-lg'; endif; ?>" role="document">
												<div class="modal-content">

													<div class="modal-header">
														<h5 class="modal-title" id="modal-<?php echo $edit_id ?>Title"><?php echo $edit_entry_name ?></h5>
														<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa-regular fa-circle-xmark"></i></span></button>
													</div>

													<div class="modal-body">
														<form method="post" name="<?php echo $edit_update ?>"> <?php

															if( $record_type == 'item' ) :

																$update_dropdowns = $wpdb->get_results( "SELECT parent_id, entry_name FROM custom_record WHERE (record_type='card' OR record_type='card_revision') AND active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) ORDER BY entry_name ASC" );  ?>

																<div class="entry form-row mb-1">
																	<div class="col-12">

																		<select class="custom-select" name="update-card-name">
																			<option value="" disabled selected>Select Card</option><?php

																			foreach( $update_dropdowns as $update_dropdown ) :

																				$update_dropdown_card_id = $update_dropdown->parent_id;
																				$update_dropdown_card = $update_dropdown->entry_name;

																				if( $edit_card_id == $update_dropdown_card_id ) : $selected = 'selected'; else : $selected = ''; endif;

																				echo '<option value="'.$update_dropdown_card_id.'" '.$selected.'>'.$update_dropdown_card.'</option>';

																			endforeach; ?>

																		</select>

																	</div>
																</div> <?php

															endif; ?>

															<div class="input-group mb-3">

																<input type="text" class="form-control" value="<?php echo $edit_entry_name ?>" name="update-entry-name"> <?php

																if( $record_type == 'item' ) : ?>

																	<input type="number" class="form-control" value="<?php echo $edit_entry_size ?>" name="update-entry-size" step="0.01" placeholder="Size"> <?php

																	$unit_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=1 ORDER BY tag ASC" );  ?>

																	<select class="custom-select" name="update-entry-unit">
																		<option value="" disabled selected>Select Unit</option><?php

																		foreach( $unit_dropdowns as $unit_dropdown ) :

																			$update_dropdown_unit_id = $unit_dropdown->id;
																			$update_dropdown_unit = $unit_dropdown->tag;

																			if( $edit_entry_unit_id == $update_dropdown_unit_id ) : $selected = 'selected'; else : $selected = ''; endif;

																			echo '<option value="'.$update_dropdown_unit_id.'" '.$selected.'>'.$update_dropdown_unit.'</option>';

																		endforeach; ?>

																	</select> <?php

																endif; ?>

																<div class="input-group-append"><input type="submit" class="btn btn-primary d-inline-block" name="<?php echo $edit_update ?>" value="Update" /></div>

															</div>

														</form>

													</div>

												</div>
											</div>
										</div> <?php

										else : ?>

											<button type="button" class="btn btn-light-inactive d-inline-block edit-button-block"><i class="fa-solid fa-pencil"></i></button> <?php

									endif;

									if( $record_type == 'item' ) :
											$update_card_name = $_POST['update-card-name'];
											$update_entry_size = $_POST['update-entry-size'];
											$update_entry_unit = $_POST['update-entry-unit'];
									else :
											$update_card_name = NULL;
											$update_entry_size = NULL;
											$update_entry_unit = NULL;
									endif;
									$update_entry_name = $_POST['update-entry-name'];

									if ( isset( $_POST[$edit_update] ) ) :

										$wpdb->insert( 'custom_record',
											array(
												'entry_date' => $entry_date,
												'entry_name' => stripslashes( $update_entry_name ),
												'record_type' => $record_type_revision,
												'size' => $update_entry_size,
												'unit_id' => $update_entry_unit,
												'card_id' => $update_card_name,
												'active' => 1,
												'parent_id' => $edit_parent_id,
												'user_id' => $user_id,
												'loc_id' => $master_loc
											)
										);

										header ('Location:'.$site_url.'/'.$slug.'?setting='.strtolower( $title ));
										ob_end_flush();

									endif;  // end of edit

									$items_attached = $wpdb->get_results( "SELECT id FROM custom_record WHERE card_id=$edit_parent_id" ); // start of delete | restore

									if( empty( $items_attached ) ) :

										if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="fa-solid fa-trash-can"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1; $btn_style = 'btn-success'; $edit_value = '<i class="fa-solid fa-trash-can-arrow-up"></i>'; endif; ?>

										<form method="post" name="archive" id="<?php echo $edit_archive ?>" class="d-inline-block">
											<button type="submit" class="btn <?php echo $btn_style ?> d-inline-block edit-button-block" name="<?php echo $edit_archive ?>"><?php echo $edit_value ?></button>
										</form> <?php

										if ( isset( $_POST[$edit_archive] ) ) :

											$wpdb->insert( 'custom_record',
												array(
													'entry_date' => $entry_date,
													'entry_name' => stripslashes( $edit_entry_name ),
													'record_type' => $record_type_revision,
													'size' => $edit_entry_size,
													'unit_id' => $edit_entry_unit_id,
													'card_id' => $edit_card_id,
													'active' => $edit_active_update,
													'parent_id' => $edit_parent_id,
													'user_id' => $user_id,
													'loc_id' => $master_loc
												)
											);

											header ('Location:'.$site_url.'/'.$slug.'?setting='.strtolower( $title ));
											ob_end_flush();

										endif;

									endif; // end of delete | restore ?>

								</td>
								<td><?php echo $edit_entry_name; if( !empty($edit_entry_size )) : echo ' ('.(float)$edit_entry_size.' '.$edit_entry_unit.')'; endif; ?></td> <?php
								if( $record_type == 'item' ) : ?> <td><?php echo $edit_card_name; ?></td> <?php endif; ?>
							</tr> <?php

						endforeach; ?>

					</tbody>
				</table>
			</div>

		</section>	<?php

	endif; ?>

</article>

<aside class="col-xl-4 pr-3"> <?php

	if( $record_type != 'location' ) : ?>

		 <?php

	endif; ?>

</aside>

<!-- JQuery Datatables -->
<script>
	$(document).ready(function() {
    	$('#edit').DataTable({
			columnDefs: [
				{ width: "145px", targets: 0 },
				{ orderable: false, targets: 0 }
			],
			dom: '<"top"fp>rt<"bottom"lip><"clear">',
			pageLength: 25,
			order: [[ 1, 'desc' ]],
        	scrollX: true,
			language: {
    		search: "Filter <?php echo $title; ?>",
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
			.html('<i class="fa-solid fa-minus"></i>');
		}).on('click', '.btn-remove', function(e)
		{
			e.preventDefault();
			$(this).parents('.entry:first').remove();
			return false;
		});
	});

</script> <?php

get_footer(); ?>
