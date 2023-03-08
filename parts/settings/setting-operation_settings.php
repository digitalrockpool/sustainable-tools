<?php 
/* ***

Template Part:  Settings - Operations

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2023, Digital Rockpool LTD
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

$utility_dropdowns = $wpdb->get_results( "SELECT master_tag.id AS id, master_tag.tag AS tag FROM master_tag WHERE cat_id=$cat_id AND NOT EXISTS (SELECT custom_tag.tag_id FROM custom_tag WHERE master_tag.id=custom_tag.tag_id AND loc_id=$master_loc) GROUP BY tag ORDER BY tag ASC" );

if( empty( $utility_dropdowns ) ) : ?>

  <p>All <?php echo strtolower( $title ); ?> have been added. If you require a new <?php echo strtolower( $title_singular ); ?> please email <a href="mailto:support@yardstick.co.uk" title="support@yardstick.co.uk">support@yardstick.co.uk</a></p> <?php

else : ?>

  <form method="post" name="add-operation-settings" id="add-operation-settings" class="needs-validation" novalidate>

    <div class="row g-1">

      <div class="col-md-4 mb-3">
        <label for="set-capacity">Capacity<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" id="set-capacity" name="set-operation-tag[]" min="1" step="1" required>
        <input type="hidden" name="set-operation-type[]" value="289"> <!-- value = master_tag.id -->
        <div class="invalid-feedback">Please enter a whole number greater than or equal to 1</div>

      </div>

      <div class="col-md-4 mb-3">
        <label for="set-days-open">Days open<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" id="set-days-open" name="set-operation-tag[]" min="1" max="365" step="1" required>
        <input type="hidden" name="set-operation-type[]" value="280"> <!-- value = master_tag.id -->
        <div class="invalid-feedback">Please enter a whole number between 1 and 365</div>
      </div>

      <div class="col-md-4 mb-3">
        <label for="set-total-area">Total area in m2<sup class="text-danger">*</sup></label>
        <input type="number" class="form-control" id="set-total-area" name="set-operation-tag[]" min="1" step="0.01" required>
        <input type="hidden" name="set-operation-type[]" value="288"> <!-- value = master_tag.id -->
        <div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
      </div>

    </div>

    <div class="row g-1">
      <div class="col-2 offset-10 mb-3"><button class="btn btn-primary" type="submit" name="add-operation-settings">Add</button></div>
    </div>
  </form> <?php

  if ( isset( $_POST['add-operation-settings'] ) ) :

    $set_operation_type_array = $_POST['set-operation-type'];
    $set_operation_tag_array = $_POST['set-operation-tag'];

    foreach( $set_operation_type_array as $index => $set_operation_type_array ) :

      $tag_id = $set_operation_type_array;
      $tag = $set_operation_tag_array[$index];
      
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

    endforeach;

  header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
  ob_end_flush();

  endif;

endif; ?>

<script>
  $(document).on('change', '.set-utility-type', function(){
    var unitPOP = $(this).val();
    var dropDown = $(this).parent().parent().find(".set-utility-unit");
    dropDown.empty();
    $.ajax({
      url:"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php",
      type:'GET',
      data:'action=operations_unit_settings&utilityID=' + unitPOP,
      success:function(results) {
        dropDown.append(results);
      }
    });
  });
</script> <?php