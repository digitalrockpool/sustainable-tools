<?php ob_start();
session_start();

/* ***

Template Name: Stock

Template Post Type: Page

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/
@copyright	Copyright (c) 2022, Digital Rockpool LTD
@license	GPL-2.0+

*** */


get_header();

global $wpdb;
global $post;

$site_url = get_site_url();
$slug = $post->post_name;

$user_id = get_current_user_id();
$master_loc = $_SESSION['master_loc'];
$entry_date = date( 'Y-m-d H:i:s' );

$start_url = $_GET['start'];
$end_url = $_GET['end'];

if( empty( $end_url ) ) : $end = date( 'Y-m-d' ); else : $end = $end_url; endif;
if( empty( $start_url ) ) : $start = date( 'Y-m-d', strtotime( "$end -364 days" ) ); else : $start = $start_url; endif;

$month_end = date_format( date_create( $end ), 'd-M-Y' );
$month_start = date_format( date_create( $start ), 'd-M-Y' );

$stock_type = $_GET['item'];
$stock_type_revision = $stock_type.'_revision';
$title = ucfirst( $stock_type );
$title_pasttense = ucfirst( $stock_type ).'d';
$show_help = get_field('show_help');

// $quantity_remaining = $_SESSION['quantity_remaining']; ?>

<article class="col-xl-12 px-3">
	<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		<header class="header-flexbox">

			<h1 class="h4-style"><?php echo $title ?> Items</h1> <?php

			if( !empty( $show_help ) ) : ?> <a href="<?php echo $show_help ?>" class="h4-style"> <i class="far fa-question-circle" aria-hidden="true"></i></a> <?php endif; ?>

		</header>

		<form method="post" name="add-stock-item" id="add-stock-item">

			<div class="form-row">
    			<div class="form-group col-md-5">

					<label class="control-label" for="stock-card-name">Card Name<sup class="text-danger">*</sup></label> <?php

					if( $stock_type == 'receive' ) : // all cards

						$card_dropdowns = $wpdb->get_results( "SELECT parent_id, entry_name FROM custom_record WHERE (record_type='card' OR record_type='card_revision') AND active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) ORDER BY entry_name ASC" );

					else : // only cards with stock

						$card_dropdowns = $wpdb->get_results( "SELECT card.parent_id, card.entry_name FROM data_stock INNER JOIN custom_record ON data_stock.item_id=custom_record.parent_id INNER JOIN custom_record card ON custom_record.card_id=card.parent_id WHERE card.active=1 AND card.loc_id=$master_loc AND card.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) GROUP BY card.parent_id ORDER BY card.entry_name ASC" );

					endif; ?>

					<select class="custom-select" id="stock-card-name" name="stock-card-name" required>
						<option value="" disabled selected>Select Card *</option><?php

						foreach( $card_dropdowns as $card_dropdown ) :

							$card_dropdown_parent_id = $card_dropdown->parent_id;
							$card_dropdown_entry_name = $card_dropdown->entry_name;

							echo '<option value="'.$card_dropdown_parent_id.'">'.$card_dropdown_entry_name.'</option>';

						endforeach; ?>

					</select>

				</div>
    			<div class="form-group col-md-5">

					<label class="control-label" for="stock-date">Date<sup class="text-danger">*</sup></label>

					<div class="input-group">
						<input type="text" class="form-control date" name="stock-date" id="stock-date" aria-describedby="stock-date" value="<?php echo date_format( date_create( $entry_date ), 'd-M-Y' ) ?>" data-date-end-date="0d" required>

						<div class="input-group-append"><span class="input-group-text"><i class="far fa-calendar-alt"></i></span></div>
					</div>

				</div>
			</div>

			<div id="repeater-field">
				<div class="entry form-row mb-1">
					<div class="col-10 input-group">
						<select class="custom-select" id="stock-item-name" name="stock-item-name[]" required>
							<option value="" selected disabled>Select Item *</option> <!-- populated by function populate_stock_item_receive || populate_stock_item_issue -->
						</select> <?php

						if( $stock_type == 'receive' ) : ?>

							<select class="custom-select" name="stock-location[]">
								<option value="" selected disabled>Select Location</option> <?php

								$location_dropdowns = $wpdb->get_results( "SELECT parent_id, location FROM custom_location WHERE loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY location ASC" );

								foreach( $location_dropdowns as $location_dropdown ) :

									$location_dropdown_parent_id = $location_dropdown->parent_id;
									$location_dropdown_location = $location_dropdown->location; ?>

									<option value="<?php echo $location_dropdown_parent_id ?>"><?php echo $location_dropdown_location ?></option> <?php
								endforeach; ?>

								<option value="0">Unknown</option>

							</select>
							<input type="number" class="form-control" id="stock-quantity" name="stock-quantity[]" min="1" step="1" placeholder="Quantity *" required>
							<input type="number" class="form-control" id="stock-rate" name="stock-rate[]" min="0" step="0.01" placeholder="Rate *" required>
							<input type="number" class="form-control" id="stock-total" name="stock-total" min="0" step="0.01" placeholder="Total" readonly> <?php

						else : ?>

							<select class="custom-select" id="stock-rate" name="stock-rate[]" required>
								<option value="" selected disabled>Select Receive Date @ Rate *</option> <!-- populated by function populate_stock_item_issue_rate -->
							</select>
							<input type="number" class="form-control" id="stock-quantity" name="stock-quantity[]" placeholder="Quantity *" min="1" step="1" required>
							<input type="hidden" class="form-control" id="stock-remaining" name="stock-remaining[]"> <!-- for balance calculation -->
							<input type="number" class="form-control" id="stock-balance" name="stock-balance[]" value="" min="0" step="1" placeholder="Balance" readonly><?php

						endif; ?>

					</div>

					<div class="col-2">
						<span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
					</div>
				</div>
			</div>

			<div class="form-row">
				<div class="col-2 offset-10 mb-3"><button class="btn btn-primary" type="submit" name="add-stock-item"><?php echo $title ?> Items</button></div>
			</div>
		</form> <?php

		$stock_date = $_POST['stock-date'];
		$stock_item_name_array = $_POST['stock-item-name'];
		$stock_location_array = $_POST['stock-location'];
		$stock_quantity_array = $_POST['stock-quantity'];
		$stock_rate_array = $_POST['stock-rate'];
		$stock_balance_array = $_POST['stock-balance'];

		if( isset( $_POST['add-stock-item'] ) ) :

			foreach( $stock_item_name_array as $index => $stock_item_name_array ) :

				$stock_item_id = $stock_item_name_array;
				$stock_quantity = $stock_quantity_array[$index];

				if( $stock_type == 'receive' ) :

					$stock_location_null = $stock_location_array[$index];
					$stock_rate = $stock_rate_array[$index];
					$stock_balance = $stock_quantity;

					if( empty( $stock_location_null ) ) : $stock_location = NULL; else : $stock_location = $stock_location_null; endif;

				elseif( $stock_type == 'issue' ) :

					$stock_id = $stock_rate_array[$index];

					$issue_item = $wpdb->get_row( "SELECT id, entry_date, record_type, stock_date, item_id, location, quantity, rate, receive_id, active, parent_id FROM data_stock WHERE receive_id=$stock_id AND id IN (SELECT MAX(id) FROM data_stock GROUP BY receive_id)" );

					$issue_id = $issue_item->id;
					$issue_entry_date = $issue_item->entry_date;
					$issue_record_type = $issue_item->record_type;
					$issue_stock_date = $issue_item->stock_date;
					$issue_item_id = $issue_item->item_id;
					$stock_location = $issue_item->location;
					$issue_quantity = $issue_item->quantity;
					$stock_rate = $issue_item->rate;
					$issue_parent_id = $issue_item->parent_id;
					$issue_receive_id = $issue_item->receive_id;
					$issue_active = $issue_item->active;

					$wpdb->update( 'data_stock', // link issues by setting initial receive id as receive_id and sets balance to NULL
						array(
							'entry_date' => $issue_entry_date,
							'record_type' => $issue_record_type,
							'stock_date' => date_format( date_create( $issue_stock_date ), 'Y-m-d' ),
							'item_id' => $issue_item_id,
							'location' => $stock_location,
							'quantity' => $issue_quantity,
							'rate' => $stock_rate,
							'balance' => NULL,
							'receive_id' => $issue_receive_id,
							'active' => $issue_active,
							'parent_id' => $issue_parent_id,
							'user_id' => $user_id,
							'loc_id' => $master_loc
						),
						array(
							'id' => $issue_id
						)
					);

					$stock_balance = $stock_balance_array[$index];
					$stock_receive_id = $issue_parent_id;

				endif;

				$wpdb->insert( 'data_stock',
					array(
						'entry_date' => $entry_date,
						'record_type' => $stock_type,
						'stock_date' => date_format( date_create( $stock_date ), 'Y-m-d' ),
						'item_id' => $stock_item_id,
						'location' => $stock_location,
						'quantity' => $stock_quantity,
						'rate' => $stock_rate,
						'balance' => $stock_balance,
						'receive_id' => 0,
						'active' => 1,
						'parent_id' => 0,
						'user_id' => $user_id,
						'loc_id' => $master_loc
					)
				);

				$parent_id = $wpdb->insert_id;

				if( $stock_type == 'receive' ) : $stock_receive_id = $parent_id; else : $stock_receive_id = $issue_receive_id; endif;

				$wpdb->update( 'data_stock', // update parent_id
					array(
						'entry_date' => $entry_date,
						'record_type' => $stock_type,
						'stock_date' => date_format( date_create( $stock_date ), 'Y-m-d' ),
						'item_id' => $stock_item_id,
						'location' => $stock_location,
						'quantity' => $stock_quantity,
						'rate' => $stock_rate,
						'balance' => $stock_balance,
						'receive_id' => $stock_receive_id,
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

			header ('Location:'.$site_url.'/'.$slug.'?item='.$stock_type);
			ob_end_flush();

		endif; ?>

	</section>

	<section class="dark-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		<header class="header-flexbox">

			<h1 class="h4-style"><?php echo $title_pasttense ?> Items</h1>

			<form method="post" name="change_date_range" id="change-date-range">
				<div class="form-group">
					<div class="input-group mb-2">
						<div class="input-group-prepend"><div class="input-group-text">SELECT DATE RANGE</div></div>
						<input type="text" class="form-control date" name="edit_date_range_start" id="edit_date_range_start" aria-describedby="edit_date_range_start" placeholder="dd-mmm-yyyy" value="<?php echo $month_start ?>" data-date-end-date="0d" required>
						<input type="text" class="form-control date" name="edit_date_range_end" aria-describedby="edit_date_range_end" placeholder="dd-mmm-yyyy" value="<?php echo $month_end ?>" data-date-end-date="0d" required>
						<div class="input-group-append"><button type="submit" class="btn btn-primary" name="change_date_range"><i class="far fa-calendar-alt"></i></button></div>
					</div>
					<small class="form-text text-muted text-right">Large date ranges will cause the page to load slowly</small>
				</div>

			</form> <?php

			if( isset( $_POST['change_date_range'] ) ) :
				$change_date_range_start = date( 'Y-m-d', strtotime( $_POST['edit_date_range_start'] ) );
				$change_date_range_end = date( 'Y-m-d', strtotime( $_POST['edit_date_range_end'] ) );

				header ('Location:'.$site_url.'/'.$slug.'/?item='.$stock_type.'&start='.$change_date_range_start.'&end='.$change_date_range_end);
				ob_end_flush();
			endif; ?>

		</header>  <?php

		$edit_stocks = $wpdb->get_results( "SELECT data_stock.id, data_stock.stock_date, data_stock.record_type, data_stock.item_id, custom_record.entry_name AS item, custom_record.size, tag, card_record.entry_name AS card, custom_location.location, data_stock.location AS location_id, data_stock.quantity, data_stock.rate, data_stock.receive_id, data_stock.balance, data_stock.active, data_stock.parent_id, display_name FROM data_stock INNER JOIN log_users ON data_stock.user_id=log_users.id LEFT JOIN custom_location ON (data_stock.location=custom_location.parent_id) AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) LEFT JOIN custom_record ON (data_stock.item_id=custom_record.parent_id) AND custom_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) LEFT JOIN master_tag ON custom_record.unit_id=master_tag.id LEFT JOIN custom_record card_record ON (custom_record.card_id=card_record.parent_id) AND card_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) WHERE data_stock.id IN (SELECT MAX(id) FROM data_stock GROUP BY parent_id) AND data_stock.loc_id=$master_loc AND (data_stock.record_type='$stock_type' OR data_stock.record_type='$stock_type_revision') AND stock_date BETWEEN '$start' AND '$end' ORDER BY data_stock.active DESC, data_stock.stock_date DESC" );

		if( empty( $edit_stocks) ) :

			echo 'No '.strtolower( $title ).' items has been added.';

		else : ?>

			<div class="table-responsive-xl mb-3">
				<table id="edit" class="table table-borderless nowrap" style="width:100%;">
					<thead>
						<tr>
							<th scope="col" class="no-sort no-export"><div class="edit-button-block d-inline-block">View</div><div class="edit-button-block d-inline-block">Edit</div><div class="edit-button-block d-inline-block">Delete</div></th>
							<th scope="col"><?php echo $title_pasttense ?> Date</th>
							<th scope="col">Card</th>
							<th scope="col">Item</th>
							<th scope="col"><?php if( $stock_type == 'issue' ) : echo 'Receive Date'; else : echo 'Location'; endif; ?></th>
							<th scope="col">Rate</th>
							<th scope="col">Quantity <?php echo $title_pasttense; ?></th>
							<th scope="col"><?php if( $stock_type == 'issue' ) : echo 'Remaining Balance'; else : echo 'Total Cost'; endif; ?></th>
						</tr>
					</thead>

					<tbody> <?php

						foreach ( $edit_stocks as $edit_stock ) :

							$edit_id = $edit_stock->id;
							$edit_stock_date = $edit_stock->stock_date;
							$edit_record_type = $edit_stock->record_type;
							$edit_item_id = $edit_stock->item_id;
							$edit_item_name = $edit_stock->item;
							$edit_item_size = $edit_stock->size;
							$edit_item_unit = $edit_stock->tag;
							$edit_card_name = $edit_stock->card;
							$edit_location = $edit_stock->location;
							$edit_location_id = $edit_stock->location_id;
							$edit_quantity = $edit_stock->quantity;
							$edit_rate = $edit_stock->rate;
							$edit_receive_id = $edit_stock->receive_id;
							$edit_balance = $edit_stock->balance;
							$edit_active = $edit_stock->active;
							$edit_parent_id = $edit_stock->parent_id;
							$edit_update = 'update-'.$edit_id;
							$edit_archive = 'archive-'.$edit_id;

							$edit_receive_dates = $wpdb->get_row( "SELECT stock_date FROM data_stock WHERE id=$edit_receive_id" );
							$edit_receive_date = $edit_receive_dates->stock_date; ?>

							<tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
								<td class="align-top strikeout-buttons"><?php // start of view revisions ?>

									<button type="button" class="btn btn-dark d-inline-block edit-button-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $edit_id ?>"><i class="far fa-eye"></i></button>

									<div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
										<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
											<div class="modal-content">

												<div class="modal-header">
													<h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $edit_card_name.': '.$edit_item_name; if( !empty( $edit_item_size ) ) : echo ' ('.$edit_item_size.' '.$edit_item_unit.')'; endif; ?></h5>
													<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
												</div>

												<div class="modal-body"> <?php

													$revision_stocks = $wpdb->get_results( "SELECT data_stock.id, data_stock.entry_date, stock_date, custom_record.entry_name as item, card_record.entry_name as card, custom_record.size, master_tag.tag, custom_location.location, quantity, rate, data_stock.parent_id, display_name, data_stock.active FROM data_stock INNER JOIN log_users ON data_stock.user_id=log_users.id LEFT JOIN custom_record ON (data_stock.item_id=custom_record.parent_id) AND custom_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) LEFT JOIN custom_record card_record ON (custom_record.card_id=card_record.parent_id) AND card_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) LEFT JOIN master_tag ON custom_record.unit_id=master_tag.id LEFT JOIN custom_location ON (data_stock.location=custom_location.parent_id) AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) WHERE data_stock.parent_id=$edit_parent_id ORDER BY data_stock.id DESC" );

													foreach( $revision_stocks as $revision_stock ) :

														$revision_id = $revision_stock->id;
														$revision_entry_date = date_create( $revision_stock->entry_date );
														$revision_stock_date = date_create( $revision_stock->stock_date );
														$revision_card_name = $revision_stock->card;
														$revision_item_name = $revision_stock->item;
														$revision_item_size = $revision_stock->size;
														$revision_item_unit = $revision_stock->tag;
														$revision_location = $revision_stock->location;
														$revision_quantity = $revision_stock->quantity;
														$revision_rate = $revision_stock->rate;
														$revision_parent_id = $revision_stock->parent_id;
														$revision_active = $revision_stock->active;
														$revision_username = $revision_stock->display_name;

														if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

														echo '<b>'.$title.' Date:</b> '.date_format( $revision_stock_date, "d-M-Y" ).'<br />';
														echo '<b>Card:</b> '.$revision_card_name.'<br />';
														echo '<b>Item:</b> '.$revision_item_name;
															if( !empty( $revision_item_size ) ) : echo ' ('.$revision_item_size.' '.$revision_item_unit.')'; endif;
														echo '<br />';
														echo '<b>Location:</b> '.$revision_location.'<br />';
														echo '<b>Quantity:</b> '.number_format( $revision_quantity,0,'.',',' ).'<br />';
														echo '<b>Rate:</b> '.number_format( $revision_rate,2,'.',',' ).'<br />';
														echo '<b>Total:</b> '.number_format( $revision_quantity*$revision_rate,2,'.',',' ).'<br />';
														echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';

														if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

													endforeach; ?>

												</div>
											</div>
										</div>
									</div> <?php // end of view revisions

									if( !empty( $edit_balance ) ) :

										if( $edit_active == 1 ) :  // start of edit stock ?>

											<button type="button" class="btn btn-light d-inline-block edit-button-block" data-toggle="modal" data-target="#modal-<?php echo $edit_id ?>"><i class="fas fa-pencil"></i></button>

											<div class="modal fade" id="modal-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-<?php echo $edit_id ?>Title" aria-hidden="true">
												<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<h5 class="modal-title" id="modal-<?php echo $edit_id ?>Title"><?php echo $edit_card_name.': '.$edit_item_name; if( !empty( $edit_item_size ) ) : echo ' ('.$edit_item_size.' '.$edit_item_unit.')'; endif; ?></h5>

															<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
														</div>

														<div class="modal-body">
															<p> <?php if( $stock_type == 'issue' ) : echo 'Received on '.date_format( date_create( $edit_receive_date ), 'd-M-Y' ).' at rate '.$edit_rate; endif; ?></p>

															<form method="post" name="<?php echo $edit_update ?>">

																<div class="form-row">
																	<div class="form-group <?php if( $stock_type == 'receive' ) : echo 'col-6'; else : echo 'col-4'; endif; ?>">
																		<label class="control-label" for="update-stock-date">Date<sup class="text-danger">*</sup></label>

																		<div class="input-group">
																			<input type="text" class="form-control date" name="update-stock-date" id="update-stock-date" aria-describedby="stock-item-date" value="<?php echo date_format( date_create( $edit_stock_date ), 'd-M-Y' ) ?>" data-date-end-date="0d" required>

																			<div class="input-group-append"><span class="input-group-text"><i class="far fa-calendar-alt"></i></span></div>
																		</div>
																	</div> <?php

																	if( $stock_type == 'receive' ) : ?>

																		<div class="form-group col-6">
																			<label class="control-label" for="update-location">Location<sup class="text-danger">*</sup></label>

																			<select class="form-control" id="update-location" name="update-location" required>
																				<option value="0">Unknown</option> <?php

																				$location_dropdowns = $wpdb->get_results( "SELECT parent_id, location FROM custom_location WHERE loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) ORDER BY location ASC" );

																				foreach( $location_dropdowns as $location_dropdown ) :

																					$location_dropdown_parent_id = $location_dropdown->parent_id;
																					$location_dropdown_location = $location_dropdown->location;

																					if( $edit_location_id == $location_dropdown_parent_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

																					<option value="<?php echo $location_dropdown_parent_id ?>" <?php echo $selected ?>><?php echo $location_dropdown_location ?></option> <?php
																				endforeach; ?>
																			</select>
																		</div>
																		</div> <!-- end of form row -->

																		<div class="form-row">
																			<div class="col-md-4 form-group">
																				<label for="update-rate">Rate</label>
																				<input type="number" class="form-control" id="update-rate" name="update-rate" step="0.01" value="<?php echo $edit_rate ?>" required>
																			</div>
																			<div class="col-md-4 form-group">
																				<label for="update-quantity">Quantity</label>
																				<input type="number" class="form-control" id="update-quantity" name="update-quantity" step="1" value="<?php echo $edit_quantity ?>" required>
																			</div>
																			<div class="col-md-4 form-group">
																				<label for="update-total">Total</label>
																				<input type="number" class="form-control" id="update-total" name="update-total" value="<?php echo $edit_quantity*$edit_rate ?>" readonly>
																			</div>
																		</div> <?php

																	else : ?>

																		<div class="col-md-4 form-group">
																				<label for="update-quantity">Quantity<sup class="text-danger">*</sup></label>
																				<input type="number" class="form-control" id="update-quantity" name="update-quantity" step="1" value="<?php echo $edit_quantity ?>" required>
																			</div>
																			<input type="hidden" id="update-remaining" name="update-remaining" value="<?php echo $archive_balance ?>">  <!-- for balance calculation -->
																			<div class="col-md-4 form-group">
																				<label for="update-balance">Balance</label>
																				<input type="number" class="form-control" id="update-balance" name="update-balance" value="<?php echo $edit_balance ?>" readonly>
																			</div>
																		</div> <?php

																	endif; ?>

																<div class="form-row">
																	<div class="col-12"><button class="btn btn-primary" type="submit" name="<?php echo $edit_update ?>">Update <?php echo $title_pasttense ?> Item</button></div>
																</div>
															</form>

														</div>

													</div>
												</div>
											</div> <?php

										else : ?>

											<button type="button" class="btn btn-light-inactive d-inline-block edit-button-block"><i class="fas fa-pencil"></i></button> <?php

										endif; // end of edit stock

										if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="fas fa-trash-alt"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1; $btn_style = 'btn-success'; $edit_value = '<i class="fas fa-trash-restore-alt"></i>'; endif; // start of delete | restore stock

										if( $edit_active == 1 && $stock_type == 'issue' ) : $archive_balance = $edit_quantity+$edit_balance; elseif( $edit_active == 0 && $stock_type == 'issue' ) : $archive_balance = $edit_balance-$edit_quantity; else : $archive_balance = $edit_quantity; endif; ?>

										<form method="post" name="archive" id="<?php echo $edit_archive ?>" class="d-inline-block">
											<button type="submit" class="btn <?php echo $btn_style ?> d-inline-block edit-button-block" name="<?php echo $edit_archive ?>"><?php echo $edit_value ?></button>
										</form> <?php

										if ( isset( $_POST[$edit_archive] ) ) :

											$wpdb->insert( 'data_stock',
												array(
													'entry_date' => $entry_date,
													'record_type' => $stock_type.'_revision',
													'stock_date' => date_format( date_create( $edit_stock_date ), 'Y-m-d' ),
													'item_id' => $edit_item_id,
													'location' => $edit_location_id,
													'quantity' => $edit_quantity,
													'rate' => $edit_rate,
													'balance' => $archive_balance,
													'receive_id' => $edit_receive_id,
													'active' => $edit_active_update,
													'parent_id' => $edit_parent_id,
													'user_id' => $user_id,
													'loc_id' => $master_loc
												)
											);

											$wpdb->update( 'data_stock',
												array(
													'entry_date' => $entry_date,
													'record_type' => $edit_record_type,
													'stock_date' => date_format( date_create( $edit_stock_date ), 'Y-m-d' ),
													'item_id' => $edit_item_id,
													'location' => $edit_location_id,
													'quantity' => $edit_quantity,
													'rate' => $edit_rate,
													'balance' => NULL,
													'receive_id' => $edit_receive_id,
													'active' => $edit_active,
													'parent_id' => $edit_parent_id,
													'user_id' => $user_id,
													'loc_id' => $master_loc
												),
												array(
													'id' => $edit_id
												)
											);

											header ('Location:'.$site_url.'/'.$slug.'?item='.$stock_type );
											ob_end_flush();

										endif; // end of delete | restore stock

										$update_stock_date = $_POST['update-stock-date'];
										$update_quantity = $_POST['update-quantity'];

										if( $stock_type == 'issue' ) :

											$update_location = $edit_location_id;
											$update_rate = $edit_rate;
											$update_balance = $_POST['update-balance'];

										else :

											$update_location = $_POST['update-location'];
											$update_rate = $_POST['update-rate'];
											$update_balance = $update_quantity;

										endif;

										if ( isset( $_POST[$edit_update] ) ) :

											$wpdb->insert( 'data_stock',
												array(
													'entry_date' => $entry_date,
													'record_type' => $stock_type.'_revision',
													'stock_date' => date_format( date_create( $update_stock_date ), 'Y-m-d' ),
													'item_id' => $edit_item_id,
													'location' => $update_location,
													'quantity' => $update_quantity,
													'rate' => $update_rate,
													'balance' => $update_balance,
													'receive_id' => $edit_receive_id,
													'active' => 1,
													'parent_id' => $edit_parent_id,
													'user_id' => $user_id,
													'loc_id' => $master_loc
												)
											);

											$wpdb->update( 'data_stock',
												array(
													'entry_date' => $entry_date,
													'record_type' => $edit_record_type,
													'stock_date' => date_format( date_create( $edit_stock_date ), 'Y-m-d' ),
													'item_id' => $edit_item_id,
													'location' => $edit_location_id,
													'quantity' => $edit_quantity,
													'rate' => $edit_rate,
													'balance' => NULL,
													'receive_id' => $edit_receive_id,
													'active' => 1,
													'parent_id' => $edit_parent_id,
													'user_id' => $user_id,
													'loc_id' => $master_loc
												),
												array(
													'id' => $edit_id
												)
											);

											header ('Location:'.$site_url.'/'.$slug.'?item='.$stock_type );
											ob_end_flush();

										endif;
									endif; ?>

								</td>
								<td><?php echo date_format( date_create( $edit_stock_date ), 'd-M-Y' ); ?></td>
								<td><?php echo $edit_card_name; ?></td>
								<td><?php echo $edit_item_name; if( !empty( $edit_item_size )) : echo ' ('.(float)$edit_item_size.' '.$edit_item_unit.')'; endif; ?></td>
								<td><?php if( $stock_type == 'issue' ) : echo date_format( date_create( $edit_receive_date ), 'd-M-Y' ); else : echo $edit_location; endif; ?></td>
								<td class="text-right"><?php echo number_format( $edit_rate,2,'.',',' ); ?></td>
								<td class="text-right"><?php echo number_format( $edit_quantity,0,'.',',' ); ?></td>
								<td class="text-right"><?php if( $stock_type == 'issue' && !empty( $edit_balance ) ) : echo number_format( $edit_balance,0,'.',',' ); elseif( $stock_type == 'issue' && empty( $edit_balance ) ) : echo ''; else : echo number_format( $edit_quantity*$edit_rate,2,'.',',' ); endif; ?></td>
							</tr> <?php

						endforeach; ?>

					</tbody>
				</table>
			</div> <?php

		endif; ?>

	</section>
</article>

<!-- Date Picker -->
<script>
	$('.date').datepicker({
		autoclose: true,
		format: 'd-M-yyyy',
		maxDate: '0'
 	});
</script>

<!-- Card / Item Dynamic Dropdown -->
<script>
	$(document).ready(function(){

		jQuery('#stock-card-name').change(function(){
			var itemPOP=jQuery('#stock-card-name').val();

			jQuery("#stock-item-name").empty();
			jQuery.ajax({
				url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
				type:'POST',
				data:'action=populate_stock_item_<?php echo $stock_type ?>&cardID=' + itemPOP,

				success:function(results) {
					jQuery("#stock-item-name").append(results);
				}
			});
		});

	});
</script>

<!-- Issue Item / Rate Dynamic Dropdown -->
<script>
	$(document).on('change', '#stock-item-name', function(){
		var itemPOP = $(this).val();
		var dropDown = $(this).parent().parent().find("#stock-rate");
		dropDown.empty();
		$.ajax({
			url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
			type:'GET',
			data:'action=populate_stock_item_issue_rate&itemID=' + itemPOP,
			success:function(results) {
				dropDown.append(results);
			}
		});
	});
</script>

<!-- Issue Item - Balance Dynamic Value -->
<script>
	$(document).on('change', '#stock-rate', function(){
		var balancePOP = $(this).val();
		var dropDown = $(this).parent().parent().find("#stock-remaining, #stock-balance");
		dropDown.empty();
		$.ajax({
			url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
			type:'GET',
			data:'action=populate_stock_balance&rowID=' + balancePOP,
			success:function(results) {
				dropDown.val(results);
			}
		});
	});
</script>

<!-- Recieve Item - Multiple Quantity and Rate -->
<script>
	function updateTotalValue(){
		const self = $(this);
		setTimeout(function(){
			const secondField = self.attr('id') == 'stock-rate' ? self.siblings('#stock-quantity') : self.siblings('#stock-rate');
			const multipliedVal = secondField.val() * self.val()
			self.siblings('#stock-total').val(multipliedVal);
		},10);
	};

	$(document).on('change keydown', '#stock-rate, #stock-quantity', updateTotalValue);
</script>

<!-- Recieve Item - Multiple Quantity and Rate for Modal Box THIS IS NOT WORKING-->
<script>
	$(document).on('change keydown', '#update-rate, #update-quantity', function(){
		$('#update-quantity').keyup(calculate);
		$('#update-rate').keyup(calculate);
	});

	function calculate(e){
    	$('#update-total').val($('#update-quantity').val() * $('#update-rate').val());
	}
</script>

<!-- Minus Quantity and Balance -->
<script>
	function updateTotalValue(){
		const self = $(this);
		setTimeout(function(){
			const secondField = self.attr('id') == 'stock-quantity' ? self.siblings('#stock-remaining') : self.siblings('#stock-quantity');
			const multipliedVal = secondField.val() - self.val()
			self.siblings('#stock-balance').val(multipliedVal);
		},10);
	};

	$(document).on('change keydown', '#stock-quantity, #stock-remaining', updateTotalValue);
</script>

<!-- Minus Quantity and Rate for Modal Box-->
<script>
	$(document).on('change keydown', '#update-quantity', function(){
		$('#update-quantity').keyup(calculate);
		$('#update-remaining').keyup(calculate);
	});

	function calculate(e){
    	$('#update-balance').val($('#update-remaining').val() - $('#update-quantity').val());
	}
</script>


<!-- Add / Minus Buttons for Repeater Fields -->
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
		}).on('click', '.btn-remove', function(e)
		{
			e.preventDefault();
			$(this).parents('.entry:first').remove();
			return false;
		});
	});

</script>

<!-- JQuery Datatables -->
<script>
	$(document).ready(function() {
    	$('#edit').DataTable({
			columnDefs: [
				{ width: "145px", targets: 0 },
				{ orderable: false, targets: 0 },
				{ type: "date", targets: 1 }
			],
			dom: '<"top"fB>rt<"bottom"lip><"clear">',
			buttons: [ {
				extend: 'excel',
				text: '<i class="far fa-file-excel"></i> Export',
				title: "<?php echo $title_pasttense; ?> Items",
				exportOptions: { columns: 'thead th:not(.no-export)' }
			} ],
			pageLength: 25,
			order: [[ 1, 'desc' ]],
        	scrollX: true,
			language: {
    			search: "Filter <?php echo $title_pasttense; ?> Items",
				paginate: {
      				first:    '<i class="fad fa-fast-backward"></i>',
					previous: '<i class="fad fa-step-backward"></i>',
					next:     '<i class="fad fa-step-forward"></i>',
					last:     '<i class="fad fa-fast-forward"></i>'
    			}
			}
		});
	});
</script> <?php

get_footer(); ?>
