<?php ob_start();
session_start();

/* Template Name: Report

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

$report = str_replace( '-', ' ', $_GET['report'] );
$report_url = $_GET['report'];
$report_setup = $wpdb->get_row( "SELECT id, title FROM master_report WHERE title='$report'" );
$report_id = $report_setup->id;
$show_help = get_field('show_help');

$card_filter = str_replace( '-', ' ', $_GET['card'] );
$start_url = $_GET['start'];
$end_url = $_GET['end'];
if( empty( $start_url ) ) : $start = date( 'Y-m-d', strtotime('-1 months')); else : $start = date( 'Y-m-d', strtotime( $start_url )); endif;
if( empty( $end_url ) ) : $end = date( 'Y-m-d'); else : $end = date( 'Y-m-d', strtotime( $end_url )); endif; ?>

<article class="col-12 px-3">
	<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		<header class="header-flexbox">
			<h1 class="h4-style">Report <i class="fal fa-chevron-double-right small"></i> <?php echo $report ?></h1> <?php

			if( !empty( $show_help ) ) : ?> <a href="<?php echo $show_help ?>" class="h4-style"> <i class="far fa-question-circle" aria-hidden="true"></i></a> <?php endif; ?>
		</header>

		<form method="post" name="change_chart" id="chartTypeForm">

			<div class="form-row">
				<div class="form-group col-md-6">
					<label class="control-label" for="filter-card">Card</label> <?php

					$card_dropdowns = $wpdb->get_results( "SELECT parent_id, entry_name FROM custom_record WHERE (record_type='card' OR record_type='card_revision') AND active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) ORDER BY entry_name ASC" ); ?>

					<select class="custom-select" id="filter-card" name="filter-card">
						<option value="" disabled selected>Select Card</option>
						<option value="">All Cards</option><?php

						foreach( $card_dropdowns as $card_dropdown ) :

							$card_dropdown_entry_name = $card_dropdown->entry_name;
							$card_dropdown_value = strtolower( str_replace( '-', ' ', $card_dropdown_entry_name ));

							if( $card_dropdown_value == $card_filter ) : $selected = 'selected'; else : $selected = ''; endif;

							echo '<option value="'.$card_dropdown_value.'" '.$selected.'>'.$card_dropdown_entry_name.'</option>';

						endforeach; ?>

					</select>
				</div>
				<div class="form-group col-md-6">
					<label class="control-label" for="filter-date-range">Date Range<sup class="text-danger">*</sup></label>
					<div class="input-group mb-2">
						<div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
						<input type="text" class="form-control date" name="filter-date-start" id="filter-date-start" aria-describedby="filter-date-start" placeholder="dd-mmm-yyyy" value="<?php echo date( 'd-M-Y', strtotime( $start )) ?>" data-date-end-date="0d" required>
						<input type="text" class="form-control date" name="filter-date-end" id="filter-date-end" aria-describedby="filter-date-end" placeholder="dd-mmm-yyyy" value="<?php echo date( 'd-M-Y', strtotime( $end )) ?>" data-date-end-date="0d" required>
					</div>
					<small class="form-text text-muted">Large date ranges will cause the page to load slowly</small>
				</div>
			</div>
			<button type="submit" class="btn btn-primary" name="filter-report">Filter Report</button>

		</form> <?php

		if( isset( $_POST['filter-report'] ) ) :
			$change_card = str_replace( ' ', '-', strtolower( $_POST['filter-card'] ) );
			$change_date_range_start = date( 'Y-m-d', strtotime( $_POST['filter-date-start'] ) );
			$change_date_range_end = date( 'Y-m-d', strtotime( $_POST['filter-date-end'] ) );

			header ('Location:'.$site_url.'/'.$slug.'/?report='.$report_url.'&card='.$change_card.'&start='.$change_date_range_start.'&end='.$change_date_range_end);
			ob_end_flush();
		endif;  ?>

	</section>

	<section class="dark-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix"> <?php

		$report_headers = $wpdb->get_results( "SELECT xAxis, db_value FROM master_reportmeta INNER JOIN master_report ON master_reportmeta.report_id=master_report.id WHERE title='$report'" );
		if( empty( $card_filter ) ) :
			$card_sql = 'AND data_stock.stock_date BETWEEN \''.$start.'\' AND \''.$end.'\'';
		else :
			$card_sql = 'AND custom_record.entry_name=\''.$card_filter.'\' AND data_stock.stock_date BETWEEN \''.$start.'\' AND \''.$end.'\'';
		endif;

		if( $report_id == 1 ) : // zero balance

			$datatable_empty_msg = 'There are no zero balance items between '.date( 'd-M-Y', strtotime( $start )).' and '.date( 'd-M-Y', strtotime( $end )).'.<br />Please change the date range to see more items.';

			$report_rows = $wpdb->get_results( "SELECT data_stock.stock_date, custom_record.entry_name, item_record.entry_name AS item, item_record.size, tag FROM custom_record INNER JOIN custom_record item_record ON custom_record.parent_id=item_record.card_id INNER JOIN master_tag ON item_record.unit_id=master_tag.id INNER JOIN data_stock ON item_record.parent_id=data_stock.item_id WHERE custom_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) AND item_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) AND data_stock.active=1 AND data_stock.loc_id=$master_loc $card_sql GROUP BY data_stock.item_id HAVING SUM(data_stock.balance) = 0");

		elseif( $report_id == 2 ) : // balance

			$datatable_empty_msg = 'There are no transactions between '.date( 'd-M-Y', strtotime( $start )).' and '.date( 'd-M-Y', strtotime( $end )).'.<br />Please change the date range to see more transactions.';

			$report_rows = $wpdb->get_results( "SELECT custom_record.entry_name, item_record.entry_name AS item, item_record.size, tag, SUM(receive.quantity) AS receive_quantity, SUM(receive.quantity*receive.rate) AS receive_value, SUM(issue.quantity) AS issue_quantity, SUM(issue.quantity*issue.rate) AS issue_value, SUM(receive.quantity)-SUM(issue.quantity) AS balance, SUM(receive.quantity*receive.rate)-SUM(issue.quantity*issue.rate) AS balance_value FROM custom_record INNER JOIN custom_record item_record ON custom_record.parent_id=item_record.card_id INNER JOIN master_tag ON item_record.unit_id=master_tag.id INNER JOIN data_stock ON item_record.parent_id=data_stock.item_id LEFT JOIN data_stock AS receive ON data_stock.id=receive.id AND (receive.record_type='receive' OR receive.record_type='receive_revision') LEFT JOIN data_stock AS issue ON data_stock.id=issue.id AND (issue.record_type='issue' OR issue.record_type='issue_revision') WHERE custom_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) AND item_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) AND data_stock.id IN (SELECT MAX(id) FROM data_stock GROUP BY parent_id) AND data_stock.active=1 AND data_stock.loc_id=$master_loc $card_sql GROUP BY data_stock.item_id");

		elseif( $report_id == 3 ) : // transactions

			$datatable_empty_msg = 'There are no transactions between '.date( 'd-M-Y', strtotime( $start )).' and '.date( 'd-M-Y', strtotime( $end )).'.<br />Please change the date range to see more transactions.';

			$report_rows = $wpdb->get_results( "SELECT data_stock.stock_date, custom_record.entry_name, item_record.entry_name AS item, item_record.size, tag, data_stock.record_type, quantity, quantity*rate AS cost FROM custom_record INNER JOIN custom_record item_record ON custom_record.parent_id=item_record.card_id INNER JOIN master_tag ON item_record.unit_id=master_tag.id INNER JOIN data_stock ON item_record.parent_id=data_stock.item_id WHERE custom_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) AND item_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) AND data_stock.id IN (SELECT MAX(id) FROM data_stock GROUP BY parent_id) AND data_stock.active=1 AND data_stock.loc_id=$master_loc $card_sql");

		elseif( $report_id == 4 ) :

			$datatable_empty_msg = 'There are no transactions between '.date( 'd-M-Y', strtotime( $start )).' and '.date( 'd-M-Y', strtotime( $end )).'.<br />Please change the date range to see more transactions.';

			$report_rows = $wpdb->get_results( "SELECT custom_record.entry_name, item_record.entry_name AS item, item_record.size, tag, custom_location.location, SUM(quantity*rate) AS receive_value FROM custom_record INNER JOIN custom_record item_record ON custom_record.parent_id=item_record.card_id INNER JOIN master_tag ON item_record.unit_id=master_tag.id INNER JOIN data_stock ON item_record.parent_id=data_stock.item_id INNER JOIN custom_location ON data_stock.location=custom_location.parent_id WHERE custom_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) AND item_record.id IN (SELECT MAX(id) FROM custom_record GROUP BY parent_id) AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id) AND data_stock.id IN (SELECT MAX(id) FROM data_stock GROUP BY parent_id) AND (data_stock.record_type='receive' OR data_stock.record_type='receive_revision') AND data_stock.active=1 AND data_stock.loc_id=$master_loc $card_sql GROUP BY custom_record.card_id, data_stock.location");

		endif; ?>

		<div class="table-responsive-xl mb-3">
			<table id="report" class="table table-borderless nowrap" style="width:100%;">
				<thead>
					<tr><?php
						foreach( $report_headers as $report_header ) : ?>
							<th><?php echo $report_header->xAxis; ?></th> <?php
						endforeach; ?>
					</tr>
				</thead>
				<tbody><?php
					foreach( $report_rows as $report_row ) : ?>
						<tr> <?php

							foreach( $report_headers as $report_header ) : ?>

								<td> <?php

									$db_value_lookup = $report_header->db_value;
									$db_value = $report_row->$db_value_lookup;
									$db_value_size = $report_row->size;
									$db_value_unit = $report_row->tag;

									if ( is_numeric( $db_value ) || empty ( $db_value ) ) :?> <div class="text-right"> <?php endif;

									if( $db_value_lookup == 'stock_date' ) :
										echo date_format( date_create( $db_value ), 'd-M-Y' );

									elseif( $db_value_lookup == 'item' && !empty( $db_value_size ) ) :
										echo $db_value.' ('.(float)$db_value_size.' '.$db_value_unit.')';

									elseif( $db_value_lookup == 'record_type' ) :
										if( $db_value == 'receive_revision' ) : echo 'Received'; elseif( $db_value == 'issue_revision' ) : echo 'Issued'; else : echo ucfirst( $db_value ).'d'; endif;

									elseif( $db_value_lookup == 'balance' && $db_value == '' ) :
										echo $report_row->receive_quantity;

									elseif( $db_value_lookup == 'receive_value' || $db_value_lookup == 'issue_value' || $db_value_lookup == 'cost' || ($db_value_lookup == 'balance_value' && $db_value != '' ) ) :
										echo number_format( $db_value,2,'.',',' );

									elseif( $db_value_lookup == 'balance_value' && $db_value == '' ) : // fix for balance value when no items issued
										echo number_format( $report_row->receive_value,2,'.',',' );

									else :
										echo $db_value;

									endif;

									if ( is_numeric( $db_value ) ) :?> </div> <?php endif; ?>

								</td> <?php

							endforeach; ?>
						</tr> <?php

					endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>
</article>


<!-- Datepicker -->
<script>

	$('.date').datepicker({
		format: 'd-M-yyyy',
		autoclose: true
	 });

</script>

<!-- JQuery Datatables -->
<script>
	$(document).ready(function() {
    	$('#report').DataTable({
			dom: '<"top"fB>rt<"bottom"lip><"clear">',
			buttons: [ {
				extend: 'excel',
				text: '<i class="far fa-file-excel"></i> Export',
				title: "<?php echo ucwords( $report ) ?>",
				exportOptions: { columns: 'thead th:not(.no-export)' }
			} ], <?php
			if ( $report == 'transactions' ) : ?>

				columnDefs: [
					{ type: "date", targets: 0 }
				],
				order: [[ 0, 'desc' ]], <?php

			else : ?>

				order: [[ 0, 'asc' ]], <?php

			endif; ?>

			pageLength: 25,
        	scrollX: true,
			language: {
      			emptyTable: "<?php echo $datatable_empty_msg ?>",
    			search: "Filter <?php echo $report ?>",
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

get_footer();
