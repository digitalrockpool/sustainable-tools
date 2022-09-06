<?php 
/* ***

Template Part:  Latest Entries - Labour

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$user_id = get_current_user_id();
$employee_id = $args['extra_value'];
$title = $args['title'];

$add_rows = $wpdb->get_results( "SELECT custom_location.location, tag, salary FROM data_labour INNER JOIN custom_location ON (data_labour.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) INNER JOIN master_tag ON data_labour.gender=master_tag.id INNER JOIN relation_user ON data_labour.loc_id=relation_user.loc_id WHERE employee_type=$employee_id AND relation_user.user_id=$user_id AND data_labour.active=1 ORDER BY data_labour.id DESC LIMIT 5" );

if( empty( $add_rows) ) :

	echo '<p>No '.strtolower( $title ).' data has been added.</p>';

else : ?>

	<div class="table-responsive-xl mb-4">
		<table id="latest" class="table table-borderless">
			<thead>
				<tr>
					<th scope="col">Hometown</th>
					<th scope="col">Gender</th> <?php
					if( $employee_id == 72 || $employee_id == 73 ) : // casual || intern ?> <th scope="col">Pay</th> <?php else : ?> <th scope="col">Salary</th> <?php endif; ?>
				</tr>
			</thead>

			<tbody> <?php

				foreach ( $add_rows as $add_row ) :

					$latest_hometown = $add_row->location;
					$latest_gender = $add_row->tag;
					$latest_salary = $add_row->salary; ?>

					<tr>
						<td><?php echo $latest_hometown ?></td>
						<td><?php echo $latest_gender ?></td>
						<td class="text-right" nowrap><?php echo number_format( $latest_salary, 2) ?></td>
					</tr> <?php

				endforeach; ?>

			</tbody>
		</table>
	</div> <?php

endif;