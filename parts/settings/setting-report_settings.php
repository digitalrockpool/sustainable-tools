<?php 
/* ***

Template Part:  Settings - Report Settings

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$site_url = get_site_url();
$slug = $post->post_name;
$setting_query = $_GET['setting'];

$user_id = get_current_user_id();
$master_loc = $_SESSION['master_loc'];
$plan_id = $_SESSION['plan_id'];

$entry_date = date( 'Y-m-d H:i:s' );

$report_initiate_id = $_SESSION['report_initiate_id']; /* used to update first report settings submission */
$report_active = $_SESSION['report_active']; /* used to identify first report settings submission - parent_id */

$set_fy_day = $_SESSION['fy_day'];
$set_fy_month = $_SESSION['fy_month'];
$set_fy_date = $set_fy_day.'-'.$set_fy_month.'-2000';

$set_calendar_id = $_SESSION['calendar_id'];
$set_calendar = $_SESSION['calendar'];
$set_currency = $_SESSION['currency'];
$set_geo_type = $_SESSION['geo_type'];

$set_local = $_SESSION['local'];
$set_very_local = $_SESSION['very_local'];

$set_local_location = $_SESSION['loc_county'];
$set_very_local_location = $_SESSION['loc_city'];

$set_industry_id = $_SESSION['industry_id'];
$set_sector_id = $_SESSION['sector_id'];
$set_subsector_id = $_SESSION['subsector_id'];
$set_industry = $_SESSION['industry'];
$set_sector = $_SESSION['sector'];
$set_subsector = $_SESSION['subsector'];
$set_other = $_SESSION['other'];

$split = explode( "|", $set_other );
$set_industry_other = $split[0];
$set_sector_other = $split[1];
$set_subsector_other = $split[2];

if( !empty( $set_industry_other ) ) : $set_industry_separator = ': '; endif;
if( !empty( $set_sector_other ) ) : $set_sector_separator = ': '; endif;
if( !empty( $set_subsector_other ) ) : $set_subsector_separator = ': '; endif;?>

<p><span class="text-danger"><i class="fa-solid fa-circle-exclamation"></i></i></span> It is recommended that report settings are only entered once and not changed after submission.</p>

<p class="small">Fields marked with an asterisk <span class="text-danger">*</span> are required.</p>


<form method="post" name="set-reporting-form" id="set-reporting-update"> <?php

  if( $report_active == 0 ) :

    $display_none_reporting_off = 'display:none;';

  else :

    $display_none_reporting_on = 'display:none;'; ?>

    <div class="row">
      <div class="col-12"><label>Change settings <span class="text-danger">*</span></label>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="report-change-set">
          <label class="form-check-label" for="report-change-set">I acknowledge this may affect the annual reports</label>
        </div>
      </div>
    </div> <?php

  endif; ?>

  <h5 class="border-top mt-4 pt-3">Reporting Period</h5>
  <p class="form-text">Add the month and day you would like your reporting year to start. For example you may want to choose the same date as the start of your financial year.</p>

  <div class="row">
    <div class="col-6">
      <div class="input-group">
        <span class="input-group-text"><i class="fa-regular fa-calendar-days"></i></span>
        <input type="text" class="form-control date reporting-on" style="<?php echo $display_none_reporting_on ?>" name="set-reporting-date" id="set-reporting-date" aria-describedby="set-reporting-date" placeholder="dd-mmm" value="<?php echo date_format( date_create( $set_fy_date ),'d-M' ); ?>" required>
        <input type="text" class="form-control date reporting-off" style="<?php echo $display_none_reporting_off ?>" aria-describedby="set-reporting-date" value="<?php echo date_format( date_create( $set_fy_date ),'d-M' ); ?>" readonly>
      </div>
    </div>
  </div>

  <h5 class="border-top mt-4 pt-3">Currency and Calendar</h5>
  <p class="form-text">Only one currency and calendar is supported per property. If your business uses multiple currencies choose the most frequently used. Data is entered using the Gregorian - International calendar but reports are generated using the regional calendar, if selected</p>

  <div class="row g-1">
    <div class="col-6">
      <label for="set-reporting-currency-code">Currency Code<span class="text-danger"> *</span></label>
      <select class="form-select reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-currency-code" name="set-reporting-currency-code"> <!-- multi-select doesn't work with reporting-on/off -->
        <option value="" selected disabled>Select Currency</option> <?php

        $currencies = $wpdb->get_results( "SELECT currency_alpha FROM master_country GROUP BY currency_alpha ORDER BY currency_alpha ASC" );

        foreach( $currencies as $currency ) :

          $currency_code = $currency->currency_alpha;

          if( $currency_code == $set_currency ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $currency_code ?>" <?php echo $selected ?>><?php echo $currency_code ?></option> <?php

        endforeach; ?>
      </select>
      <input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_currency ?>" readonly>
    </div>
    <div class="col-6">
      <label for="set-reporting-calendar">Calendar<span class="text-danger"> *</span></label>
      <select class="form-select reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-calendar" name="set-reporting-calendar">
        <option value="" selected disabled>Select Calendar</option> <?php

        $calendars = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=37 ORDER BY tag ASC" );

        foreach( $calendars as $calendar ) :

          $calendar_id = $calendar->id;
          $calendar_name = $calendar->tag;

          if( $calendar_id == $set_calendar_id ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $calendar_id ?>" <?php echo $selected ?>><?php echo $calendar_name ?></option> <?php

        endforeach; ?>
      </select>
      <input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_calendar ?>" readonly>
    </div>
  </div>

  <h5 class="border-top mt-4 pt-3">Geographical Boundaries</h5>
  <p class="form-text">Reporting can be based on your location or by distance. If you chose distance, please enter the distance in kilometres that you consider to be very local and local. These values will reflect the remoteness your business.</p> <?php

  if( empty( $_SESSION['loc_city'] ) ) : ?>

    <p><span class="text-danger"><i class="fa-solid fa-circle-exclamation"></i></i></span> Please enter your location before setting your geographical boundaries.</p> <?php

  endif;

  if( $set_geo_type == 143 ) :

    $unit = ' (kms)';
    $set_distance_very_local = $set_very_local;
    $set_distance_local = $set_local;
    $checked_distance = 'checked';
    $reporting_on_distance ='reporting-on';

  else :

    $unit = '';
    $set_location_very_local = $set_very_local;
    $set_location_local = $set_local;
    $checked_location = 'checked';
    $reporting_on_location ='reporting-on';

  endif; ?>

  <div class="form-check form-check-inline reporting-on" style="<?php echo $display_none_reporting_on ?>">
    <input class="form-check-input set-geo-type" type="radio" name="set-reporting-geo-type" id="set-reporting-distance" value="143" <?php echo $checked_distance ?>>
    <label class="form-check-label" for="set-reporting-distance">By distance (km)</label>
  </div>

  <div class="form-check form-check-inline reporting-on" style="<?php echo $display_none_reporting_on ?>">
    <input class="form-check-input set-geo-type" type="radio" name="set-reporting-geo-type" id="set-reporting-location" value="144" <?php echo $checked_location ?>>
    <label class="form-check-label" for="set-reporting-distance">By location</label>
  </div>

  <div class="row g-1 mt-2 distance-on <?php echo $reporting_on_distance ?>" style="display:none;">
    <div class="form-group col-6">
      <label for="set-reporting-distance-very-local">Very Local<?php echo $unit ?><span class="text-danger"> *</span></label>
      <input type="number" class="form-control distance-on-required" name="set-reporting-distance-very-local" id="set-reporting-distance-very-local" aria-describedby="set-reporting-distance-very-local" min="1" step="0.01" value="<?php echo $set_distance_very_local ?>">
    </div>

    <div class="col-6">
      <label for="set-reporting-distance-local">Local<?php echo $unit ?><span class="text-danger"> *</span></label>
      <input type="number" class="form-control distance-on-required" name="set-reporting-distance-local" id="set-reporting-distance-local" aria-describedby="set-reporting-distance-local" min="1" step="0.01" value="<?php echo $set_distance_local ?>">
    </div>
  </div>

  <div class="row g-1 mt-2 location-on <?php echo $reporting_on_location ?>" style="display:none;">
    <div class="col-6">
      <label for="set-reporting-location-very-local">Very Local<span class="text-danger"> *</span></label>
      <input type="text" class="form-control location-on-required" name="set-reporting-location-very-local" id="set-reporting-location-very-local" aria-describedby="set-reporting-location-very-local" value="<?php echo $set_very_local_location ?>" readonly>
    </div>

    <div class="col-6">
      <label for="set-reporting-location-local">Local<span class="text-danger"> *</span></label>
      <input type="text" class="form-control location-on-required" name="set-reporting-location-local" id="set-reporting-location-local" aria-describedby="set-reporting-location-local" value="<?php echo $set_local_location ?>" readonly>
    </div>
  </div>

  <div class="row g-1 reporting-off" style="<?php echo $display_none_reporting_off ?>">
    <div class="col-6">
      <label>Very Local<?php echo $unit ?></label>
      <input type="text" class="form-control" value="<?php echo $set_very_local ?>" readonly>
    </div>

    <div class="col-6">
      <label>Local<?php echo $unit ?></label>
      <input type="text" class="form-control" value="<?php echo $set_local ?>" readonly>
    </div>
  </div>

  <h5 class="border-top mt-4 pt-3">Benchmarking</h5>
  <p class="form-text">Benchmark your performance against other businesses in your industry, sector and subsector.</p>

  <div class="row g-1 mb-3">
    <div class="col-4">
      <label for="set-reporting-industry">Industry<span class="text-danger"> *</span></label>
      <select class="form-select reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-industry" name="set-reporting-industry">
        <option value="" selected disabled>Select Industry</option> <?php

        $industries = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=1 ORDER BY tag ASC" );

        foreach( $industries as $industry ) :

          $industry_id = $industry->id;
          $industry_tag = $industry->tag;

          if( $industry_tag == $set_industry ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $industry_id ?>" <?php echo $selected ?>><?php echo $industry_tag ?></option> <?php

        endforeach; ?>
      </select> <?php

      if( $set_industry_id == 145 ) : $industry_other_display = 'reporting-on'; endif; ?>

      <input type="text" class="form-control <?php echo $industry_other_display ?> industry-other-on mt-1" style="display: none;" name="set-reporting-industry-other" id="set-reporting-industry-other" aria-describedby="set-reporting-industry-other" value="<?php echo $set_industry_other ?>">
      <input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_industry.$set_industry_separator.$set_industry_other ?>" readonly>
    </div>
    <div class="col-4">
      <label for="set-reporting-sector">Sector<span class="text-danger"> *</span></label>
      <select class="form-select reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-sector" name="set-reporting-sector">
        <option value="" selected disabled>Select Sector</option> <?php

        $sectors = $wpdb->get_results( "SELECT master_tag.id, tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE parent_id=$set_industry_id ORDER BY tag ASC" );

        foreach( $sectors as $sector ) :

          $sector_id = $sector->id;
          $sector_tag = $sector->tag;

          if( $sector_tag == $set_sector ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $sector_id ?>" <?php echo $selected ?>><?php echo $sector_tag ?></option> <?php

        endforeach; ?>
      </select> <?php

      if( $set_sector_id == 146 ) : $sector_other_display = 'reporting-on'; endif; ?>

      <input type="text" class="form-control <?php echo $sector_other_display ?> sector-other-on mt-1" style="display: none;" name="set-reporting-sector-other" id="set-reporting-sector-other" aria-describedby="set-reporting-sector-other" value="<?php echo $set_sector_other ?>">
      <input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_sector.$set_sector_separator.$set_sector_other ?>" readonly>
    </div>
    <div class="col-4">
      <label for="set-reporting-subsector">Subsector<span class="text-danger"> *</span></label>
      <select class="form-select reporting-on" style="<?php echo $display_none_reporting_on ?>" id="set-reporting-subsector" name="set-reporting-subsector">
        <option value="" selected disabled>Select Subsector</option> <?php

        $subsectors = $wpdb->get_results( "SELECT master_tag.id, tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE parent_id=$set_sector_id ORDER BY tag ASC" );

        foreach( $subsectors as $subsector ) :

          $subsector_id = $subsector->id;
          $subsector_tag = $subsector->tag;

          if( $subsector_tag == $set_subsector ) : $selected = 'selected'; else : $selected = ''; endif; ?>

          <option value="<?php echo $subsector_id ?>" <?php echo $selected ?>><?php echo $subsector_tag ?></option> <?php

        endforeach; ?>
      </select> <?php

      if( $set_subsector_id == 147 ) : $subsector_other_display = 'reporting-on'; endif; ?>

      <input type="text" class="form-control <?php echo $subsector_other_display ?> subsector-other-on mt-1" style="display: none;" name="set-reporting-subsector-other" id="set-reporting-subsector-other" aria-describedby="set-reporting-subsector-other" value="<?php echo $set_subsector_other ?>">
      <input type="text" class="form-control reporting-off" style="<?php echo $display_none_reporting_off ?>" value="<?php echo $set_subsector.$set_subsector_separator.$set_subsector_other ?>" readonly>
    </div>
  </div>

  <div class="row g-1 reporting-on" style="<?php echo $display_none_reporting_on ?>">
    <div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="set-reporting-update">Update</button></div>
  </div> <?php

  $update_set_reporting_date = $_POST['set-reporting-date'];
  $update_set_reporting_currency_code = $_POST['set-reporting-currency-code'];
  $update_set_reporting_calendar = $_POST['set-reporting-calendar'];
  $update_set_reporting_geo_type = $_POST['set-reporting-geo-type'];
  $update_set_reporting_distance_very_local = $_POST['set-reporting-distance-very-local'];
  $update_set_reporting_distance_local = $_POST['set-reporting-distance-local'];
  $update_set_reporting_location_very_local = $_POST['set-reporting-location-very-local'];
  $update_set_reporting_location_local = $_POST['set-reporting-location-local'];
  $update_set_reporting_industry = $_POST['set-reporting-industry'];
  $update_set_reporting_sector = $_POST['set-reporting-sector'];
  $update_set_reporting_subsector = $_POST['set-reporting-subsector'];
  $update_set_reporting_industry_other = $_POST['set-reporting-industry-other'];
  $update_set_reporting_sector_other = $_POST['set-reporting-sector-other'];
  $update_set_reporting_subsector_other = $_POST['set-reporting-subsector-other'];

  // if( $plan_id == 1 ) : $fy_month = 1; elseif( $plan_id == 4 && $is_master == 0 ) : $fy_month = NULL;  else : $fy_month = $fy_month_entry; endif;

  $update_fy_day = date( 'j', strtotime( $update_set_reporting_date ) );
  $update_fy_month = date( 'n', strtotime( $update_set_reporting_date ) );

  if( $update_set_reporting_geo_type == 143 ) : $update_very_local = $update_set_reporting_distance_very_local; $update_local = $update_set_reporting_distance_local; else : $update_very_local = NULL; $update_local = NULL; endif;

  if( empty( $update_set_reporting_industry_other ) && empty( $update_set_reporting_sector_other ) && empty( $update_set_reporting_subsector_other ) ) : $update_other = NULL; else : $update_other = $update_set_reporting_industry_other.'|'.$update_set_reporting_sector_other.'|'.$update_set_reporting_subsector_other; endif;

  if ( isset( $_POST['set-reporting-update'] ) ) :

    if( $report_active == 0 ) :

      $wpdb->update( 'profile_locationmeta',
        array(
          'entry_date' => $entry_date,
          'record_type' => 'entry_revision',
          'industry' => $update_set_reporting_industry,
          'sector' => $update_set_reporting_sector,
          'subsector' => $update_set_reporting_subsector,
          'other' => $update_other,
          'fy_day' => $update_fy_day,
          'fy_month' => $update_fy_month,
          'currency' => $update_set_reporting_currency_code,
          'geo_type' => $update_set_reporting_geo_type,
          'very_local' => $update_very_local,
          'local' => $update_local,
          'calendar' => $update_set_reporting_calendar,
          'active' => 1,
          'parent_id' => $report_initiate_id,
          'user_id' => $user_id,
          'loc_id' => $master_loc
        ),
        array(
          'id' => $report_initiate_id
        )
      );

    else :

      $wpdb->insert( 'profile_locationmeta',
        array(
          'entry_date' => $entry_date,
          'record_type' => 'entry_revision',
          'industry' => $update_set_reporting_industry,
          'sector' => $update_set_reporting_sector,
          'subsector' => $update_set_reporting_subsector,
          'other' => $update_other,
          'fy_day' => $update_fy_day,
          'fy_month' => $update_fy_month,
          'currency' => $update_set_reporting_currency_code,
          'geo_type' => $update_set_reporting_geo_type,
          'very_local' => $update_very_local,
          'local' => $update_local,
          'calendar' => $update_set_reporting_calendar,
          'active' => 1,
          'parent_id' => $report_active,
          'user_id' => $user_id,
          'loc_id' => $master_loc
        )
      );

    endif;

    header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
    ob_end_flush();

  endif; ?>

</form>

<script>
  $.fn.datepicker.dates.en.titleFormat="MM"; /* day / month date picker */
  $(document).ready(function(){
      var date_input=$('input[name="set_reporting_date"]');
      date_input.datepicker({
      format: 'dd-M',
      autoclose: true,
      startView: 1,
      maxViewMode: "months",
      orientation: "bottom left",
      })
  });
  $(document).ready(function(){
    $('#report-change-set').click(function() {
      if( $(this).is(':checked')) {
        $('.reporting-on').show();
        $('.reporting-off').hide();
      }
      else {
        $('.reporting-on').hide();
        $('.reporting-off').show();
      }
    });
    $('#set-reporting-distance').click(function() {
      if( $(this).is(':checked')) {
        $('.distance-on').show();
        $('.distance-on-required').attr('required', '');
        $('.location-on').hide();
        $('.location-on-required').removeAttr('required');
      }
    });
    $('#set-reporting-location').click(function() {
      if( $(this).is(':checked')) {
        $('.location-on').show();
        $('.location-on-required').attr('required', '');
        $('.distance-on').hide();
        $('.distance-on-required').removeAttr('required');
      }
    });
    $('#set-reporting-industry').change(function() {
      if ($(this).val() == 145) {
        $('.industry-other-on').show();
        $('.industry-other-on').attr('required', '');
      }
      else {
        $('.industry-other-on').hide();
        $('.industry-other-on').removeAttr('required');
      }
    });
    $('#set-reporting-industry').change(function(){
      var sectorPOP = $('#set-reporting-industry').val();

      $("#set-reporting-sector").empty();
      $.ajax({
        url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
        type:'GET',
        data:'action=report_settings_sector&industryID=' + sectorPOP,

        success:function(results) {
          $("#set-reporting-sector").append(results);
        }
      });
    });
    $('#set-reporting-sector').change(function() {
      if ($(this).val() == 146) {
        $('.sector-other-on').show();
        $('.sector-other-on').attr('required', '');
      }
      else {
        $('.sector-other-on').hide();
        $('.sector-other-on').removeAttr('required');
      }
    });
    $('#set-reporting-sector').change(function(){
      var subsectorPOP = $('#set-reporting-sector').val();

      $("#set-reporting-subsector").empty();
      $.ajax({
        url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
        type:'GET',
        data:'action=report_settings_subsector&sectorID=' + subsectorPOP,

        success:function(results) {
          $("#set-reporting-subsector").append(results);
        }
      });
    });
    $('#set-reporting-subsector').change(function() {
      if ($(this).val() == 147) {
        $('.subsector-other-on').show();
        $('.subsector-other-on').attr('required', '');
      }
      else {
        $('.subsector-other-on').hide();
        $('.subsector-other-on').removeAttr('required');
      }
    });
  });

</script> <?php