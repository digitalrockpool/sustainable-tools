<?php 
/* ***

Template Part:  Settings - Categories - Edits

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

$title = $args['title'];
$title_singular = $args['title_singular'];

$edit_rows  = $wpdb->get_results( "SELECT id, category, parent_id, active FROM custom_category WHERE loc_id=$master_loc AND id IN (SELECT MAX(id) FROM custom_category GROUP BY parent_id) ORDER BY active DESC, category ASC" );

if( empty( $edit_rows ) ) :

  echo 'Please add the '.strtolower( $title ).' used by your business.';

else : ?>

  <div class="table-responsive-xl">
    <table id="tags" class="table table-borderless">
      <thead>
        <tr>
          <th scope="col" class="no-sort">View | Delete | Edit </th>
          <th scope="col">Sort <?php echo $title ?></th>
        </tr>
      </thead>

      <tbody> <?php

        foreach ( $edit_rows as $edit_row ) :

          $edit_id = $edit_row->id;
          $edit_category = $edit_row->category;
          $edit_parent_id = $edit_row->parent_id;
          $edit_active = $edit_row->active;
          $edit_update = 'update-'.$edit_id;
          $edit_archive = 'archive-'.$edit_id; ?>

          <tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
            <td class="align-top strikeout-buttons">

              <button type="button" class="btn btn-dark d-inline-block" data-bs-toggle="modal" data-bs-target="#modalRevisions-<?php echo $edit_id ?>"><i class="fa-regular fa-eye"></i></button>

              <div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                  <div class="modal-content">

                    <div class="modal-header">
                      <h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $edit_category ?></h5>
                      <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa-regular fa-circle-xmark"></i></span></button>
                    </div>

                    <div class="modal-body"> <?php

                      $revision_rows = $wpdb->get_results( "SELECT custom_category.id, entry_date, category, parent_id, display_name, active FROM custom_category INNER JOIN yard_users ON custom_category.user_id=yard_users.id WHERE parent_id=$edit_parent_id ORDER BY custom_category.id DESC" );

                      foreach( $revision_rows as $revision_row ) :

                        $revision_id = $revision_row->id;
                        $revision_entry_date = date_create( $revision_row->entry_date );
                        $revision_catergory = $revision_row->category;
                        $revision_parent_id = $revision_row->parent_id;
                        $revision_active = $revision_row->active;
                        $revision_username = $revision_row->display_name;

                        if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

                        echo '<b>'.$title_singular.':</b> '.$revision_catergory.'<br />';
                        echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';
                        echo '<b>Entry ID:</b> '.$revision_id.'<br />';

                        if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

                      endforeach; ?>

                    </div>

                  </div>
                </div>
              </div> <?php

              if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="fa-solid fa-trash-can"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1; $btn_style = 'btn-success'; $edit_value = '<i class="fa-solid fa-trash-can-arrow-up"></i>'; endif; ?>

              <form method="post" name="archive" id="<?php echo $edit_archive ?>" class="d-inline-block">
                <button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $edit_archive ?>"><?php echo $edit_value ?></button>
              </form> <?php

              if ( isset( $_POST[$edit_archive] ) ) :

                $wpdb->insert( 'custom_category',
                  array(
                    'entry_date' => $entry_date,
                    'record_type' => 'entry_revision',
                    'category' => $edit_category,
                    'active' => $edit_active_update,
                    'parent_id' => $edit_parent_id,
                    'user_id' => $user_id,
                    'loc_id' => $master_loc
                  )
                );

                header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
                ob_end_flush();

              endif;

              if( $edit_active == 1 ) : ?>

                <button type="button" class="btn btn-light d-inline-block" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $edit_id ?>"><i class="fa-solid fa-pencil"></i></button>

                <div class="modal fade" id="modal-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-<?php echo $edit_id ?>Title" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">

                      <div class="modal-header">
                        <h5 class="modal-title" id="modal-<?php echo $edit_id ?>Title"><?php echo $edit_category ?></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa-regular fa-circle-xmark"></i></span></button>
                      </div>

                      <div class="modal-body">
                        <form method="post" name="update" id="<?php echo $edit_update ?>">

                          <div class="input-group mb-3">
                            <input type="text" class="form-control" value="<?php echo $edit_category ?>" aria-label="Category Update" aria-describedby="categoryUpdate" name="update_category">

                            <div class="input-group-append"><input type="submit" class="btn btn-primary d-inline-block" aria-describedby="categoryUpdate" name="<?php echo $edit_update ?>" value="Update" /></div>
                          </div>

                        </form>

                      </div>

                    </div>
                  </div>
                </div> <?php

              endif;

              if ( isset( $_POST[$edit_update] ) ) :
                $update_category = $_POST['update_category'];

                $wpdb->insert( 'custom_category',
                  array(
                    'entry_date' => $entry_date,
                    'record_type' => 'entry_revision',
                    'category' => $update_category,
                    'active' => 1,
                    'parent_id' => $edit_parent_id,
                    'user_id' => $user_id,
                    'loc_id' => $master_loc
                  )
                );

                header ('Location:'.$site_url.'/'.$slug.'/?setting='.$setting_query);
                ob_end_flush();

              endif; ?>

            </td>
            <td><?php echo $edit_category; ?></td>
          </tr> <?php

        endforeach; ?>

      </tbody>
    </table>
  </div> <?php

endif;