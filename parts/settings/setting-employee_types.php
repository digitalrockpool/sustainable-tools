<?php 
/* ***

Template Part:  Settings - Employee Types

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

$employee_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=$cat_id AND NOT EXISTS (SELECT tag FROM custom_tag WHERE master_tag.id=custom_tag.tag_id AND cat_id=$cat_id AND loc_id=$master_loc) ORDER BY tag ASC" );

if( empty( $employee_dropdowns ) ) : ?>

  <p>All <?php echo strtolower( $title ); ?> have been added. If you require a new <?php echo strtolower( $title_singular ); ?> please email <a href="mailto:support@yardstick.co.uk" title="support@yardstick.co.uk">support@yardstick.co.uk</a>.</p> <?php

else : ?>

  <form method="post" id="add-employee-settings" name="add-employee-settings" class="needs-validation" novalidate>

    <div id="repeater-field">
      <div class="entry row g-1 mb-1">
        <div class="col-10">
          <select class="form-select" id="set-employee-setting" name="set-employee-setting[]" required>
            <option value="" selected disabled>Select <?php echo $title_singular ?> *</option> <?php

            foreach( $employee_dropdowns as $employee_dropdown ) : ?>
              <option value="<?php echo $employee_dropdown->id ?>"><?php echo $employee_dropdown->tag ?></option> <?php
            endforeach; ?>

          </select>
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

      $tag = NULL;
      $tag_id = $set_employee_setting_array;
      $tag_check = $wpdb->get_row( "SELECT tag_id FROM custom_tag WHERE tag_id=$tag_id AND loc_id=$master_loc" );

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
  
endif;