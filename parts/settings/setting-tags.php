<?php 
/* ***

Template Part:  Settings - Tags

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
$tag_toggle = $_SESSION['tag_toggle'];

$entry_date = date( 'Y-m-d H:i:s' );

$cat_id = $args['cat_id'];

if( $tag_toggle == 0 ) : ?>

  <p>Please enable and add at least one category before adding tags.</p> <?php

else : ?>

  <form method="post" name="add-tag-settings" id="add-tag-settings" class="needs-validation" novalidate>

    <div id="repeater-field">
      <div class="entry form-row mb-1">
        <div class="col-5">
          <select id="set-category" name="set-category[]" class="form-control" required>
            <option value="" selected disabled>Select Category *</option> <?php

            $category_dropdowns = $wpdb->get_results( "SELECT parent_id, category FROM custom_category WHERE loc_id=$master_loc AND active=1 AND id IN (SELECT MAX(id) FROM custom_category GROUP BY parent_id) ORDER BY category ASC" );

            foreach( $category_dropdowns as $category_dropdown ) : ?>
              <option value="<?php echo $category_dropdown->parent_id ?>"><?php echo $category_dropdown->category ?></option> <?php
            endforeach; ?>
          </select>
          <div class="invalid-feedback">Select a category</div>
        </div>

        <div class="col-5">
          <input type="text" class="form-control" id="set-tag-name" name="set-tag-name[]" placeholder="Tag Name *" required>
          <div class="invalid-feedback">Enter a tag</div>
        </div>

        <div class="col-2">
          <span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fas fa-plus"></i></button></span>
        </div>
      </div>
    </div>

    <div class="form-row">
      <div class="col-2 offset-10 mb-3"><button class="btn btn-primary float-none" type="submit" name="add-tag-settings">Add</button></div>
    </div>
  </form> <?php

  $set_category_array = $_POST['set-category'];
  $set_tag_name_array = $_POST['set-tag-name'];

  if ( isset( $_POST['add-tag-settings'] ) ) :

    foreach( $set_category_array as $index => $set_category_array ) :

      $custom_cat = $set_category_array;
      $tag = $set_tag_name_array[$index];
      $tag_check = $wpdb->get_row( "SELECT tag FROM custom_tag WHERE tag='$tag' AND loc_id=$master_loc" );

      if( empty( $tag_check ) ) :

        $wpdb->insert( 'custom_tag',
          array(
            'entry_date' => $entry_date,
            'record_type' => 'entry',
            'tag' => $tag,
            'tag_id' => NULL,
            'size' => NULL,
            'unit_id' => NULL,
            'custom_cat' => $custom_cat,
            'cat_id' => $cat_id,
            'parent_id' => 0,
            'user_id' => $user_id,
            'active' => 1,
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