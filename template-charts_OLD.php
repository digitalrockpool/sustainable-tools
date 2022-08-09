<?php ob_start();

/* Template Name: Charts

Template Post Type: Page

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */

$licence = $_SESSION['licence'];
$registration_date = $_SESSION['registration_date'];
if ( $licence == -1 && strtotime( $registration_date ) < strtotime( '-15 days' ) ) : wp_redirect( home_url( '/subscription/' ) ); endif;

get_header();

global $wpdb;
global $post;

$site_url = get_site_url();
$slug = $post->post_name;
	
$user_id = get_current_user_id();
	
$master_loc = $_SESSION['master_loc'];
$measure_toggle = $_SESSION['measure_toggle'];
$tag_toggle = $_SESSION['tag_toggle'];
$currency = $_SESSION['currency'];

$chart = str_replace( '-', ' ', $_GET['chart'] );
$series = str_replace( '-', ' ', $_GET['series'] );

$filter_url = str_replace( '-', ' ', $_GET['filter'] );
$filter_hyphen = str_replace( '   ', ' - ', $filter_url );
$filter_left = substr($filter_hyphen, 0, strpos($filter_hyphen, '|'));
$filter_right = substr($filter_hyphen, strpos($filter_hyphen, '|')+1);

if( empty( $filter_left ) ) : $filter = $filter_hyphen; $filter2 = ' IS NULL'; else : $filter = $filter_left; $filter2 = '=\''.$filter_right.'\''; endif;

$frequency = $_GET['frequency'];
$start = $_GET['start'];
$end = $_GET['end'];
$chart_setup = $wpdb->get_row( "SELECT master_chart.id, module, tag, chart_title, data_series, xAxis, yAxis, axis_type, help_id, sql_chart, filter_type FROM master_chart INNER JOIN master_module ON master_chart.mod_id=master_module.id INNER JOIN master_tag ON master_chart.chart_type=master_tag.id WHERE chart_title='$chart' and data_series='$series'" );
$chart_id = $chart_setup->id;
$module = $chart_setup->module;
$chart_type = $chart_setup->tag;
$chart_title = $chart_setup->chart_title;
$data_series = $chart_setup->data_series;
$xAxis_value = $chart_setup->xAxis;
$yAxis = $chart_setup->yAxis;
$axis_type = $chart_setup->axis_type;
$help_id = $chart_setup->help_id;
$sql_chart = $chart_setup->sql_chart;
$filter_type = $chart_setup->filter_type;

$chart_units = $wpdb->get_row( "SELECT system_tag.tag AS system_tag, custom_tag.tag, unit_tag.tag AS unit_tag FROM custom_tag LEFT JOIN master_tag system_tag ON custom_tag.tag_id=system_tag.id LEFT JOIN master_tag unit_tag ON custom_tag.unit_id=unit_tag.id WHERE system_tag.tag='$filter' AND custom_tag.tag$filter2 AND custom_tag.loc_id=$master_loc AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" );
$chart_unit = $chart_units->unit_tag;

if( $xAxis_value == 'Frequency' ) : $xAxis = ucwords( $frequency ); else : $xAxis = $xAxis_value; endif; ?>
	
<article class="col-12 px-3">
	<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		
		<h1 class="h4-style">Charts <i class="fal fa-chevron-double-right small"></i> <?php echo $module ?> <i class="fal fa-chevron-double-right small"></i> <?php echo $chart_title.' '.$data_series ?> <i class="fal fa-chevron-double-right small"></i> <?php if( empty( $filter_left ) ) : echo $filter; else : echo $filter.' - '.$filter_right; endif; if( !empty( $chart_unit ) ) : echo ' <span style="text-transform:none;">('.$chart_unit.')</span>'; else : echo ''; endif; ?></h1> <?php

		// $chart_rows = $wpdb->get_results( "SELECT id, $xAxis, sum($yAxis) AS $yAxis FROM $sql_chart WHERE system_tag='$filter' AND custom_tag$filter2 AND loc_id=$master_loc AND measure_date BETWEEN '$start' AND '$end' GROUP BY $frequency"); 
		$chart_rows = $wpdb->get_results( "SELECT id, $xAxis, sum($yAxis) AS $yAxis FROM $sql_chart WHERE loc_id=$master_loc AND measure_date BETWEEN '$start' AND '$end' GROUP BY $frequency"); ?>

		<div id="chart-box" style="width:100%; height:400px;"></div>
		
	</section>
</article>

<article class="col-8 px-3">
	<section class="dark-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		
		<h1 class="h4-style">Chart Data</h1>
		<table id="chart-table" class="table table-borderless nowrap" style="width:100%;"> 
			<thead>
				<tr>
					<th><?php echo $xAxis; ?></th> <?php 
					
					if( !empty( $filter_DONT_KNOW_WHAT_THIS_IS ) ) : echo '<th>'.$filter.'</th>'; endif; ?>
		
					<th class="text-right pr-4"><?php echo $yAxis; if( $axis_type == 'unit') : echo ' ('.$chart_unit.')'; elseif( $axis_type == 'currency') : echo ' ('.$currency.')'; endif; ?></th> <?php
					
					if( $tag_toggle == 1 ) : ?><th>Tags</th><?php endif; ?>
				</tr>
			</thead>
			<tbody> <?php
				foreach( $chart_rows as $chart_row ) :
					$row_date = date_create( $chart_row->$xAxis ); ?>
					<tr>
						<td><span class="d-none"><?php echo date_format($row_date,"d/m/y"); ?></span><?php echo $chart_row->$xAxis; ?></td>
						<td class="text-right pr-4"><?php echo number_format( $chart_row->$yAxis, 2 ) ?></td> <?php
						
						if( $tag_toggle == 1 ) : ?>
							<td><?php

								$data_id = $chart_row->id;
								$chart_tags = $wpdb->get_results( "SELECT tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$data_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" );

								foreach( $chart_tags as $chart_tag ) : ?>
									<div class="btn btn-light d-inline-block mr-2 float-none default-pointer"><?php echo $chart_tag->tag ?></div> <?php
								endforeach; ?>
							</td> <?php
						endif; ?>
					</tr> <?php
				endforeach; ?>
			</tbody>
		</table>
		
	</section>
</article>

<aside class="col-4 pr-3">
	<section class="secondary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		
		<h1 class="h4-style">Charts</h1> <?php
		
		$month_end = date( 'd-M-Y', strtotime('last day of previous month'));
		$month_start = date( '01-M-Y', strtotime('-12 months')); ?>
		
		<form method="post" name="change_chart" id="chartTypeForm">

			<div class="form-group">
				<label for="chartType">Chart Type<sup class="text-danger">*</sup></label>
				<select class="form-control" name="data_series" id="chartType" required>
					<option value="">Select Chart Type</option> <?php
					$chart_dropdowns = $wpdb->get_results( "SELECT data_series FROM master_chart WHERE chart_title='$chart_title'");
					foreach( $chart_dropdowns as $chart_dropdown ) :
						$data_series_value = $chart_dropdown->data_series;
						if( $series == strtolower( $data_series_value ) ) : $selected = 'selected'; else : $selected = ''; endif; ?>
						<option value="<?php echo $data_series_value ?>" <?php echo $selected ?>><?php echo $data_series_value ?></option> <?php
					endforeach; ?>
				</select>
			</div> <?php
			
			if( !empty( $filter_type ) ) :  ?>
	
				<div class="form-group">
					<label for="chartFilter"><?php echo $chart_title; ?> Type<sup class="text-danger">*</sup></label>
					<select class="form-control" name="chart_filter" id="chartFilter" required>
						<option value="">Select <?php echo $chart_title; ?> Type</option> <?php
						$filter_type(); ?>
					</select>
				</div> <?php
			
			endif; ?>
			
			<div class="form-group">
				<label for="chartFrequency">Frequency<sup class="text-danger">*</sup></label>
				<select class="form-control" name="chart_frequency" id="chartFrequency">
					<option value="year">Annual</option> <?php
					if( $measure_toggle == 86 ) : ?><option value="measure" <?php if( $frequency == 'custom' ) : echo 'selected'; endif; ?>>Measure</option> <?php endif;
					if( $measure_toggle <= 84 ) : ?><option value="month" <?php if( $frequency == 'month' ) : echo 'selected'; endif; ?>>Monthly</option> <?php endif;
					if( $measure_toggle <= 83 ) : ?><option value="week" <?php if( $frequency == 'week' ) : echo 'selected'; endif; ?>>Weekly</option> <?php endif;
					if( $measure_toggle <= 82 ) : ?><option value="day" <?php if( $frequency == 'day' ) : echo 'selected'; endif; ?>>Daily</option> <?php endif; ?>
				</select>
			</div>
			<div class="form-group">
				<label class="control-label" for="editMeasureStart">Date Range<sup class="text-danger">*</sup></label>
				<div class="input-group mb-2">
					<div class="input-group-prepend"><div class="input-group-text"><i class="far fa-calendar-alt"></i></div></div>
					<input type="text" class="form-control date" name="date_range_start" id="dateRangeStart" aria-describedby="dateRangeStart" placeholder="dd-mmm-yyyy" value="<?php echo date( 'd-M-Y', strtotime( $start ) ) ?>" data-date-end-date="0d" required>
					<input type="text" class="form-control date" name="date_range_end" id="dateRangeEnd" aria-describedby="dateRangeEnd" placeholder="dd-mmm-yyyy" value="<?php echo date( 'd-M-Y', strtotime( $end ) ) ?>" data-date-end-date="0d" required>
				</div>
				<small id="dateRangeWarning" class="form-text text-muted">Large date ranges will cause the page to load slowly</small>
			</div>
			<button type="submit" class="btn btn-primary" name="change_chart">Select Chart</button>
  
		</form> <?php
	
		if( isset( $_POST['change_chart'] ) ) :
			$change_data_series = str_replace( ' ', '-', strtolower( $_POST['data_series'] ) );
			$change_chart_filter = str_replace( ' ', '-', strtolower( $_POST['chart_filter'] ) );
			$change_chart_frequency = $_POST['chart_frequency'];
			$change_date_range_start = date( 'Y-m-d', strtotime( $_POST['date_range_start'] ) );
			$change_date_range_end = date( 'Y-m-d', strtotime( $_POST['date_range_end'] ) );
			
			header ('Location:'.$site_url.'/'.$slug.'/?chart='.strtolower($chart_title).'&series='.$change_data_series.'&filter='.$change_chart_filter.'&frequency='.$change_chart_frequency.'&start='.$change_date_range_start.'&end='.$change_date_range_end);
			ob_end_flush();	  
		endif;  ?>
		
	</section>
</aside>
		
<script>
	let draw = false;

	init();

	function init() {
  		const table = $("#chart-table").DataTable({
			order: [[ 0, "asc" ]],
			buttons: [ {
				extend: 'excel',
				text: '<i class="far fa-file-excel"></i> Export',
				title: "<?php echo $title_pasttense; ?> Items",
				exportOptions: { columns: 'thead th:not(.no-export)' }
			} ]
		});
		const tableData = getTableData(table);
  		createHighcharts(tableData);
		setTableEvents(table);
	}

	function getTableData(table) {
		const dataArray = [],
			  xArray = [],
			  y1Array = [],
			  y2Array = [];

		table.rows({ search: "applied" }).every(function() {
			const data = this.data();
			xArray.push(data[0].replace(":", "<br />"));
			y1Array.push(parseInt(data[1].replace(/\,/g, "")));
			y2Array.push(parseInt(data[2].replace(/\,/g, "")));
		});

		dataArray.push(xArray, y1Array, y2Array);

		return dataArray;
	}

	function createHighcharts(data) {
		Highcharts.setOptions({
			chart: {
				style: {
					fontFamily: 'Roboto',
					fontSize: '14px'
				}
			},
			lang: {
				thousandsSep: ","
			}
		});
        
		Highcharts.chart('chart-box', {
			title: {
				text: null
			},
			xAxis: [{
				title: { 
					text: '<?php echo $xAxis; ?>',
					style: {
                        fontWeight: 'bold',
					color: '#3a3a3a'
                    },
				},
				categories: data[0]
			}],
			yAxis: [{
				title: {
					text: '<?php echo $yAxis; if( $axis_type == 'unit') : echo ' ('.$chart_unit.')'; elseif( $axis_type == 'currency') : echo ' ('.$currency.')'; endif; ?>',
					style: {
                        fontWeight: 'bold',
						color: '#3a3a3a'
                    }
				}
			},
			/* {
				title: {
					align: 'low',
					offset: 0,
					text: 'Cost',
					style: {
                        fontWeight: 'bold',
						color: '#3a3a3a'
                    },
					rotation: 0,
					x: 35,
					y: 40
				},
				opposite: true
			} */],
			series: [{
				name: '<?php echo $yAxis; ?>',
				color: '#ff9a21',
				type: 'column',
				data: data[1]
			},
			/* {
				name: 'Cost',
				color: '#ce211b',
				type: 'spline',
				data: data[2],
				yAxis: 1
			} */],
			tooltip: {
				shared: true
			},
			legend: {
				align: 'right',
				verticalAlign: 'middle',
				layout: 'vertical',
				itemMarginBottom: 8,
				symbolPadding: 20
			},
			credits: {
				enabled: false
			}
		});
	}

function setTableEvents(table) {
  // listen for page clicks
  table.on("page", () => {
    draw = true;
  });

  // listen for updates and adjust the chart accordingly
  table.on("draw", () => {
    if (draw) {
      draw = false;
    } else {
      const tableData = getTableData(table);
      createHighcharts(tableData);
    }
  });
}

</script>
	
<!-- Datepicker -->

<script>

	$('.date').datepicker({
		format: 'd-M-yyyy',
		autoclose: true
	 });

</script> <?php

get_footer();