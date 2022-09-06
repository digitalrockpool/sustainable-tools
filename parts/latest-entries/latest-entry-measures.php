<?php 
/* ***

Template Part:  Latest Entries - Measures

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$user_id = get_current_user_id();
$measure_toggle = $_SESSION['measure_toggle'];
$title = $args['title'];

$add_rows = $wpdb->get_results( "SELECT measure_start, bednight, roomnight, client, loc_name FROM data_measure INNER JOIN profile_location ON (data_measure.loc_id=profile_location.parent_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)) INNER JOIN relation_user ON data_measure.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND data_measure.active=1 AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id) ORDER BY data_measure.id DESC LIMIT 5" );

if( empty( $add_rows) ) :

  echo '<p>No '.strtolower( $title ).' data has been added.</p>';

else : ?>

  <div class="table-responsive-xl mb-4">
    <table id="latest" class="table table-borderless">
      <thead>
        <tr>
          <th scope="col">Start Date</th>
          <th scope="col">B/N</th>
          <th scope="col">R/N</th>
          <th scope="col">Client</th>
        </tr>
      </thead>

      <tbody> <?php

        foreach( $add_rows as $add_row ) :

          if( $measure_toggle == 83 ) : /* weekly */
            $latest_date = 'Week '.date_format( date_create( $add_row->measure_start ), 'W Y' );

          elseif( $measure_toggle == 84 ) : /* monthly */
            $latest_date = date_format( date_create( $add_row->measure_start ), 'M-Y' );

          else :
            $latest_date = date_format( date_create( $add_row->measure_start ), 'd-M-Y' );

          endif;

          $latest_bednight = $add_row->bednight;
          $latest_roomnight = $add_row->roomnight;
          $latest_client = $add_row->client; ?>

          <tr>
            <td><?php echo $latest_date ?></td>
            <td class="text-right" nowrap><?php echo number_format( $latest_bednight ) ?></td>
            <td class="text-right" nowrap><?php echo number_format( $latest_roomnight ) ?></td>
            <td class="text-right" nowrap><?php echo number_format( $latest_client ) ?></td>
          </tr> <?php

        endforeach; ?>

      </tbody>
    </table>
  </div><?php

endif;