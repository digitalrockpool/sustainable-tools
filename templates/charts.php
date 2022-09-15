<?php ob_start();

/* ***

Template Post Type: Page

@package	Sustainable Tools
@author		Digital Rockpool
@link		https://www.sustainable.tools/yardstick
@copyright	Copyright (c) 2022, Digital Rockpool LTD
@license	GPL-2.0+

*** */

get_header();

$calendar_id = $_SESSION['calendar_id'];
$measure_toggle = $_SESSION['measure_toggle'];
$tag_toggle = $_SESSION['tag_toggle'];
$currency = $_SESSION['currency'];

$chart_url = $_GET['chart'];
$chart = str_replace( '-', ' ', $chart_url );

$frequency = $_GET['frequency'];
if( empty( $frequency ) ) : $frequency_sql = 'IS NULL'; else : $frequency_sql = '='.'\''.$frequency.'\''; endif;

$filter = str_replace( '   ', ' - ', str_replace( '-', ' ', $_GET['filter'] ) );

$date_url = $_GET['wdt_column_filter'];
$start_date = str_replace('/', '-', substr($date_url[1], 0, strpos($date_url[1], '|')));
$end_date = str_replace('/', '-', substr($date_url[1], strpos($date_url[1], '|')+1));

$chart_setup = $wpdb->get_row( "SELECT * FROM master_chart INNER JOIN master_module ON master_chart.mod_id=master_module.id WHERE chart='$chart' AND frequency $frequency_sql" );
$module = $chart_setup->module;
$chart_title = $chart_setup->chart;
$filter_dropdown = $chart_setup->filter_dropdown;
$table_id = $chart_setup->table_id;
$chart1_id = $chart_setup->chart1_id;
$chart2_id = $chart_setup->chart2_id;
$display = $chart_setup->display;

$chart_units = $wpdb->get_row( "SELECT system_tag.tag AS system_tag, custom_tag.tag, unit_tag.tag AS unit_tag FROM custom_tag LEFT JOIN master_tag system_tag ON custom_tag.tag_id=system_tag.id LEFT JOIN master_tag unit_tag ON custom_tag.unit_id=unit_tag.id WHERE system_tag.tag='$filter' AND custom_tag.loc_id=$master_loc AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)" );
$chart_unit = $chart_units->unit_tag; ?>
	
<article class="col-12 px-3">
	<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		
		<h1 class="h4-style">Charts <i class="fa-solid fa-chevrons-right"></i> <?php echo $module ?> <i class="fa-solid fa-chevrons-right"></i> <?php echo $chart; if( !empty( $filter ) ) : ?> <i class="fa-solid fa-chevrons-right"></i> <?php echo $filter; endif; if( !empty( $chart_unit ) ) : echo ' <span style="text-transform:none;">('.$chart_unit.')</span>'; endif; ?></h1> <?php
		
		if( $display == 'inline' ) : ?><div class="row"><div class="col-6"> <?php endif;

		echo do_shortcode( "[wpdatachart id=$chart1_id]" );
		
		if( $display == 'inline' ) : ?>
		
			</div>
			<div class="col-6"> <?php 
				echo do_shortcode( "[wpdatachart id=$chart2_id]" ); ?>
			</div></div><?php
				
		elseif( $display == 'block' ) :  ?>
		
			<div class="mt-5"> <?php 
		
				echo do_shortcode( "[wpdatachart id=$chart2_id]" );  ?>
		
			</div> <?php 
		
		endif; ?>
		
	</section>
</article>

<article class="col-8 px-3"><?php
				
	if( $display == 'sm-block' ) :  ?>
		<section class="primary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix"> <?php

			echo do_shortcode( "[wpdatachart id=$chart2_id]" ); ?>

		</section><?php 
	endif; ?>

	<section class="dark-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">

		<h1 class="h4-style float-left">Chart Data</h1> <?php
		echo do_shortcode( "[wpdatatable id=$table_id var1=$master_loc var2='$filter']" ); ?>

	</section>

</article>

<aside class="col-4 pr-3">
	<section class="secondary-box p-3 pb-4 mb-4 bg-white shadow-sm clearfix">
		
		<h1 class="h4-style">Charts</h1>
		
		<form method="post" name="change-chart" id="change-chart"> <?php
			
			if( !empty( $filter_dropdown ) ) :  ?>
	
				<div class="form-group">
					<label for="chart-filter"><?php echo $chart_title; ?> Type<sup class="text-danger">*</sup></label>
					<select class="form-control" name="chart-filter" id="chart-filter" required>
						<option value="">Select <?php echo $chart_title; ?> Type</option> <?php
						$filter_dropdown(); ?>
					</select>
				</div> <?php
			
			endif; ?>
			
			<div class="form-group">
				<label for="chart-frequency">Frequency<sup class="text-danger">*</sup></label>
				<select class="form-control" name="chart-frequency" id="chart-frequency">
					<option value="year">Annual</option> <?php
					if( $measure_toggle == 86 ) : ?><option value="measure" <?php if( $frequency == 'custom' ) : echo 'selected'; endif; ?>>Measure</option> <?php endif;
					if( $measure_toggle <= 84 ) : ?><option value="month" <?php if( $frequency == 'month' ) : echo 'selected'; endif; ?>>Monthly</option> <?php endif;
					if( $calendar_id == 231 ) : ?><option value="month_nepal" <?php if( $frequency == 'month_nepal' ) : echo 'selected'; endif; ?>>Monthly - Nepal</option> <?php endif;
					if( $measure_toggle <= 83 ) : ?><option value="week" <?php if( $frequency == 'week' ) : echo 'selected'; endif; ?>>Weekly</option> <?php endif;
					if( $measure_toggle <= 82 ) : ?><option value="day" <?php if( $frequency == 'day' ) : echo 'selected'; endif; ?>>Daily</option> <?php endif; ?>
				</select>
			</div>
			<div class="form-group">
				<label class="control-label" for="chart-start-date">Date Range<sup class="text-danger">*</sup></label>
				<div class="input-group mb-2">
					<div class="input-group-prepend"><div class="input-group-text"><i class="fa-regular fa-calendar-days"></i></div></div>
					<input type="text" class="form-control date" name="chart-start-date" id="chart-start-date" aria-describedby="chart-start-date" placeholder="dd-mmm-yyyy" value="<?php echo date( 'd-M-Y', strtotime( $start_date ) ) ?>" data-date-end-date="0d" required>
					<input type="text" class="form-control date" name="chart-end-date" id="chart-end-date" aria-describedby="chart-end-date" placeholder="dd-mmm-yyyy" value="<?php echo date( 'd-M-Y', strtotime( $end_date ) ) ?>" data-date-end-date="0d" required>
				</div>
				<small id="chart-date-warning" class="form-text text-muted">Large date ranges will cause the page to load slowly</small>
			</div>
			<button type="submit" class="btn btn-primary" name="change-chart">Select Chart</button>
  
		</form> <?php
	
		if( isset( $_POST['change-chart'] ) ) :
			$change_chart_filter = str_replace( ' ', '-', strtolower( $_POST['chart-filter'] ) );
			$change_chart_frequency = $_POST['chart-frequency'];
			$change_date_range_start = date( 'd/m/Y', strtotime( $_POST['chart-start-date'] ) );
			$change_date_range_end = date( 'd/m/Y', strtotime( $_POST['chart-end-date'] ) );
			
			header ('Location:'.$site_url.'/'.$slug.'/?chart='.strtolower($chart_title).'&frequency='.$change_chart_frequency.'&filter='.$change_chart_filter.'&wdt_column_filter[1]='.$change_date_range_start.'|'.$change_date_range_end);
			ob_end_flush();	  
		endif;  ?>
		
		<div class="d-none">hello<?php dynamic_sidebar( 'chart-sidebar' ); ?></div> 
		
	</section>
</aside>
	
<!-- Datepicker -->
<script>

	$('.date').datepicker({
		format: 'd-M-yyyy',
		autoclose: true
	 });

</script> <?php

get_footer();