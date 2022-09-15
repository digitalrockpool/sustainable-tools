<?php 
/* ***

Template Part:  Settings - Ethnicities

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
$title_singular = $args['title_singular']; ?>

<form method="post" id="add-employee-settings" name="add-employee-settings" class="needs-validation" novalidate>

  <div id="repeater-field">
    <div class="entry row g-1 mb-1">
      <div class="col-10">
        <input type="text" class="form-control" id="set-employee-setting" name="set-employee-setting[]" placeholder="Add <?php echo strtolower( $title_singular ); ?>" required>
      </div>

      <div class="col-2">
        <span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fa-solid fa-plus"></i></button></span>
      </div>
    </div>
  </div>

  <div class="row g-1">
    <div class="col-2 offset-10 mb-3"><button class="btn btn-primary float-none" type="submit" name="add-employee-settings">Add</button></div>
  </div>
</form> <?php

$set_employee_setting_array = $_POST['set-employee-setting'];
$set_tag_id_array = $_POST['set-tag-id'];

if ( isset( $_POST['add-employee-settings'] ) ) :

  foreach( $set_employee_setting_array as $index => $set_employee_setting_array ) :

    $tag = $set_employee_setting_array;
    $tag_check = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag='$tag' AND cat_id=$cat_id AND loc_id=$master_loc" );

    if( empty( $tag_check ) ) :

      $wpdb->insert( 'custom_tag',
        array(
          'entry_date' => $entry_date,
          'record_type' => 'entry',
          'tag' => $tag,
          'tag_id' => NULL,
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

    endif;

  endforeach;

  header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
  ob_end_flush();

endif;