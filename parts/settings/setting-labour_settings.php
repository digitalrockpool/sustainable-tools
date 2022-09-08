<?php 
/* ***

Template Part:  Settings - Labour Settings

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
$entry_date = date( 'Y-m-d H:i:s' );

$cat_id = $args['cat_id'];
$title = $args['title'];
$title_singular = $args['title_singular'];

$labour_settings = $wpdb->get_results( "SELECT id FROM custom_tag WHERE cat_id=$cat_id AND loc_id=$master_loc" );

if( !empty( $labour_settings ) ) : ?>

  <p>All <?php echo strtolower( $title ); ?> have been added. If you require a new <?php echo strtolower( $title_singular ); ?> please email <a href="mailto:support@yardstick.co.uk" title="support@yardstick.co.uk">support@yardstick.co.uk</a>.</p> <?php

else : ?>

  <form method="post" id="add-employee-settings" name="add-employee-settings" class="needs-validation" novalidate>

    <div class="form-row">

      <div class="col-md-4 mb-3">
        <label for="set-dpw">Contracted days per week<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" id="set-dpw" name="set-employee-setting[]" min="1" max="7" step="0.1" value="5" required>
        <input type="hidden" name="set-tag-id[]" value="281">
        <div class="invalid-feedback">Please enter a number between 1 and 5</div>
      </div>

      <div class="col-md-4 mb-3">
        <label for="set-wpy">Contracted weeks per year<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" id="set-wpy" name="set-employee-setting[]" min="1" max="52" step="0.1" value="52" required>
        <input type="hidden" name="set-tag-id[]" value="282">
        <div class="invalid-feedback">Please enter a number between 1 and 52</div>
      </div>

      <div class="col-md-4 mb-3">
        <label for="set-al">Annual leave in days<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" id="set-al" name="set-employee-setting[]" min="1" max="365" step="0.1" value="25" required>
        <input type="hidden" name="set-tag-id[]" value="283">
        <div class="invalid-feedback">Please enter a number between 1 and 365</div>
      </div>

    </div>

    <div class="form-row">
      <div class="col-2 offset-10 mb-3"><button class="btn btn-primary" type="submit" name="add-employee-settings">Add</button></div>
    </div>
  </form> <?php

  $set_employee_setting_array = $_POST['set-employee-setting'];
  $set_tag_id_array = $_POST['set-tag-id'];

  if ( isset( $_POST['add-employee-settings'] ) ) :

    foreach( $set_employee_setting_array as $index => $set_employee_setting_array ) :

      $tag = $set_employee_setting_array;
      $tag_id = $set_tag_id_array[$index];

      $wpdb->insert( 'custom_tag',
        array(
          'entry_date' => $entry_date,
          'record_type' => 'entry',
          'tag' => $tag,
          'tag_id' => $tag_id,
          'size' => NULL,
          'unit_id' => NULL,
          'custom_cat' => NULL,
          'cat_id' => $cat_id,
          'parent_id' => 0,
          'user_id' => $user_id,
          'loc_id' => $master_loc
        )
      );

      $parent_id = $wpdb->insert_id;

      $wpdb->update( 'custom_tag',
        array(
          'parent_id' => $parent_id,
        ),
        array(
          'id' => $parent_id
        )
      );

    endforeach;

    header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
    ob_end_flush();

  endif;

endif;