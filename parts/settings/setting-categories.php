<?php 
/* ***

Template Part:  Settings - Categories

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

<p>Tags are keywords that are attached to entries to give additional information. Categories lets you to label your groups of tags.</p>

<form method="post" name="setting-categories-tags" id="setting-categories-tags">

  <div class="row g-1">
    <div class="col-md-4 mb-3">
      <label class="font-weight-normal align-top pt-1 pr-4">Change Categories and Tags:<sup class="text-danger">*</sup></label> <?php

        $categories_tags_selected = $wpdb->get_row( "SELECT parent_id, active FROM custom_tag WHERE loc_id=$master_loc AND tag IS NULL AND id IN (SELECT MAX(id) FROM custom_tag WHERE cat_id=22 GROUP BY parent_id)" );
        $categories_tags_selected_active = $categories_tags_selected->active;
        $categories_tags_selected_parent_id = $categories_tags_selected->parent_id;
       if( $categories_tags_selected_active == 1 ) : $checked_enabled = 'checked'; else : $checked_disabled = 'checked'; endif; ?> 
    </div>

    <div class="col-md-4 mb-3">
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="edit-categories-tags" id="edit-categories-tags-enable" value="1" <?php echo $checked_enabled ?>>
        <label class="form-check-label" for="edit-categories-tags-enable">Enable</label>
      </div>

      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="edit-categories-tags" id="edit-categories-tags-disable" value="0" <?php echo $checked_disabled ?>>
        <label class="form-check-label" for="edit-categories-tags-disable">Disable</label>
      </div>
    </div>

    <div class="col-4 mb-3">
      <button class="btn btn-primary float-none" type="submit" name="setting-categories-tags">Update</button>
    </div>
  </div>

</form><?php

$update_categories_tags = $_POST['edit-categories-tags'];

if ( isset( $_POST['setting-categories-tags'] ) ) :

  $wpdb->insert( 'custom_tag',
    array(
      'entry_date' => $entry_date,
      'record_type' => 'entry_revision',
      'tag' => NULL,
      'tag_id' => NULL,
      'size' => NULL,
      'unit_id' => NULL,
      'cat_id' => 22,
      'active' => $update_categories_tags,
      'parent_id' => $categories_tags_selected_parent_id,
      'user_id' => $user_id,
      'loc_id' => $master_loc
    )
  );

  header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
  ob_end_flush();

endif;

if( $categories_tags_selected_active == 1 ) : ?>

  <h4 class="border-top pt-3 mt-4">Add Categories</h5>
  <form method="post" name="add-category-settings" id="add-category-settings" class="needs-validation" novalidate>

    <div id="repeater-field">
      <div class="entry row g-1 mb-1">
        <div class="col-10">
          <input type="text" class="form-control" id="set-category-name" name="set-category-name[]" placeholder="Category Name *" required>
          <div class="invalid-feedback">Enter a category name</div>
        </div>

        <div class="col-2">
          <span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fa-solid fa-plus"></i></button></span>
        </div>
      </div>
    </div>

    <div class="row g-1">
      <div class="col-2 offset-10 mb-3"><button class="btn btn-primary float-none" type="submit" name="add-category-settings">Add</button></div>
    </div>
  </form> <?php

  $set_category_name_array = $_POST['set-category-name'];

  if ( isset( $_POST['add-category-settings'] ) ) :

    foreach( $set_category_name_array as $index => $set_category_name_array ) :

      $category = $set_category_name_array;
      $category_check = $wpdb->get_row( "SELECT category FROM custom_category WHERE category='$category' AND loc_id=$master_loc" );

      if( empty( $category_check ) ) :

        $wpdb->insert( 'custom_category',
          array(
            'entry_date' => $entry_date,
            'record_type' => 'entry',
            'category' => $category,
            'active' => 1,
            'parent_id' => 0,
            'user_id' => $user_id,
            'loc_id' => $master_loc
          )
        );

        $parent_id = $wpdb->insert_id;

        $wpdb->update( 'custom_category',
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