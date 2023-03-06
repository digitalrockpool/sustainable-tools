<?php 
/* ***

Template Part:  Account - Manage Team Members - Revisions

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$site_url = get_site_url();
$slug = $post->post_name;

$user_id = get_current_user_id();
$master_loc = $_SESSION['master_loc'];
$user_role = $_SESSION['user_role'];

$entry_date = date( 'Y-m-d H:i:s' );  ?>

<section class="dark-box p-3 mb-4 bg-white shadow-sm">

  <h2 class="h4-style">Edit Team Members</h2> <?php

  $teams  = $wpdb->get_results( "SELECT relation_user.id, user_id, display_name, user_email, master_tag.id AS user_role_id, tag, relation_user.parent_id, relation_user.active FROM wp_users INNER JOIN relation_user ON wp_users.ID=relation_user.user_id INNER JOIN master_tag ON relation_user.role_id=master_tag.id WHERE relation_user.loc_id=$master_loc AND user_id <> $user_id AND relation_user.id IN (SELECT MAX(id) FROM relation_user GROUP BY parent_id) AND active=1 ORDER BY relation_user.active DESC, display_name ASC" );

  if( empty( $teams ) ) :

    echo 'Please add team members.';

  else : ?>	

    <div class="table-responsive-xl">
      <table id="tags" class="table table-borderless"> 
        <thead>
          <tr>
            <th scope="col" class="no-sort">View | Delete | Edit</th>
            <th scope="col">Team Member</th>
            <th scope="col">Role</th>
          </tr>
        </thead>

        <tbody> <?php

          foreach ( $teams as $team ) :

            $edit_id = $team->id;
            $edit_user_id = $team->user_id;
            $edit_display_name = $team->display_name;
            $edit_email = $team->user_email;
            $edit_user_role_id = $team->user_role_id;
            $edit_user_role = $team->tag;
            $edit_parent_id = $team->parent_id;
            $edit_active = $team->active;
            $edit_update = 'update-'.$edit_id;
            $edit_archive = 'archive-'.$edit_id; ?>

            <tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
              <td class="align-top strikeout-buttons">

                <button type="button" class="btn btn-dark d-inline-block" data-bs-toggle="modal" data-bs-target="#modalRevisions-<?php echo $edit_id ?>"><i class="fa-regular fa-eye"></i></button>

                <div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">

                      <div class="modal-header">
                        <h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $edit_display_name ?></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa-regular fa-circle-xmark"></i></span></button>
                      </div>

                      <div class="modal-body"> <?php

                        $revision_rows = $wpdb->get_results( "SELECT relation_user.id, display_name, tag, relation_user.active, relation_user.parent_id FROM wp_users INNER JOIN relation_user ON wp_users.ID=relation_user.edited_by INNER JOIN master_tag ON relation_user.role_id=master_tag.id WHERE relation_user.parent_id=$edit_parent_id ORDER BY relation_user.id DESC" );
                              
                        foreach( $revision_rows as $revision_row ) :

                              $revision_id = $revision_row->id;
                              $revision_entry_date = date_create( $revision_row->entry_date );
                              $revision_user_role = $revision_row->tag;
                              $revision_username = $revision_row->display_name;
                              $revision_active = $revision_row->active; 
                              $revision_parent_id = $revision_row->parent_id;

                              echo '<b>Role:</b> '.$revision_user_role.'<br />';

                              if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;

                              echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';
                              echo '<b>Entry ID:</b> '.$revision_id.'<br />';
                              if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

                        endforeach; ?>

                      </div>

                    </div>
                  </div>
                </div> 

                <button type="button" class="btn btn-danger d-inline-block" data-bs-toggle="modal" data-bs-target="#modalDelete-<?php echo $edit_id ?>"><i class="fa-solid fa-trash-can"></i></button>

                <div class="modal fade text-left" id="modalDelete-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalDelete-<?php echo $edit_id ?>Title" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">

                      <div class="modal-header">
                        <h5 class="modal-title" id="modalDelete-<?php echo $edit_id ?>Title">Are you sure you want to delete <?php echo $edit_display_name ?>?</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa-regular fa-circle-xmark"></i></span></button>
                      </div>

                      <div class="modal-body"> 
                        <p>Deleting a user will remove all their account information except their display name. If the user has entered any data this will be kept.</p>
                      </div>
                      
                      <div class="modal-footer">
                        <form method="post" name="archive" id="<?php echo $edit_archive ?>" class="d-inline-block">
                          <button type="submit" class="btn btn-primary" name="<?php echo $edit_archive ?>">Delete Team Member</button>
                        </form>
                      </div>

                    </div>
                  </div>
                </div> <?php

                if ( isset( $_POST[$edit_archive] ) ) :

                  $wpdb->insert( 'relation_user',
                    array(
                      'entry_date' => $entry_date,
                      'record_type' => 'entry_revision',
                      'user_id' => $edit_user_id,
                      'loc_id' => $master_loc,
                      'role_id' => $edit_user_role_id,
                      'active' => 0,
                      'deleted_user' => $edit_display_name,
                      'parent_id' => $edit_parent_id,
                      'edited_by' => $user_id
                    )
                  );

                  header ('Location:'.$site_url.'/'.$slug);
                  ob_end_flush();	

                endif;

                if( $edit_active == 1 ) : ?>

                  <button type="button" class="btn btn-light d-inline-block" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $edit_id ?>"><i class="fa-solid fa-pencil"></i></button>

                  <div class="modal fade" id="modal-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modal-<?php echo $tag_id ?>Title" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                      <div class="modal-content">

                        <div class="modal-header">
                          <h5 class="modal-title" id="modal-<?php echo $edit_id ?>Title"><?php echo $edit_display_name ?></h5>
                          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa-regular fa-circle-xmark"></i></span></button>
                        </div>

                        <div class="modal-body">
                          
                          <form method="post" name="update" id="<?php echo $edit_update ?>">
      
                            <label for="update-user-role">User Role</label>
                            <div class="input-group mb-3"> <?php
                  
                              if( $user_role == 222 ) : /* super admin */

                                $user_role_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=35 ORDER BY id ASC" ); 

                              else : 

                                $user_role_dropdowns = $wpdb->get_results( "SELECT id, tag FROM master_tag WHERE cat_id=35 AND id!=222 ORDER BY id ASC" ); 

                              endif; ?>

                              <select class="custom-select d-inline-block" id="update-user-role" aria-label="Update User Role" name="update-user-role"><?php

                                foreach( $user_role_dropdowns as $user_role_dropdown ) :

                                  $edit_dropdown_user_role_id = $user_role_dropdown->id;
                                  $edit_dropdown_user_role = $user_role_dropdown->tag;

                                  if( $edit_dropdown_user_role == $edit_user_role ) : $selected = 'selected'; else : $selected = ''; endif;

                                  echo '<option value="'.$edit_dropdown_user_role_id.'" '.$selected.'>'.$edit_dropdown_user_role.'</option>';

                                endforeach; ?>

                              </select> 

                              <div class="input-group-append"><input type="submit" class="btn btn-primary d-inline-block" aria-describedby="Update" name="<?php echo $edit_update ?>" value="Update" /></div>
                            </div>

                          </form> 
                        
                          <p>Team members are responsible for editing their own name and email address under <a href="<?php echo $site_url.'/'.$slug.'/' ?>/my-profile/" title="My Profile">My Profile</a>.</p>
                        
                        </div>

                      </div>
                    </div>
                  </div> <?php

                  $update_user_role = $_POST['update-user-role'];

                  if ( isset( $_POST[$edit_update] ) ) :

                    $wpdb->insert( 'relation_user',
                      array(
                        'entry_date' => $entry_date,
                        'record_type' => 'entry_revision',
                        'user_id' => $edit_user_id,
                        'loc_id' => $master_loc,
                        'role_id' => $update_user_role,
                        'active' => 1,
                        'deleted_user' => NULL,
                        'parent_id' => $edit_parent_id,
                        'edited_by' => $user_id
                      )
                    );

                    header ('Location:'.$site_url.'/'.$slug);
                    ob_end_flush();	

                  endif;						
                      
                endif; ?>

              </td>
              <td><?php echo '<b>'.$edit_display_name.'</b><br />'.$edit_email; ?></td>
              <td><?php echo $edit_user_role ?></td>
            </tr> <?php

          endforeach; ?>

        </tbody>
      </table>
    </div> <?php

  endif; ?>
</section> <?php