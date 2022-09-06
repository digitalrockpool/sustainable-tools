<?php 
/* ***

Template Part:  Latest Entries - Operations

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$user_id = get_current_user_id();
$cat_id = $args['extra_value'];
$title = $args['title'];

$add_rows = $wpdb->get_results( "SELECT measure_date, measure_start, master_tag.tag AS master_tag, custom_tag.tag AS custom_tag, amount FROM data_operations LEFT JOIN data_measure ON (data_operations.measure=data_measure.parent_id AND data_measure.id IN (SELECT MAX(id) FROM data_measure GROUP BY parent_id)) INNER JOIN custom_tag ON (data_operations.utility_id=custom_tag.parent_id AND custom_tag.id IN (SELECT MAX(id) FROM custom_tag GROUP BY parent_id)) INNER JOIN master_tag ON master_tag.id=custom_tag.tag_id INNER JOIN relation_user ON data_operations.loc_id=relation_user.loc_id WHERE relation_user.user_id=$user_id AND master_tag.cat_id=$cat_id AND data_operations.id IN (SELECT MAX(id) FROM data_operations GROUP BY parent_id) GROUP BY data_operations.id ORDER BY data_operations.id DESC LIMIT 5" );

if( empty( $add_rows) ) :

	echo '<p>No '.strtolower( $title ).' data has been added.</p>';

else : ?>

	<div class="table-responsive-xl mb-4">
		<table id="latest" class="table table-borderless">
			<thead>
				<tr>
					<th scope="col">Date</th>
					<th scope="col"><?php echo $title ?></th>
						<th scope="col">Amount</th>
				</tr>
			</thead>

			<tbody> <?php

				foreach ( $add_rows as $add_row ) :

					$latest_date = $add_row->measure_date;
					$latest_date_formatted = date_format( date_create( $latest_date ), 'd-M-Y' );
					$latest_measure_start = $add_row->measure_start;
					$latest_measure_start_formatted = date_format( date_create( $latest_measure_start ), 'd-M-Y' );
					$latest_master_tag = $add_row->master_tag;
					$latest_custom_tag_entry = $add_row->custom_tag;
					$latest_amount = $add_row->amount;

          if( !empty( $latest_custom_tag_entry ) ) : $latest_custom_tag = ' - '.$latest_custom_tag_entry; endif; ?>

					<tr>
					<td nowrap><?php if( empty( $latest_date ) ) : echo $latest_measure_start_formatted; else : echo $latest_date_formatted; endif; ?></td> <?php
					if( $loc_number > 1 ) : ?> <td scope="col"><?php echo substr( $latest_loc_name, 10 ) ?></td> <?php endif; ?>
					<td><?php echo $latest_master_tag.$latest_custom_tag ?></td>
					<td class="text-right" nowrap><?php echo number_format($latest_amount, 2) ?></td>
					</tr> <?php

        endforeach; ?>

			</tbody>
		</table>
	</div> <?php

endif;