<?php 
/* ***

Template Part:  Settings - Data Settings

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
$entry_date = date( 'Y-m-d H:i:s' ); ?>

<form method="post" id="add-data-settings" name="add-data-settings">

  <h5>Module Visability</h5> <!-- swap out with toggle --> <?php

  $data_settings = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id='50'" );
  foreach( $data_settings as $data_setting ) :

    $switch_id = $data_setting->id;
    $switch_title = $data_setting->tag;
    $switch = strtolower($switch_title).'-switch-';

    $custom_data_settings = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag_id=$switch_id AND loc_id=$master_loc ORDER BY id DESC" );
    $switch_toggle = $custom_data_settings->tag;

    if( $switch_toggle === 'off'  ) : $switch_toggle = 'selected'; else : $switch_toggle = ''; endif; ?>

    <div class="form-group row">
      <label for="staticEmail" class="col-sm-2 col-form-label"><?php echo $switch_title ?></label>
      <div class="col-sm-10">
        <select class="form-control" name="set-data-setting[]" id="<?php echo $switch ?>on" >
          <option value="on">On</option>
          <option value="off"<?php echo $switch_toggle ?>>Off</option>
        </select>
        <input type="hidden" name="set-tag-id[]" value="<?php echo $switch_id ?>">
        <input type="hidden" name="set-cat-id[]" value="50">
      </div>
    </div> <?php

  endforeach;

  $custom_data_date = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE cat_id='48' AND loc_id=$master_loc ORDER BY id DESC" );
  $custom_data_table = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE cat_id='49' AND loc_id=$master_loc ORDER BY id DESC" );
  $data_date = $custom_data_date->tag ?: 1;
  $data_table = $custom_data_table->tag ?: 25; ?>

  <h5 class="border-top mt-4 pt-3">Edit Data Table Settings</h5>
  <div class="form-row">
    <div class="form-group col-6">
      <label for="set-default-date-range-number">Default Date Range<span class="text-danger"> *</span></label>
      <div class="input-group">
        <input type="number" class="form-control" id="set-default-date-range-number" name="set-data-setting[]" min="1" max="365" step="1" value="<?php echo $data_date ?>" required>
        <select class="form-control" id="set-default-date-range" name="set-tag-id[]">
          <option value="310">days</option>
          <option value="311" selected>month(s)</option>
        </select>
        <input type="hidden" name="set-cat-id[]" value="48">
      </div>
    </div>
    <div class="form-group col-6">
      <label for="set-default-table-rows">Default Table Rows<span class="text-danger"> *</span></label>
      <input type="number" class="form-control" id="set-default-table-rows" name="set-data-setting[]" min="1" step="1" value="<?php echo $data_table ?>" required>
      <input type="hidden" name="set-tag-id[]" value="312">
      <input type="hidden" name="set-cat-id[]" value="49">
    </div>
  </div>

  <div class="form-row">
    <div class="col-12 mb-3"><button class="btn btn-primary" type="submit" name="add-data-settings">Update</button></div>
  </div>

</form><?php

$set_data_setting_array = $_POST['set-data-setting'];
$set_tag_id_array = $_POST['set-tag-id'];
$set_cat_id_array = $_POST['set-cat-id'];

if ( isset( $_POST['add-data-settings'] ) ) :

  foreach( $set_data_setting_array as $index => $set_data_setting_array ) :

    $tag = $set_data_setting_array;
    $tag_id = $set_tag_id_array[$index];
    $cat_id = $set_cat_id_array[$index];
    $tag_check = $wpdb->get_row( "SELECT parent_id FROM custom_tag WHERE tag_id=$tag_id AND loc_id=$master_loc" );

    if( empty( $tag_check ) ) :

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
          'active' => 1,
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

    else :

      $wpdb->insert( 'custom_tag',
        array(
          'entry_date' => $entry_date,
          'record_type' => 'entry_revision',
          'tag' => $tag,
          'tag_id' => $tag_id,
          'size' => NULL,
          'unit_id' => NULL,
          'custom_cat' => NULL,
          'cat_id' => $cat_id,
          'active' => 1,
          'parent_id' => $tag_check->parent_id,
          'user_id' => $user_id,
          'loc_id' => $master_loc
        )
      );

  endif;

  endforeach;

  header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
  ob_end_flush();

endif;