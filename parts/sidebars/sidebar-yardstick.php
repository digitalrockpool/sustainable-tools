<?php 
/* ***

Template Part:  Sidebar - Yardstick

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$site_url = get_site_url();
$master_loc = $_SESSION['master_loc'];
$plan_id = $_SESSION['plan_id'];

$standards_assigned = $_SESSION['standards_assigned'];
$measure_toggle = $_SESSION['measure_toggle'];

$user_role = $args['user_role'];

if(  $measure_toggle == 82 ) : /* daily */

  $measure = 'day';
  $start_date = date( 'd/m/Y', strtotime('-8 days'));
  $end_date = date('d/m/Y',strtotime('-1 days'));
  
elseif( $measure_toggle == 83 ) : /* weekly */

  $measure = 'week';
  $day_number = date('N');
  $start_date = date('d/m/Y',strtotime('-'.( 90+$day_number ).' days'));
  $end_date = date('d/m/Y', strtotime('-'.$day_number.' days'));
  
elseif( $measure_toggle == 84 ) : /* monthly */
  
  $measure = 'month';
  $start_date = date('01/m/Y', strtotime('-12 months')); 
  $end_date = date('d/m/Y', strtotime('last day of previous month'));
  
else :

  $measure = 'year';
  $fy_day = $_SESSION['fy_day'];
  $fy_month = $_SESSION['fy_month'];
  $year = date('Y');
  $last_year = date('Y',strtotime('-1 year'));
  $fy_date_create = strtotime("$year-$fy_month-$fy_day");
  $fy_date = date('Y-m-d', $fy_date_create);
  $today = date('Y-m-d');

  if( $fy_date >= $today ) :
    $end_date_create = strtotime("$last_year-$fy_month-$fy_day");
  else :
    $end_date_create = strtotime("$year-$fy_month-$fy_day");
  endif;

  $end_date = date('d/m/Y', $end_date_create);
  $minus_ten_calc = "$last_year-$fy_month-$fy_day";
  $start_date = date( 'd/m/Y', strtotime('-10 years', strtotime($minus_ten_calc)));  

endif;

$selected_modules = $wpdb->get_results("SELECT tag_id, tag FROM custom_tag WHERE cat_id=50 AND active=1 AND loc_id=$master_loc ORDER BY id DESC LIMIT 4"); 
foreach( $selected_modules as $selected_module ) :
  $selected_tag_id = $selected_module->tag_id;
  $selected_tag = $selected_module->tag;
  $module_toggle[] = $selected_tag_id.$selected_tag;
endforeach; ?>

<img src="<?php echo get_template_directory_uri() ?>/lib/img/logo-yardstick-dark.png" alt="Yardstick Logo" class="py-2" />

<a class="nav-link" href="<?php echo $site_url ?>/yardstick/" role="button"><i class="fa-regular fa-chart-pie"></i>Dashboard</a><?php

if( $user_role == 222 || $user_role == 223 || $user_role == 224 ) : /* super_admin || admin || editor */ ?>

  <div class="nav-section-label">Data</div>  

  <a class="nav-link" href="<?php echo $site_url ?>/yardstick/data/?add=measures" role="button"><i class="fa-regular fa-bed"></i>Measures</a><?php

  if( in_array( "313on", $module_toggle ) ) : /* operations */ ?>
    <a class="nav-link nav-dropdown-indicator" data-bs-toggle="collapse" href="#collapse-data-operations" role="button" aria-expanded="false" aria-controls="collapse-data-operations"><i class="fa-regular fa-car-building"></i>Operations</a>
    <ul class="nav collapse" id="collapse-data-operations">
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/data/?add=fuel">Fuel</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/data/?add=water">Water</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/data/?add=waste">Waste</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/data/?add=plastic">Plastic</a></li>
    </ul><?php
  endif;

  if( in_array( "314on", $module_toggle ) ) : /* labour */
    $employee_types = $wpdb->get_results( "SELECT tag FROM master_tag WHERE cat_id=6 AND id NOT IN (SELECT tag_id FROM custom_tag WHERE cat_id=6 AND active=1 AND loc_id=$master_loc)");
    if( $employee_types ) : ?>
      <a class="nav-link nav-dropdown-indicator" data-bs-toggle="collapse" href="#collapse-data-labour" role="button" aria-expanded="false" aria-controls="collapse-data-labour"><i class="fa-solid fa-people-pants-simple"></i>Labour</a>
      <ul class="nav collapse" id="collapse-data-labour"><?php
          foreach( $employee_types as $employee_type ) :
            $employee_tag = $employee_type->tag;
            $employee_tag_url = strtolower( str_replace( " ", "-", $employee_tag ) ); ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/data/?add=<?php echo $employee_tag_url ?>"><?php echo $employee_tag ?></a></li><?php
          endforeach; ?>
      </ul><?php
    endif; 
  endif;

  if( in_array( "315on", $module_toggle ) ) : /* supply chain */ ?>
    <a class="nav-link" href="<?php echo $site_url ?>/yardstick/data/?add=supply-chain" role="button"><i class="fa-regular fa-cart-shopping"></i>Supply Chain</a><?php
  endif;

  if( in_array( "316on", $module_toggle ) ) : /* charity */
    $donation_types = $wpdb->get_results( "SELECT tag FROM master_tag WHERE cat_id=4 AND id NOT IN (SELECT tag_id FROM custom_tag WHERE cat_id=4 AND active=1 AND loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id))");
    if( $donation_types ) : ?>
      <a class="nav-link nav-dropdown-indicator" data-bs-toggle="collapse" href="#collapse-data-charity" role="button" aria-expanded="false" aria-controls="collapse-data-charity"><i class="fa-regular fa-hand-holding-heart"></i>Charity</a>
        <ul class="nav collapse" id="collapse-data-charity"><?php
          foreach( $donation_types as $donation_type ) :
            $donation_tag = $donation_type->tag;
            $donation_tag_url = strtolower( str_replace( " ", "-", $donation_tag ) ); ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/data/?add=<?php echo $donation_tag_url ?>"><?php echo $donation_tag ?></a></li><?php
          endforeach; ?>
      </ul><?php
    endif;
  endif;
  
  if( $standards_assigned >= 0 ) : ?>
    <div class="nav-section-label">Standards</div>  
    <a class="nav-link" href="<?php echo $site_url ?>/yardstick/standards" role="button"><i class="fa-regular fa-star"></i>Standards</a> <?php
  endif;

endif; ?>

<div class="nav-section-label">Charts</div><?php

if( in_array( "313on", $module_toggle ) ) : /* operations */ ?>
  <a class="nav-link nav-dropdown-indicator" data-bs-toggle="collapse" href="#collapse-chart-operations" role="button" aria-expanded="false" aria-controls="collapse-chart-operations"><i class="fa-regular fa-car-building"></i>Operations</a>
  <ul class="nav collapse" id="collapse-chart-operations"><?php

    $master_chart_rows = $wpdb->get_results( "SELECT chart, filter_dropdown FROM master_chart WHERE mod_id=2 GROUP BY chart" );
    foreach( $master_chart_rows as $master_chart_row ) :

      $chart_title = $master_chart_row->chart;
      $chart_filter = $master_chart_row->filter_dropdown;

      if( $chart_filter == 'chart_dropdown_master_tag' ) :

        $cat_id_lookup = $wpdb->get_row( "SELECT id FROM master_category WHERE category='$chart_title'");
        $cat_id = $cat_id_lookup->id;

        $filter_row = $wpdb->get_row( "SELECT master_tag.tag as tag FROM master_tag INNER JOIN custom_tag ON master_tag.id=custom_tag.tag_id INNER JOIN data_operations ON custom_tag.parent_id=data_operations.utility_id WHERE custom_tag.cat_id=$cat_id AND custom_tag.loc_id=$master_loc AND custom_tag.active=1 ORDER BY master_tag.tag ASC" );
        $filter_tag = $filter_row->tag;
        $filter = '&filter='.str_replace( ' ', '-', strtolower( $filter_tag ) );

      endif;

      $chart = str_replace( ' ', '-', strtolower( $chart_title ) ); ?>

      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url.'/charts/?chart='.$chart.'&frequency='.$measure.$filter.'&wdt_column_filter[1]='.$start_date.'|'.$end_date ?>"><?php echo $chart_title ?></a></li><?php

    endforeach; ?>
  </ul><?php
endif;

if( $user_role == 222 || $user_role == 223 ) : /* super_admin || admin */ ?>

  <div class="nav-section-label">Settings</div> 
  <a class="nav-link nav-dropdown-indicator" data-bs-toggle="collapse" href="#collapse-setting-general" role="button" aria-expanded="false" aria-controls="collapse-setting-general"><i class="fa-regular fa-sliders"></i>General</a>
  <ul class="nav collapse" id="collapse-setting-general"><?php
    if( $plan_id != 2 ) : ?>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=data_settings">Data Settings</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=categories">Categories</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=tags">Tags</a></li><?php
    endif; ?>
    <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=reporting">Reporting</a></li>
  </ul> <?php

  if( $plan_id != 2 ) : ?>
    <a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=measures" role="button"><i class="fa-regular fa-bed"></i>Measures</a> <?php
  endif; ?>

  <a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=locations" role="button"><i class="fa-regular fa-location-dot"></i>Locations</a><?php

  if( in_array( "313on", $module_toggle ) ) : /* operations */ ?>
    <a class="nav-link nav-dropdown-indicator" data-bs-toggle="collapse" href="#collapse-setting-operations" role="button" aria-expanded="false" aria-controls="collapse-setting-operations"><i class="fa-regular fa-car-building"></i>Operations</a>
    <ul class="nav collapse" id="collapse-setting-operations">
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=operation_settings">Operation Settings</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=fuel">Fuel</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=water">Water</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=waste">Waste</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=waste_disposal">Waste Disposal</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=plastic">Plastic</a></li>
    </ul><?php
  endif;

  if( in_array( "314on", $module_toggle ) ) : /* labour */ ?>
    <a class="nav-link nav-dropdown-indicator" data-bs-toggle="collapse" href="#collapse-setting-labour" role="button" aria-expanded="false" aria-controls="collapse-setting-labour"><i class="fa-solid fa-people-pants-simple"></i>Labour</a>
    <ul class="nav collapse" id="collapse-setting-labour">
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=labour_settings">Labour Settings</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=employee_types">Employee Types</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=ethnicities">Ethnicities</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=roles">Roles</a></li>
    </ul><?php
  endif;

  if( in_array( "316on", $module_toggle ) ) : /* charity */ ?>
    <a class="nav-link" href="<?php echo $site_url ?>/yardstick/settings/?setting=donation_types" role="button"><i class="fa-regular fa-hand-holding-heart"></i>Charity</a><?php
  endif;

endif; ?>


<script>
$(function() {
  // Sidebar toggle behavior
  $('#sidebarCollapse').on('click', function() {
    $('#sidebar, #content').toggleClass('active');
  });
});
</script>

<script>
$(document).ready(function(){
    $('a[data-toggle="collapse"]').on('show.bs.collapse', function(e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });
    var activeTab = localStorage.getItem('activeTab');
    if(activeTab){
        $('#myTab a[href="' + activeTab + '"]').tab('show');
    }
});
</script>

<?php

