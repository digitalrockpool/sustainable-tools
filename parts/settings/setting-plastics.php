<?php 
/* ***

Template Part:  Settings - Plastic

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

$utility_dropdowns = $wpdb->get_results( "SELECT master_tag.id, master_tag.tag FROM master_tag WHERE cat_id=$cat_id ORDER BY master_tag.tag ASC" );

if( empty( $utility_dropdowns ) ) : ?>

  <p>All <?php echo strtolower( $title ); ?> have been added. If you require a new <?php echo strtolower( $title_singular ); ?> please email <a href="mailto:support@yardstick.co.uk" title="support@yardstick.co.uk">support@yardstick.co.uk</a></p> <?php

else : ?>

  <form method="post" name="add-operation-settings" id="add-operation-settings" class="needs-validation" novalidate> 

    <div id="repeater-field">
      <div class="entry row g-1 mb-1">
        <div class="col-3">
          <select id="set-operation-type" name="set-operation-type[]" class="form-select set-utility-type" required>
            <option value="" selected disabled>Select <?php echo $title_singular ?> *</option> <?php

            foreach( $utility_dropdowns as $utility_dropdown ) : ?>
              <option value="<?php echo $utility_dropdown->id ?>"><?php echo $utility_dropdown->tag ?></option> <?php
            endforeach; ?>

          </select>
          <div class="invalid-feedback">Please select <?php echo strtolower( $title_singular ); ?> </div>
        </div>

        <div class="col-3">
          <input type="text" class="form-control" id="set-operation-tag" name="set-operation-tag[]" placeholder="Description">
        </div> 
        
        <div class="col-2">
          <select id="set-operation-unit" name="set-operation-unit[]" class="form-select set-utility-unit" required>
            <option value="" selected disabled>Select Units *</option> <?php

            $unit_dropdowns = $wpdb->get_results( "SELECT master_tag.id, master_tag.tag FROM master_tag INNER JOIN relation_tag ON master_tag.id=relation_tag.child_id WHERE relation='$title_singular-unit' GROUP BY master_tag.tag" );

            foreach( $unit_dropdowns as $unit_dropdown ) : ?>
              <option value="<?php echo $unit_dropdown->id ?>"><?php echo $unit_dropdown->tag ?></option> <?php
            endforeach; ?>

          </select>
          <div class="invalid-feedback">Please select units</div>
        </div>

        <div class="col-2">
          <input type="number" class="form-control" id="set-operation-size" name="set-operation-size[]" min="0" step="0.01" placeholder="Quantity *" required>
          <div class="invalid-feedback">Please enter a number greater than or equal to 0.01</div>
        </div>

        <div class="col-2">
          <span class="input-group-btn"><button type="button" class="btn btn-success btn-add"><i class="fa-solid fa-plus"></i></button></span>
        </div>
      </div>
    </div> 

    <div class="row g-1">
      <div class="col-1 offset-10 mb-3"><button class="btn btn-primary float-none" type="submit" name="add-operation-settings">Add</button></div>
    </div>
  </form> <?php

  $set_operation_type_array = $_POST['set-operation-type'];
  $set_operation_tag_array = $_POST['set-operation-tag'];
  $set_operation_unit_array = $_POST['set-operation-unit'];
  $set_operation_size_array = $_POST['set-operation-size'];

  if ( isset( $_POST['add-operation-settings'] ) ) :

    foreach( $set_operation_type_array as $index => $set_operation_type_array ) :

      $tag_id = $set_operation_type_array;
      if( empty( $set_operation_tag_array ) ) : $tag = NULL; else : $tag = $set_operation_tag_array[$index]; endif;
      if( empty( $set_operation_unit_array ) ) : $unit_id = NULL; else : $unit_id = $set_operation_unit_array[$index]; endif;
      if( empty( $set_operation_size_array ) ) : $size = NULL; else : $size = $set_operation_size_array[$index]; endif;

      $tag_check = $wpdb->get_row( "SELECT tag_id FROM custom_tag WHERE tag='$tag' AND size=$size AND unit_id=$unit_id AND loc_id=$master_loc" );

      if( empty( $tag_check ) ) :

        $wpdb->insert( 'custom_tag',
          array(
            'entry_date' => $entry_date,
            'record_type' => 'entry',
            'tag' => $tag,
            'tag_id' => $tag_id,
            'size' => $size,
            'unit_id' => $unit_id,
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

      endif;

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