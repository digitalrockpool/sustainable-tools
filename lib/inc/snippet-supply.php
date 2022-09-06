<?php ob_start();

/* Includes: SUPPLY SNIPPETS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2019, Digital Rockpool LTD
@license	GPL-2.0+ */




function supply_chain_form( $edit_supply, $latest_start, $latest_end, $edit_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_source_id, $edit_amount, $edit_tax, $edit_note, $edit_parent_id ) {
	
	global $wpdb;
	global $post;
	
	$site_url = get_site_url();
	$slug = $post->post_name;
	
	$add_url = $_GET['add'];
	$edit_url = $_GET['edit'];
	
	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	$measure_toggle = $_SESSION['measure_toggle'];
	$tag_toggle = $_SESSION['tag_toggle'];
	
	$entry_date = date( 'Y-m-d H:i:s' ); 

	if( empty( $edit_supply ) ) : $update_supply = 'edit_supply'; else : $update_supply = $edit_supply; endif; ?>
	
	<form method="post" name="edit" id="<?php echo $update_supply ?>" class="needs-validation" novalidate>
		<div class="form-row"> <?php
									
			if( $measure_toggle == 86 ) : // custom measures
	
				custom_measure_dropdown( $edit_measure );

			elseif( $measure_toggle == 85 ) : // yearly measure 
														
				reporting_year_start_date( $edit_measure_date_formatted );
	
			else : ?>
														
				<div class="col-md-4 mb-3">
					<label class="control-label" for="edit-measure-date">Date of Purchase<sup class="text-danger">*</sup></label>
					<div class="input-group mb-2">
						<div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
						<input type="text" class="form-control date" id="edit-measure-date" name="edit-measure-date" aria-describedby="editMeasureDate" placeholder="dd-mmm-yyyy" value="<?php if( empty( $edit_url ) ) : echo date( 'd-M-Y', strtotime( '-1 day' ) ); else : echo $edit_measure_date_formatted; endif; ?>" data-date-end-date="0d" required>
						<div class="invalid-feedback">Please select a date</div>
					</div>
				</div>
														
				<div class="col-md-6 mb-3 d-flex align-items-end"> <?php 
															
					if( $measure_toggle == 84 || $measure_toggle == 83 ) : // monthly or weekly measures ?>
						<small>If this entry is for a period of time it will be added to the <?php if( $measure_toggle == 84 ) : echo 'month'; elseif( $measure_toggle == 83 ) : echo 'week'; endif; ?> of the selected date.</small> <?php
					endif; ?>
																
				</div> <?php
															
			endif; ?>
		</div>
													
		<div id="repeater-field">
			<div class="entry form-row mb-1"> <?php
									
				$source_dropdowns = $wpdb->get_results( "SELECT parent_id, location FROM custom_location WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY location ASC" ); ?>

				<div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-5<?php endif; ?>">
					<?php if( empty( $add_url ) ) : ?><label for="edit-source">Supply Source<sup class="text-danger">*</sup></label><?php endif; ?>
					<select class="form-control" id="edit-source" name="edit-source[]" required>
						<?php if( empty( $edit_url ) ) : ?><option value="">Select Source *</option><?php endif; ?>
						<option value="0" <?php if( $edit_source_id == 0 && empty( $add_url ) ) : echo 'selected'; else : echo ''; endif; ?>>Unknown Location</option> <?php

							foreach ($source_dropdowns as $source_dropdown ) :

								$source_parent_id = $source_dropdown->parent_id;
								$source = $source_dropdown->location;

								if( $edit_source_id == $source_parent_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

								<option value="<?php echo $source_parent_id ?>" <?php echo $selected ?>><?php echo $source ?></option> <?php

							endforeach; ?>

					</select>
					<div class="invalid-feedback">Please select the source</div>
				</div>

				<div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-3<?php endif; ?>">
					<?php if( empty( $add_url ) ) : ?><label for="edit-amount">Amount<sup class="text-danger">*</sup></label><?php endif; ?>
					<input type="number" class="form-control" id="edit-amount" name="edit-amount[]" min="1" step="0.01" aria-describedby="editAmount" placeholder="Amount *" value="<?php echo $edit_amount ?>" min="1" step="0.01" required>
					<div class="invalid-feedback">Please enter a number greater than 0.01</div>
				</div>

				<div class="<?php if( empty( $add_url ) ) : ?>col-md-4<?php else : ?>col-md-3<?php endif; ?>">
					<?php if( empty( $add_url ) ) : ?><label for="edit-tax">Tax</label><?php endif; ?>
					<input type="number" class="form-control" id="edit-tax" name="edit-tax[]" aria-describedby="editTax" placeholder="Tax" value="<?php echo $edit_tax ?>" min="1" step="0.01">
					<div class="invalid-feedback">Please enter a number greater than 0.01</div>
				</div> <?php 

				if( empty( $edit_url ) ) : ?><div class="col-1"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></div> <?php endif; ?>

			</div>
		</div> <?php
		
		if( $tag_toggle == 1 ) : ?>
													
			<h5 class="border-top pt-3 mt-3">Tags</h5>

			<div class="form-row">

				<div class="col-12">
					
					<select class="selectpicker form-control" name="edit-tag[]" multiple title="Select Tags" multiple data-live-search="true"> <?php
						$tag_dropdowns = $wpdb->get_results( "SELECT parent_id, tag FROM custom_tag WHERE loc_id=$master_loc AND cat_id=22 AND tag IS NOT NULL AND active=1 AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag ASC" );

						foreach ($tag_dropdowns as $tag_dropdown ) :

							$dropdown_parent_id = $tag_dropdown->parent_id;
							$dropdown_tag = $tag_dropdown->tag;
	
							$edit_tag_id = $wpdb->get_results( "SELECT tag_id FROM data_tag WHERE data_id=$edit_id AND mod_id=5", ARRAY_N );
							$data_array = array_map( function ($arr) {return $arr[0];}, $edit_tag_id );
							
							if( in_array($dropdown_parent_id, $data_array ) ) : $selected = 'selected'; else : $selected = ''; endif; ?>

							<option value="<?php echo $dropdown_parent_id ?>" <?php echo $selected ?>><?php echo $dropdown_tag ?></option> <?php

						endforeach; ?>
					</select>
				</div>

			</div> <?php
	
		endif; ?>
		
		<h5 class="border-top pt-3 mt-3">Notes</h5>
													
		<div class="form-row">
				
			<div class="col-12 mb-3">
				<label for="edit-note">Please enter any notes for this entry</label>
    			<textarea class="form-control" name="edit-note" id="edit-note" aria-describedby="editNote" placeholder="Notes"><?php echo $edit_note ?></textarea>
			</div>
				
		</div>
															
		<div class="form-row">
			<div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="<?php echo $update_supply ?>"><?php if( empty( $add_url ) ) : echo 'Update'; else : echo 'Add'; endif; echo ' '.str_replace( '-', ' ', $add_url ); ?></button></div>
		</div>
	</form> <?php
											 
	if ( isset( $_POST[$update_supply] ) ) :
	
		$update_source_array = $_POST['edit-source'];
		$update_amount_array = $_POST['edit-amount'];
		$update_tax_array = $_POST['edit-tax'];

		foreach( $update_source_array as $index => $update_source_array ) :
	
			$update_measure_null = $_POST['edit-measure'];
			$update_measure_date_null = $_POST['edit-measure-date'];
			$update_source = $update_source_array;
			$update_amount = $update_amount_array[$index];
			$update_tax_null = $update_tax_array[$index];
			$update_tags = $_POST['edit-tag'];
			$update_note_null = $_POST['edit-note'];

			if( empty( $add_url ) ) : $record_type = 'entry_revision'; else : $record_type = 'entry'; endif;
			if( empty( $update_measure_null ) ) : $update_measure = NULL; else : $update_measure = $update_measure_null; endif;
			if( empty( $update_measure_date_null ) ) : $update_measure_date = NULL; else : $update_measure_date = date_format( date_create( $update_measure_date_null ), 'Y-m-d' ); endif;
			if( empty( $update_tax_null ) ) : $update_tax = NULL; else : $update_tax = $update_tax_null; endif;
			if( empty( $update_note_null ) ) : $update_note = NULL; else : $update_note = $update_note_null; endif;
			if( empty( $edit_parent_id ) ) : $update_parent_id = 0; else : $update_parent_id = $edit_parent_id; endif;

			$wpdb->insert( 'data_supply',
				array(
					'entry_date' => $entry_date,
					'record_type' => $record_type,
					'measure' => $update_measure,
					'measure_date' => $update_measure_date,
					'amount' => $update_amount,
					'tax' => $update_tax,
					'location' => $update_source,
					'note' => $update_note,
					'active' => 1,
					'parent_id' => $update_parent_id,
					'user_id' => $user_id,
					'loc_id' => $master_loc
				)
			);

			$last_id = $wpdb->insert_id;

			if( empty( $edit_parent_id ) ) :

				$wpdb->update( 'data_supply',
					array(
						'parent_id' => $last_id,
					),
					array(
						'id' => $last_id
					)
				);

			endif;

			if( !empty( $update_tags ) ) :

				foreach( $update_tags as $update_tag ) :

					$wpdb->insert( 'data_tag',
						array(
							'data_id' => $last_id,
							'tag_id' => $update_tag,
							'mod_id' => 5
						)
					);

				endforeach;

			endif;
	
		endforeach;

		if( empty( $add_url ) ) : $query_string = 'edit='.$edit_url.'&start='.$latest_start.'&end='.$latest_end; else : $query_string = 'add='.$add_url; endif;

		header( 'Location:'.$site_url.'/'.$slug.'/?'.$query_string );
		ob_end_flush();

	endif;
}



function supply_chain_edit( $edit, $latest_start, $latest_end, $title ) {
	
	global $wpdb;
	global $post;
	
	$site_url = get_site_url();
	$slug = $post->post_name;
	
	$user_id = get_current_user_id();
	$master_loc = $_SESSION['master_loc'];
	
	$measure_toggle = $_SESSION['measure_toggle'];
	$tag_toggle = $_SESSION['tag_toggle'];
	
	$fy_day = $_SESSION['fy_day'];
	$fy_month  = $_SESSION['fy_month'];
	
	$edit_url = $_GET['edit'];
	$start = $_GET['start'];
	$end = $_GET['end'];
	
	$dateObj   = DateTime::createFromFormat('!m', $fy_month);
	$month_name = $dateObj->format('F');
	$entry_date = date( 'Y-m-d H:i:s' );
	
	$latest_measure_date = $wpdb->get_row( "SELECT measure_date FROM data_supply INNER JOIN relation_user ON data_supply.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND data_supply.id IN (SELECT MAX(id) FROM data_supply GROUP BY parent_id) ORDER BY measure_date DESC" );
	
	$latest_end = $latest_measure_date->measure_date;
	$latest_start = date( 'Y-m-d', strtotime( "$end -364 days" ) );
	
	$edit_rows = $wpdb->get_results( "SELECT data_supply.id, measure, tag AS measure_name, measure_date, measure_start, measure_end, amount, tax, data_supply.location AS location_id, custom_location.location, data_supply.note, data_supply.parent_id, data_supply.active, loc_name FROM data_supply LEFT JOIN data_measure ON (data_supply.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN custom_location ON (data_supply.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) INNER JOIN profile_location ON (data_supply.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) RIGHT JOIN relation_user ON data_supply.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND data_supply.id IN (SELECT MAX(id) FROM data_supply GROUP BY parent_id) AND measure_date BETWEEN '$start' AND '$end'" );

	if( empty( $edit_rows) ) :

		echo 'No '.strtolower( $title ).' data has been added.';

	else : ?>
			
		<div class="table-responsive-xl mb-3">
			<table id="edit" class="table table-borderless nowrap" style="width:100%;"> 
				<thead>
					<tr>
						<th scope="col" class="no-sort">View | Delete | Edit</th> <?php
						if( $measure_toggle == 86 ) : ?>
							<th scope="col">Date Range</th>
							<th scope="col">Measure Name</th> <?php 
						else : ?>
							<th scope="col">Date of Purchase</th> <?php
						endif; ?>
						<th scope="col">Supply Source</th>
						<th scope="col">Amount</th>
						<th scope="col">Tax</th> <?php
						if( $tag_toggle == 1 ) : ?> <th scope="col">Tags</th> <?php endif; ?>
					</tr>
				</thead>

				<tbody> <?php

					foreach ( $edit_rows as $edit_row ) :
								
						$edit_id = $edit_row->id;
						$edit_measure = $edit_row->measure;
						$edit_measure_name = $edit_row->measure_name;
						$edit_measure_date = $edit_row->measure_date;
						$edit_measure_date_formatted = date_format( date_create( $edit_measure_date ), 'd-M-Y' );
						$edit_measure_start = $edit_row->measure_start;
						$edit_measure_start_formatted = date_format( date_create( $edit_measure_start ), 'd-M-Y' );
						$edit_measure_end = $edit_row->measure_end;
						$edit_measure_end_formatted = date_format( date_create( $edit_measure_end ), 'd-M-Y' );
						$edit_amount = $edit_row->amount;
						$edit_tax = $edit_row->tax;
						$edit_source_id = $edit_row->location_id;
						$edit_source = $edit_row->location;
						$edit_note = $edit_row->note;
						$edit_parent_id = $edit_row->parent_id;
						$edit_active = $edit_row->active;
						$edit_supply = 'edit-'.$edit_id;
						$archive_supply = 'archive-'.$edit_id; 
					
						$data_tags = $wpdb->get_results( "SELECT data_tag.tag_id, tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$edit_id AND mod_id=5 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" ); ?>

						<tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
							<td class="align-top strikeout-buttons">
										
								<button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $edit_id ?>"><i class="far fa-eye"></i></button>

								<div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
									<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
										<div class="modal-content">

											<div class="modal-header">
												<h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $title ?></h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
											</div>

											<div class="modal-body"> <?php

												$revision_rows = $wpdb->get_results( "SELECT data_supply.id, data_supply.entry_date, measure, tag AS measure_name, measure_date, measure_start, measure_end, amount, tax, custom_location.location, data_supply.note, data_supply.parent_id, data_supply.active, loc_name, display_name FROM data_supply LEFT JOIN data_measure ON (data_supply.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN profile_location ON (data_supply.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN custom_location ON (data_supply.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) INNER JOIN yard_users ON data_supply.user_id=yard_users.id INNER JOIN relation_user ON data_supply.loc_id=relation_user.loc_id WHERE data_supply.parent_id=$edit_parent_id AND relation_user.user_id=$user_id ORDER BY data_supply.id DESC" );

												foreach( $revision_rows as $revision_row ) :
															
													$revision_id = $revision_row->id;
													$revision_entry_date = date_create( $revision_row->entry_date );
													$revision_measure = $revision_row->measure;
													$revision_measure_name = $revision_row->measure_name;
													$revision_measure_date = $revision_row->measure_date;
													$revision_measure_date_formatted = date_format( date_create( $revision_measure_date ), 'd-M-Y' );
													$revision_measure_start = $revision_row->measure_start;
													$revision_measure_start_formatted = date_format( date_create( $revision_measure_start ), 'd-M-Y' );
													$revision_measure_end = $revision_row->measure_end;
													$revision_measure_end_formatted = date_format( date_create( $revision_measure_end ), 'd-M-Y' );
													$revision_amount = $revision_row->amount;
													$revision_tax = $revision_row->tax;
													$revision_source = $revision_row->location;
													$revision_note = $revision_row->note;
													$revision_parent_id = $revision_row->parent_id;
													$revision_active = $revision_row->active;
													$revision_username = $revision_row->display_name;
												
													if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;
	
													if( $measure_toggle == 86 ) : echo '<b>Measure Name:</b> '.$revision_measure_name.'<br />'; endif;
													echo '<b>Date of Purchase:</b> ';
													if( empty( $revision_measure_date ) ) : echo $revision_measure_start_formatted.' to '.$revision_measure_end_formatted; else : echo $revision_measure_date_formatted; endif;
													echo '<br />';
													echo '<b>Supply Source:</b> '.$revision_source.'<br />';
													echo '<b>Amount:</b> '.$revision_amount.'<br />';
													echo '<b>Tax:</b> '.$revision_tax.'<br />';
	
													if( $tag_toggle == 1 ) : 
														echo '<b>Tags:</b> ';
	
														$revision_tags = $wpdb->get_results( "SELECT tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$revision_id AND mod_id=5 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag" );
														
														$trim = '';
														foreach( $revision_tags as $revision_tag ) :
															$trim .= $revision_tag->tag.', ';
														endforeach;
	
														echo rtrim($trim, ', ').'<br />'; 
	
													endif;
	
													echo '<b>Notes:</b> '.$revision_note.'<br />';
													echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';
													echo '<b>Entry ID:</b> '.$revision_id.'<br />';
												
													if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

												endforeach; ?>
														
											</div>

										</div>
									</div>
								</div> <?php
	
								if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="far fa-trash-alt"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1;  $btn_style = 'btn-success'; $edit_value = '<i class="far fa-trash-restore-alt"></i>'; endif; ?>
										
								<form method="post" name="archive" id="<?php echo $archive_supply ?>" class="d-inline-block">
									<button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $archive_supply ?>"><?php echo $edit_value ?></button>
								</form> <?php

								if ( isset( $_POST[$archive_supply] ) ) :

									$wpdb->insert( 'data_supply',
										array(
											'entry_date' => $entry_date,
											'record_type' => 'entry_revision',
											'measure' => $edit_measure,
											'measure_date' => $edit_measure_date,
											'amount' => $edit_amount,
											'tax' => $edit_tax,
											'location' => $edit_source_id,
											'note' => $edit_note,
											'active' => $edit_active_update,
											'parent_id' => $edit_parent_id,
											'user_id' => $user_id,
											'loc_id' => $master_loc
										)
									);
	
									$last_id = $wpdb->insert_id;

									if( !empty( $data_tags ) ) :

										foreach( $data_tags as $data_tag ) :
					
											$data_tag_id = $data_tag->tag_id;

											$wpdb->insert( 'data_tag',
												array(
													'data_id' => $last_id,
													'tag_id' => $data_tag_id,
													'mod_id' => 5
												)
											);

										endforeach;

									endif;

									header( 'Location:'.$site_url.'/'.$slug.'/?edit='.$edit_url.'&start='.$latest_start.'&end='.$latest_end );
									ob_end_flush();	

								endif;
										
								if( $edit_active == 1 ) : ?> 
									
									<button type="button" class="btn btn-light d-inline-block" data-toggle="modal" data-target="#modalEdit-<?php echo $edit_id ?>"><i class="fas fa-pencil"></i></button><?php
	
								endif; ?>

								<div class="modal fade" id="modalEdit-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalEdit-<?php echo $edit_id ?>Title" aria-hidden="true">
									<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
										<div class="modal-content text-left">

											<div class="modal-header">
												<h5 class="modal-title" id="modalEdit-<?php echo $edit_id ?>Title">Edit <?php echo $title ?></h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
											</div>

											<div class="modal-body"> <?php
			
												supply_chain_form( $edit_supply, $latest_start, $latest_end, $edit_id, $edit_measure, $edit_measure_name, $edit_measure_date_formatted, $edit_source_id, $edit_amount, $edit_tax, $edit_note, $edit_parent_id ); ?>
														
											</div>

										</div>
									</div>
								</div> 
										
							</td>
							<td><span class="d-none"><?php echo $edit_measure_date.$edit_measure_start ?></span><?php if( empty( $edit_measure_date ) ) : echo $edit_measure_start_formatted.' to '.$edit_measure_end_formatted; else : echo $edit_measure_date_formatted; endif; ?></td><?php
							if( $measure_toggle == 86 ) : ?><td><?php echo $edit_measure_name ?></td> <?php endif; ?>
							<td><?php echo $edit_source ?></td>
							<td><?php echo number_format( $edit_amount, 2 ) ?></td>
							<td><?php if( !empty( $edit_tax ) ) : echo number_format( $edit_tax, 2); endif; ?></td> <?php
	
							if( $tag_toggle == 1 ) : ?>
							<td><?php
								foreach( $data_tags as $data_tag ) : ?>
									<div class="btn btn-info d-inline-block mr-1 float-none"><?php echo $data_tag->tag ?></div> <?php
								endforeach; ?>
							</td> <?php
							endif; ?>
							
						</tr> <?php

					endforeach; ?>

				</tbody> 
			</table>
		</div> <?php

	endif;
	
}