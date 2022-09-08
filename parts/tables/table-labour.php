<?php 
/* ***

Template Part:  Edit Table - Labour

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
$measure_toggle = $_SESSION['measure_toggle'];
$tag_toggle = $_SESSION['tag_toggle'];

$tag_id = $args['tag_id'];
$module_strip = $args['module_strip'];
$title = $args['title'];

$edit_url = $_GET['edit'];
$start = $_GET['start'];
$end = $_GET['end'];

$entry_date = date( 'Y-m-d H:i:s' );

$edit_rows = $wpdb->get_results( "SELECT data_labour.id, measure, custom_tag.tag AS measure_name, measure_date, measure_start, measure_end, custom_location.location, data_labour.location AS location_id, gender_tag.tag AS gender, gender AS gender_id, ethnicity_tag.tag AS ethnicity, ethnicity AS ethnicity_id, disability_tag.tag AS disability, disability AS disability_id, level_tag.tag AS level, level AS level_id, role_tag.tag AS role, role AS role_id, part_time_tag.tag AS part_time, part_time AS part_time_id, promoted_tag.tag AS promoted, promoted AS promoted_id, under16_tag.tag AS under16, under16 AS under16_id, start_date, leave_date, days_worked, time_mentored, contract_dpw, contract_wpy, annual_leave, salary, overtime, bonuses, gratuities, benefits, cost_training, training_days, data_labour.note, data_labour.parent_id, data_labour.active FROM data_labour LEFT JOIN data_measure ON (data_labour.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag gender_tag ON data_labour.gender=gender_tag.id LEFT JOIN custom_location ON (data_labour.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) LEFT JOIN custom_tag ethnicity_tag ON (data_labour.ethnicity=ethnicity_tag.parent_id AND ethnicity_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag disability_tag ON data_labour.disability=disability_tag.id LEFT JOIN master_tag level_tag ON data_labour.level=level_tag.id LEFT JOIN custom_tag role_tag ON (data_labour.role=role_tag.parent_id AND role_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag part_time_tag ON data_labour.part_time=part_time_tag.id LEFT JOIN master_tag promoted_tag ON data_labour.promoted=promoted_tag.id LEFT JOIN master_tag under16_tag ON data_labour.under16=under16_tag.id INNER JOIN relation_user ON data_labour.loc_id=relation_user.loc_id WHERE employee_type=$tag_id AND relation_user.user_id=$user_id AND data_labour.id IN (SELECT MAX(id) FROM data_labour GROUP BY parent_id) AND measure_date BETWEEN '$start' AND '$end'" );

if( empty( $edit_rows) ) :

  echo 'No '.strtolower( $title ).' data has been added.';

else : ?>

  <div class="table-responsive-xl mb-3">
    <table id="edit" class="table table-borderless nowrap" style="width:100%;">
      <thead>
        <tr>
          <th scope="col" class="no-sort">View | Delete | Edit</th> <?php
          if( $measure_toggle == 86 && ( $tag_id == 72 || $tag_id == 73 ) ) : // custom measures || casual || intern ?>
            <th scope="col">Date Range</th>
            <th scope="col" class="filter-column">Measure Name</th> <?php
          else : ?>
            <th scope="col">Date</th> <?php
          endif; ?>
          <th scope="col" class="filter-column">Hometown</th>
          <th scope="col" class="filter-column">Gender</th>
          <th scope="col" class="filter-column">Ethnicity</th>
          <th scope="col" class="filter-column">Disabilities</th>
          <th scope="col" class="filter-column">Under 16</th>
          <th scope="col" class="filter-column">Role</th> <?php
          if( $tag_id == 72 || $tag_id == 73 ) : // casual || intern ?>
            <th scope="col">Days Worked</th> <?php
          elseif( $tag_id == 73 ) : ?>
            <th scope="col">Time Mentored in Days</th> <?php
          elseif( $tag_id == 228 ) : // contract ?>
            <th scope="col">Start Date</th>
            <th scope="col">Leave Date</th>
            <th scope="col">Contracted Days per Week</th>
            <th scope="col">Contracted Week per year</th>
            <th scope="col">Annual Leave in Days</th> <?php
          else : ?>
            <th scope="col" class="filter-column">Level</th>
            <th scope="col" class="filter-column">Part-Time</th>
            <th scope="col" class="filter-column">Promoted</th>
            <th scope="col">Start Date</th>
            <th scope="col">Leave Date</th>
            <th scope="col">Contracted Days per Week</th>
            <th scope="col">Contracted Week per year</th>
            <th scope="col">Annual Leave in Days</th> <?php
          endif;
          if( $tag_id == 72 ) : // casual ?>
            <th scope="col">Total Pay</th> <?php
          elseif( $tag_id == 73 ) : // intern ?>
            <th scope="col">Financial Compensation</th> <?php
          else : ?>
            <th scope="col">Salary</th>
            <th scope="col">Overtime</th>
            <th scope="col">Bonuses</th> <?php
          endif; ?>
          <th scope="col">Gratuities</th>
          <th scope="col">Benefits</th>
          <th scope="col">Cost of Training</th>
          <th scope="col">Number of Training Days</th> <?php
          if( $tag_toggle == 1 ) : ?> <th scope="col">Tags</th> <?php endif; ?>
          <th scope="col">Notes</th>
        </tr>
      </thead>

      <tbody> <?php

        foreach ( $edit_rows as $edit_row ) :

          $edit_id = $edit_row->id;
          $edit_measure_name = $edit_row->measure;
          $edit_measure_name = $edit_row->measure_name;
          $edit_measure_date = $edit_row->measure_date;
          $edit_measure_date_formatted = date_format( date_create( $edit_measure_date ), 'd-M-Y' );
          $edit_measure_start = $edit_row->measure_start;
          $edit_measure_start_formatted = date_format( date_create( $edit_measure_start ), 'd-M-Y' );
          $edit_measure_end = $edit_row->measure_end;
          $edit_measure_end_formatted = date_format( date_create( $edit_measure_end ), 'd-M-Y' );
          $edit_hometown = $edit_row->location;
          $edit_hometown_id = $edit_row->location_id;
          $edit_gender = $edit_row->gender;
          $edit_gender_id = $edit_row->gender_id;
          $edit_ethnicity = $edit_row->ethnicity;
          $edit_ethnicity_id = $edit_row->ethnicity_id;
          $edit_disability = $edit_row->disability;
          $edit_disability_id = $edit_row->disability_id;
          $edit_level = $edit_row->level;
          $edit_level_id = $edit_row->level_id;
          $edit_role = $edit_row->role;
          $edit_role_id = $edit_row->role_id;
          $edit_part_time = $edit_row->part_time;
          $edit_part_time_id = $edit_row->part_time_id;
          $edit_promoted = $edit_row->promoted;
          $edit_promoted_id = $edit_row->promoted_id;
          $edit_under16 = $edit_row->under16;
          $edit_under16_id = $edit_row->under16_id;
          $edit_start_date = $edit_row->start_date;
          $edit_start_date_formatted = date_format( date_create( $edit_start_date ), 'd-M-Y' );
          $edit_leave_date = $edit_row->leave_date;
          $edit_leave_date_formatted = date_format( date_create( $edit_leave_date ), 'd-M-Y' );
          $edit_days_worked = $edit_row->days_worked;
          $edit_time_mentored = $edit_row->time_mentored;
          $edit_contract_dpw = $edit_row->contract_dpw;
          $edit_contract_wpy = $edit_row->contract_wpy;
          $edit_annual_leave = $edit_row->annual_leave;
          $edit_salary = $edit_row->salary;
          $edit_overtime = $edit_row->overtime;
          $edit_bonuses = $edit_row->bonuses;
          $edit_gratuities = $edit_row->gratuities;
          $edit_benefits = $edit_row->benefits;
          $edit_cost_training = $edit_row->cost_training;
          $edit_training_days = $edit_row->training_days;
          $edit_note = $edit_row->note;
          $edit_parent_id = $edit_row->parent_id;
          $edit_active = $edit_row->active;
          $edit_labour = 'edit-'.$edit_id;
          $archive_labour = 'archive-'.$edit_id;

          $data_tags = $wpdb->get_results( "SELECT data_tag.tag_id, tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$edit_id AND mod_id=3 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag" ); ?>

          <tr<?php if( $edit_active == 0 ) : echo ' class="strikeout"'; endif; ?>>
            <td class="align-top strikeout-buttons">

              <button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $edit_id ?>"><i class="far fa-eye"></i></button>

              <div class="modal fade text-left" id="modalRevisions-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $edit_id ?>Title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                  <div class="modal-content">

                    <div class="modal-header">
                      <h5 class="modal-title" id="modalRevisions-<?php echo $edit_id ?>Title">Revisions for <?php echo $title ?></h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                    </div>

                    <div class="modal-body"> <?php

                      $revision_rows = $wpdb->get_results( "SELECT data_labour.id, data_labour.entry_date, measure, custom_tag.tag AS measure_name, measure_date, measure_start, measure_end, custom_location.location,  gender_tag.tag AS gender, ethnicity_tag.tag AS ethnicity, disability_tag.tag AS disability, level_tag.tag AS level, role_tag.tag AS role, part_time_tag.tag AS part_time, promoted_tag.tag AS promoted, under16_tag.tag AS under16, start_date, leave_date, days_worked, time_mentored, contract_dpw, contract_wpy, annual_leave, salary, overtime, bonuses, gratuities, benefits, cost_training, training_days, data_labour.note, data_labour.parent_id, data_labour.active, loc_name, display_name FROM data_labour LEFT JOIN data_measure ON (data_labour.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) LEFT JOIN custom_tag ON (data_measure.measure_name=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag gender_tag ON data_labour.gender=gender_tag.id LEFT JOIN custom_location ON (data_labour.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) LEFT JOIN custom_tag ethnicity_tag ON (data_labour.ethnicity=ethnicity_tag.parent_id AND ethnicity_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag disability_tag ON data_labour.disability=disability_tag.id LEFT JOIN master_tag level_tag ON data_labour.level=level_tag.id LEFT JOIN custom_tag role_tag ON (data_labour.role=role_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) LEFT JOIN master_tag part_time_tag ON data_labour.part_time=part_time_tag.id LEFT JOIN master_tag promoted_tag ON data_labour.promoted=promoted_tag.id LEFT JOIN master_tag under16_tag ON data_labour.under16=under16_tag.id INNER JOIN profile_location ON (data_labour.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN wp_users ON data_labour.user_id=wp_users.id INNER JOIN relation_user ON data_labour.loc_id=relation_user.loc_id WHERE employee_type=$tag_id AND data_labour.parent_id=$edit_parent_id AND relation_user.user_id=$user_id ORDER BY data_labour.id DESC" );

                      foreach( $revision_rows as $revision_row ) :

                        $revision_id = $revision_row->id;
                        $revision_entry_date = date_create( $revision_row->entry_date );
                        $revision_measure = $revision_row->measure;
                        $revision_measure_name = $revision_row->measure_name;
                        $revision_measure_date = $revision_row->measure_date;
                        $revision_measure_date_formatted = date_format( date_create( $revision_measure_date ), 'd-M-Y' );
                        $revision_measure_start = $revision_row->measure_start;
                        $revision_measure_start_formatted = date_format( date_create( $revision_measure_start ), 'd-M-Y' );
                        $revision_measure_end = $revision_row->measure_end;
                        $revision_measure_end_formatted = date_format( date_create( $revision_measure_end ), 'd-M-Y' );
                        $revision_hometown = $revision_row->location;
                        $revision_gender = $revision_row->gender;
                        $revision_ethnicity = $revision_row->ethnicity;
                        $revision_disability = $revision_row->disability;
                        $revision_level = $revision_row->level;
                        $revision_role = $revision_row->role;
                        $revision_part_time = $revision_row->part_time;
                        $revision_promoted = $revision_row->promoted;
                        $revision_under16 = $revision_row->under16;
                        $revision_start_date = $revision_row->start_date;
                        $revision_start_date_formatted = date_format( date_create( $revision_row->start_date ), 'd-M-Y' );
                        $revision_leave_date = $revision_row->leave_date;
                        $revision_leave_date_formatted = date_format( date_create( $revision_row->leave_date ), 'd-M-Y' );
                        $revision_days_worked = $revision_row->days_worked;
                        $revision_time_mentored = $revision_row->time_mentored;
                        $revision_contract_dpw = $revision_row->contract_dpw;
                        $revision_contract_wpy = $revision_row->contract_wpy;
                        $revision_annual_leave = $revision_row->annual_leave;
                        $revision_salary = $revision_row->salary;
                        $revision_overtime = $revision_row->overtime;
                        $revision_bonuses = $revision_row->bonuses;
                        $revision_gratuities = $revision_row->gratuities;
                        $revision_benefits = $revision_row->benefits;
                        $revision_household_contrib = $revision_row->household_contrib;
                        $revision_cost_training = $revision_row->cost_training;
                        $revision_training_days = $revision_row->training_days;
                        $revision_note = $revision_row->note;
                        $revision_parent_id = $revision_row->parent_id;
                        $revision_active = $revision_row->active;
                        $revision_username = $revision_row->display_name;

                        if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; elseif( $revision_active == 0 ) : $active_action = 'Deleted'; else : $active_action = 'Edited'; endif;
                        echo '<b>Date:</b> ';
                        if( empty( $revision_measure_date ) ) : echo $revision_measure_start_formatted.' to '.$revision_measure_end_formatted; else : echo $revision_measure_date_formatted; endif;
                        echo '<br />';
                        if( $measure_toggle == 86 && ( $tag_id == 72 || $tag_id == 73 ) ) : echo '<b>Measure Name:</b> '.$revision_measure_name.'<br />'; endif; // custom measures || casual || intern
                        echo '<b>Hometown:</b> '.$revision_hometown.'<br />';
                        echo '<b>Gender:</b> '.$revision_gender.'<br />';
                        echo '<b>Ethnicity:</b> '.$revision_ethnicity.'<br />';
                        echo '<b>Disabilities:</b> '.$revision_disability.'<br />';
                        echo '<b>Under 16:</b> '.$revision_under16.'<br />';
                        echo '<b>Role:</b> '.$revision_role.'<br />';

                        if( $tag_id == 72 || $tag_id == 73 ) : // casual || intern
                          echo '<b>Days Worked:</b> ';
                          $revision_days_worked_decimal_clean = rtrim( number_format( $revision_days_worked, 2 ) , '0' ); echo rtrim( $revision_days_worked_decimal_clean, '.' );
                          echo '<br />';
                        elseif( $tag_id == 73 ) : // intern
                          echo '<b>Time Mentored in Days:</b> ';
                          $revision_time_mentored_decimal_clean = rtrim( number_format( $revision_time_mentored, 2 ) , '0' ); echo rtrim( $revision_time_mentored_decimal_clean, '.' );
                          echo '<br />';
                        else :
                          echo '<b>Level:</b> '.$revision_level.'<br />';
                          echo '<b>Part-Time:</b> '.$revision_part_time.'<br />';
                          echo '<b>Promoted:</b> '.$revision_promoted.'<br />';


                          echo '<b>Start Date:</b> ';
                          if( !empty( $revision_start_date ) ) : echo $revision_start_date_formatted; endif;
                          echo '<br />';

                          echo '<b>Leave Date:</b> ';
                          if( !empty( $revision_leave_date ) ) : echo $revision_leave_date_formatted; endif;
                          echo '<br />';

                          echo '<b>Contracted Days per Week:</b> ';
                          $revision_contract_dpw_decimal_clean = rtrim( number_format( $revision_contract_dpw, 2 ) , '0' ); echo rtrim( $revision_contract_dpw_decimal_clean, '.' );
                          echo '<br />';

                          echo '<b>Contracted Week per year:</b> ';
                          $revision_contract_wpy_decimal_clean = rtrim( number_format( $revision_contract_wpy, 2 ) , '0' ); echo rtrim( $revision_contract_wpy_decimal_clean, '.' );
                          echo '<br />';

                          echo '<b>Annual Leave in Days:</b> ';
                          $revision_annual_leave_decimal_clean = rtrim( number_format( $revision_annual_leave, 2 ) , '0' ); echo rtrim( $revision_annual_leave_decimal_clean, '.' );
                          echo '<br />';

                        endif;

                        if( $tag_id == 72 ) : // casual
                          echo '<b>Total Pay:</b> '.number_format( $revision_salary, 2).'<br />';
                        elseif( $tag_id == 73 ) : // intern
                          echo '<b>Financial Compensation:</b> '.number_format( $revision_salary, 2).'<br />';
                        else :
                          echo '<b>Salary:</b> '.number_format( $revision_salary, 2).'<br />';
                          echo '<b>Overtime:</b> '.number_format( $revision_overtime, 2).'<br />';
                          echo '<b>Bonuses:</b> '.number_format( $revision_bonuses, 2).'<br />';
                        endif;

                        echo '<b>Gratuities:</b> '.number_format( $revision_gratuities, 2).'<br />';
                        echo '<b>Benefits:</b> '.number_format( $revision_benefits, 2).'<br />';
                        echo '<b>Cost of Training:</b> '.number_format( $revision_cost_training, 2).'<br />';
                        echo '<b>Number of Training Days:</b> ';
                        $revision_training_days_decimal_clean = rtrim( number_format( $revision_training_days, 2 ) , '0' ); echo rtrim( $revision_training_days_decimal_clean, '.' );
                        echo '<br />';

                        if( $tag_toggle == 1 ) :
                          echo '<b>Tags:</b> ';

                          $revision_tags = $wpdb->get_results( "SELECT tag FROM custom_tag INNER JOIN data_tag ON custom_tag.parent_id=data_tag.tag_id WHERE data_id=$revision_id AND mod_id=3 AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id) ORDER BY tag" );

                          $trim = '';
                          foreach( $revision_tags as $revision_tag ) :
                            $trim .= $revision_tag->tag.', ';
                          endforeach;

                          echo rtrim($trim, ', ').'<br />';

                        endif;

                        echo '<b>Notes:</b> '.$revision_note.'<br />';
                        echo '<b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_username.'<br />';
                        echo '<b>Entry ID:</b> '.$revision_id.'<br />';

                        if( $revision_id != $revision_parent_id ) : echo '<hr />'; endif;

                      endforeach; ?>

                    </div>

                  </div>
                </div>
              </div> <?php

              if( $edit_active == 1 ) : $edit_active_update = 0; $btn_style = 'btn-danger'; $edit_value = '<i class="far fa-trash-alt"></i>'; elseif( $edit_active == 0 ) : $edit_active_update = 1;  $btn_style = 'btn-success'; $edit_value = '<i class="far fa-trash-restore-alt"></i>'; endif; ?>

              <form method="post" name="archive" id="<?php echo $archive_labour ?>" class="d-inline-block">
                <button type="submit" class="btn <?php echo $btn_style ?> d-inline-block" name="<?php echo $archive_labour ?>"><?php echo $edit_value ?></button>
              </form> <?php

              if ( isset( $_POST[$archive_labour] ) ) :

                $wpdb->insert( 'data_labour',
                  array(
                    'entry_date' => $entry_date,
                    'record_type' => 'entry_revision',
                    'measure' => $edit_measure_name,
                    'measure_date' => $edit_measure_date,
                    'employee_type' => $tag_id,
                    'location' => $edit_hometown_id,
                    'gender' => $edit_gender_id,
                    'ethnicity' => $edit_ethnicity_id,
                    'disability' => $edit_disability_id,
                    'level' => $edit_level_id,
                    'role' => $edit_role_id,
                    'part_time' => $edit_part_time_id,
                    'promoted' => $edit_promoted_id,
                    'under16' => $edit_under16_id,
                    'start_date' => $edit_start_date,
                    'leave_date' => $edit_leave_date,
                    'days_worked' => $edit_days_worked,
                    'time_mentored' => $edit_time_mentored,
                    'contract_dpw' => $edit_contract_dpw,
                    'contract_wpy' => $edit_contract_wpy,
                    'annual_leave' => $edit_annual_leave,
                    'salary' => $edit_salary,
                    'overtime' => $edit_overtime,
                    'bonuses' => $edit_bonuses,
                    'gratuities' => $edit_gratuities,
                    'benefits' => $edit_benefits,
                    'household' => NULL,
                    'cost_training' => $edit_cost_training,
                    'training_days' => $edit_training_days,
                    'note' => $edit_note,
                    'active' => $edit_active_update,
                    'parent_id' => $edit_parent_id,
                    'user_id' => $user_id,
                    'loc_id' => $master_loc
                  )
                );

                $last_id = $wpdb->insert_id;

                if( !empty( $data_tags ) ) :

                  foreach( $data_tags as $data_tag ) :

                    $data_tag_id = $data_tag->tag_id;

                    $wpdb->insert( 'data_tag',
                      array(
                        'data_id' => $last_id,
                        'tag_id' => $data_tag_id,
                        'mod_id' => 3
                      )
                    );

                  endforeach;

                endif;

                header( 'Location:'.$site_url.'/'.$slug.'/?edit='.$edit_url.'&start='.$start.'&end='.$end );
                ob_end_flush();

              endif;

              if( $edit_active == 1 ) : ?>

                <button type="button" class="btn btn-light d-inline-block" data-toggle="modal" data-target="#modalEdit-<?php echo $edit_id ?>"><i class="fas fa-pencil"></i></button><?php

              endif; ?>

              <div class="modal fade" id="modalEdit-<?php echo $edit_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalEdit-<?php echo $edit_id ?>Title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                  <div class="modal-content text-left">

                    <div class="modal-header">
                      <h5 class="modal-title" id="modalEdit-<?php echo $edit_id ?>Title">Edit <?php echo $title ?></h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                    </div>

                    <div class="modal-body">
                      <p class="small">Fields marked with an asterisk<span class="text-danger">*</span> are required</p> <?php

                      $args = array(
                        'edit_charity' => $edit_charity,
                        'edit_labour' => $edit_labour,
                        'edit_id' => $edit_id,
                        'employee_id' => $employee_id,
                        'edit_measure' => $edit_measure,
                        'edit_measure_date_formatted' => $edit_measure_date_formatted,
                        'edit_hometown_id' => $edit_hometown_id,
                        'edit_gender_id' => $edit_gender_id,
                        'edit_ethnicity_id' => $edit_ethnicity_id,
                        'edit_disability_id' => $edit_disability_id,
                        'edit_level_id' => $edit_level_id,
                        'edit_role_id' => $edit_role_id,
                        'edit_part_time_id' => $edit_part_time_id,
                        'edit_promoted_id' => $edit_promoted_id,
                        'edit_under16_id' => $edit_under16_id,
                        'edit_start_date' => $edit_start_date,
                        'edit_start_date_formatted' => $edit_start_date_formatted,
                        'edit_leave_date' => $edit_leave_date,
                        'edit_leave_date_formatted' => $edit_leave_date_formatted,
                        'edit_days_worked' => $edit_days_worked,
                        'edit_time_mentored' => $edit_time_mentored,
                        'edit_contract_dpw' => $edit_contract_dpw,
                        'edit_contract_wpy' => $edit_contract_wpy,
                        'edit_annual_leave' => $edit_annual_leave,
                        'edit_salary' => $edit_salary,
                        'edit_overtime' => $edit_overtime,
                        'edit_bonuses' => $edit_bonuses,
                        'edit_gratuities' => $edit_gratuities,
                        'edit_benefits' => $edit_benefits,
                        'edit_cost_training' => $edit_cost_training,
                        'edit_training_days' => $edit_training_days,
                        'edit_note' => $edit_note,
                        'edit_parent_id' => $edit_parent_id
                      );

                      get_template_part('/parts/forms/form', $module_strip, $args ); ?>

                    </div>

                  </div>
                </div>
              </div>

            </td>
            <td><span class="d-none"><?php echo $edit_measure_date.$edit_measure_start ?></span><?php if( empty( $edit_measure_date ) ) : echo $edit_measure_start_formatted.' to '.$edit_measure_end_formatted; else : echo $edit_measure_date_formatted; endif; ?></td> <?php
            if( $measure_toggle == 86 && ( $tag_id == 72 || $tag_id == 73 ) ) : // custom measure && casual || intern ?><td><?php echo $edit_measure_name; ?></td> <?php endif; ?>
            <td><?php echo $edit_hometown ?></td>
            <td><?php echo $edit_gender ?></td>
            <td><?php echo $edit_ethnicity ?></td>
            <td><?php echo $edit_disability ?></td>
            <td><?php echo $edit_under16 ?></td>
            <td><?php echo $edit_role; ?></td> <?php
            if( $tag_id == 72 || $tag_id == 73 ) : // casual || intern ?>
              <td class="text-right"><?php $edit_days_worked_decimal_clean = rtrim( number_format( $edit_days_worked, 2 ) , '0' ); echo rtrim( $edit_days_worked_decimal_clean, '.' ); ?></td> <?php
            elseif( $tag_id == 73 ) : ?>
              <td class="text-right"><?php $edit_time_mentored_decimal_clean = rtrim( number_format( $edit_time_mentored, 2 ) , '0' ); echo rtrim( $edit_days_worked_decimal_clean, '.' ); ?></td> <?php
            elseif( $tag_id == 228 ) : // contract ?>
              <td><?php if( !empty( $edit_start_date ) ) : echo $edit_start_date_formatted; endif; ?></td>
              <td><?php if( !empty( $edit_leave_date ) ) : echo $edit_leave_date_formatted; endif; ?></td>
              <td class="text-right"><?php $edit_contract_dpw_decimal_clean = rtrim( number_format( $edit_contract_dpw, 2 ) , '0' ); echo rtrim( $edit_contract_dpw_decimal_clean, '.' ); ?></td>
              <td class="text-right"><?php $edit_contract_wpy_decimal_clean = rtrim( number_format( $edit_contract_wpy, 2 ) , '0' ); echo rtrim( $edit_contract_wpy_decimal_clean, '.' ); ?></td>
              <td class="text-right"><?php $edit_annual_leave_decimal_clean = rtrim( number_format( $edit_annual_leave, 2 ) , '0' ); echo rtrim( $edit_annual_leave_decimal_clean, '.' ); ?></td> <?php
            else : ?>
              <td><?php echo $edit_level; ?></td>
              <td><?php echo $edit_part_time; ?></td>
              <td><?php echo $edit_promoted; ?></td>
              <td><?php if( !empty( $edit_start_date ) ) : echo $edit_start_date_formatted; endif; ?></td>
              <td><?php if( !empty( $edit_leave_date ) ) : echo $edit_leave_date_formatted; endif; ?></td>
              <td class="text-right"><?php $edit_contract_dpw_decimal_clean = rtrim( number_format( $edit_contract_dpw, 2 ) , '0' ); echo rtrim( $edit_contract_dpw_decimal_clean, '.' ); ?></td>
              <td class="text-right"><?php $edit_contract_wpy_decimal_clean = rtrim( number_format( $edit_contract_wpy, 2 ) , '0' ); echo rtrim( $edit_contract_wpy_decimal_clean, '.' ); ?></td>
              <td class="text-right"><?php $edit_annual_leave_decimal_clean = rtrim( number_format( $edit_annual_leave, 2 ) , '0' ); echo rtrim( $edit_annual_leave_decimal_clean, '.' ); ?></td> <?php
            endif; ?>
            <td class="text-right"><?php echo number_format( $edit_salary, 2) ?></td> <?php
            if( $tag_id == 69 || $tag_id == 70 || $tag_id == 71 || $tag_id == 228 ) : // permanent || seasonal || fixed term || contract ?>
              <td class="text-right"><?php echo number_format( $edit_overtime, 2) ?></td>
              <td class="text-right"><?php echo number_format( $edit_bonuses, 2) ?></td> <?php
            endif; ?>
            <td class="text-right"><?php echo number_format( $edit_gratuities, 2) ?></td>
            <td class="text-right"><?php echo number_format( $edit_benefits, 2) ?></td>
            <td class="text-right"><?php echo number_format( $edit_cost_training, 2) ?></td>
            <td class="text-right"><?php $edit_training_days_decimal_clean = rtrim( number_format( $edit_training_days, 2 ) , '0' ); echo rtrim( $edit_training_days_decimal_clean, '.' ); ?></td> <?php

            if( $tag_toggle == 1 ) : ?>
              <td><?php
                foreach( $data_tags as $data_tag ) : ?>
                  <div class="btn btn-info d-inline-block mr-1 float-none"><?php echo $data_tag->tag ?></div> <?php
                endforeach; ?>
              </td> <?php
            endif; ?>

            <td><?php echo $edit_note ?></td>
          </tr> <?php

        endforeach; ?>

      </tbody>

      <tfoot>
        <tr>
          <th class="text-right">Filter Data</th><?php
          if( $measure_toggle == 86 && ( $tag_id == 72 || $tag_id == 73 ) ) : // custom measures || casual || intern ?>
            <th></th>
            <th></th><?php
          else : ?>
            <th></th><?php
          endif; ?>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th><?php
          if( $tag_id == 72 || $tag_id == 73 ) : // casual || intern ?>
            <th></th><?php
          elseif( $tag_id == 73 ) : ?>
            <th></th><?php
          elseif( $tag_id == 228 ) : // contract ?>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th><?php
          else : ?>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th><?php
          endif;
          if( $tag_id == 72 ) : // casual ?>
            <th></th><?php
          elseif( $tag_id == 73 ) : // intern ?>
            <th></th><?php
          else : ?>
            <th></th>
            <th></th>
            <th></th><?php
          endif; ?>
          <th></th>
          <th></th>
          <th></th>
          <th></th><?php
          if( $tag_toggle == 1 ) : ?> <th></th> <?php endif; ?>
          <th></th>
        </tr>
      </tfoot>

    </table>
  </div> <?php

  endif;