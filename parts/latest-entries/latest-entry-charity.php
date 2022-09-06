<?php 
/* ***

Template Part:  Latest Entries - Charity

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$user_id = get_current_user_id();
$title = $args['title'];
$donation_type = $wpdb->get_row( "SELECT id FROM master_tag WHERE tag='$title'" );
$donation_id = $donation_type->id;

$add_rows = $wpdb->get_results( "SELECT measure_date, measure_start, custom_location.location, amount FROM data_charity LEFT JOIN data_measure ON (data_charity.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) INNER JOIN custom_location ON (data_charity.location=custom_location.parent_id AND custom_location.id IN (SELECT MAX(id) FROM custom_location GROUP BY parent_id)) INNER JOIN relation_user ON data_charity.loc_id=relation_user.loc_id WHERE donation_type=$donation_id AND relation_user.user_id=$user_id AND data_charity.active=1 AND data_charity.id IN (SELECT MAX(id) FROM data_charity GROUP BY parent_id) ORDER BY data_charity.id DESC LIMIT 5" );

if( empty( $add_rows) ) :

	echo '<p>No '.strtolower( $title ).' data has been added.</p>';

else : ?>

	<div class="table-responsive-xl mb-4">
		<table id="latest" class="table table-borderless">
			<thead>
				<tr>
					<th scope="col">Date</th>
					<th scope="col">Location</th>
					<th scope="col">Amount</th>
				</tr>
			</thead>

			<tbody> <?php

				foreach ( $add_rows as $add_row ) :

					$latest_date = $add_row->measure_date;
					$latest_date_formatted = date_format( date_create( $latest_date ), 'd-M-Y' );
					$latest_measure_start = $add_row->measure_start;
					$latest_measure_start_formatted = date_format( date_create( $latest_measure_start ), 'd-M-Y' );
					$latest_donee_location = $add_row->location;
					$latest_amount = $add_row->amount;

					if( !empty( $latest_custom_tag_entry ) ) : $latest_custom_tag = ' - '.$latest_custom_tag_entry; endif; ?>

					<tr>
						<td><?php if( empty( $latest_date ) ) : echo $latest_measure_start_formatted; else : echo $latest_date_formatted; endif; ?></td>
						<td><?php echo $latest_donee_location ?></td>
						<td class="text-right" nowrap><?php echo number_format( $latest_amount, 2) ?></td>
					</tr> <?php

				endforeach; ?>

			</tbody>
		</table>
	</div> <?php

endif;