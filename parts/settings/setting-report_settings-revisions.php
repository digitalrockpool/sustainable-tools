<?php 
/* ***

Template Part:  Settings - Report Settings - Revisions

@package	      Sustainable Tools
@author		      Digital Rockpool
@link		        https://www.sustainable.tools/yardstick
@copyright	    Copyright (c) 2022, Digital Rockpool LTD
@license	      GPL-2.0+ 

*** */

$master_loc = $_SESSION['master_loc']; ?>

<section class="secondary-box p-3 mb-4 bg-white shadow-sm">

  <h2 class="h4-style">Revisions</h2> <?php

  $report_settings = $wpdb->get_results( "SELECT profile_locationmeta.id, entry_date, display_name, parent_id FROM profile_locationmeta INNER JOIN yard_users ON profile_locationmeta.user_id=yard_users.id WHERE loc_id=$master_loc AND parent_id<>0 ORDER BY entry_date DESC" );

  if( empty( $report_settings ) ) : ?>

    <p>Report settings have not been edited.</p> <?php

  else : ?>

    <div class="table-responsive-xl">
      <table id="tags" class="table table-borderless">
        <tbody> <?php

          foreach( $report_settings as $report_setting) :

            $view_id = $report_setting->id;
            $view_entry_date = date_create( $report_setting->entry_date );
            $view_display_name = $report_setting->display_name;
            $view_parent_id = $report_setting->parent_id; ?>

            <tr>
              <td>
                <button type="button" class="btn btn-dark d-inline-block" data-toggle="modal" data-target="#modalRevisions-<?php echo $view_id ?>"><i class="far fa-eye"></i></button>

                <div class="modal fade text-left" id="modalRevisions-<?php echo $view_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalRevisions-<?php echo $view_id ?>Title" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">

                      <div class="modal-header">
                        <h5 class="modal-title" id="modalRevisions-<?php echo $view_id ?>Title">Revisions</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                      </div>

                      <div class="modal-body"> <?php

                        $revision_rows = $wpdb->get_results( "SELECT profile_locationmeta.id, profile_locationmeta.entry_date, display_name, industry, master_tag.tag AS industry_tag, sector, sector.tag AS sector_tag, subsector, subsector.tag AS subsector_tag, other, fy_day, fy_month, currency, geo_type, very_local, local, county, city, calendar, profile_locationmeta.parent_id FROM profile_locationmeta INNER JOIN yard_users ON profile_locationmeta.user_id=yard_users.id INNER JOIN profile_location ON profile_locationmeta.loc_id=profile_location.parent_id LEFT JOIN master_tag ON profile_locationmeta.industry=master_tag.id LEFT JOIN master_tag sector ON profile_locationmeta.sector=sector.id LEFT JOIN master_tag subsector ON profile_locationmeta.subsector=subsector.id WHERE profile_locationmeta.id=$view_id AND profile_location.id IN (SELECT MAX(id) FROM profile_location GROUP BY parent_id)" );

                        foreach( $revision_rows as $revision_row ) :

                          $revision_id = $revision_row->id;
                          $revision_entry_date = date_create( $revision_row->entry_date );
                          $revision_display_name = $revision_row->display_name;
                          $revision_industy_tag = $revision_row->industry_tag;
                          $revision_sector_tag = $revision_row->sector_tag;
                          $revision_subsector_tag = $revision_row->subsector_tag;
                          $revision_other = $revision_row->other;
                          $revision_fy_day = $revision_row->fy_day;
                          $revision_fy_month = $revision_row->fy_month;
                          $revision_currency = $revision_row->currency;
                          $revision_geo_type = $revision_row->geo_type;
                          $revision_parent_id = $revision_row->parent_id;

                          $revision_fy_date = $revision_fy_day.'-'.$revision_fy_month.'-2000';

                          if( $revision_geo_type == 143 ) : $revision_very_local = $revision_row->very_local.' km'; $revision_local = $revision_row->local.' km'; else : $revision_very_local = $revision_row->city; $revision_local = $revision_row->county; endif;

                          $revision_split = explode( "|", $revision_other );
                          if( $revision_industy_tag == 'Other' ) : $revision_other_industry = ' - '.$revision_split[0]; else : $revision_other_industry = ''; endif;
                          if( $revision_sector_tag == 'Other' ) : $revision_other_sector = ' - '.$revision_split[1]; else : $revision_other_sector = ''; endif;
                          if( $revision_subsector_tag == 'Other' ) : $revision_other_subsector = ' - '.$revision_split[2]; else : $revision_other_subsector = ''; endif;

                          echo '<p><b>Reporting Year Start Date: </b>'.date_format( date_create( $revision_fy_date ),'d F' ).'<br />';
                          echo '<b>Currency: </b>'.$revision_currency.'<br />';
                          echo '<b>Geographical Boundaries </b><br /><span class="px-3">Local: '.$revision_local.'</span><br /><span class="px-3">Very Local: '.$revision_very_local.'</span><br />';
                          echo '<b>Benchmarking </b><br /><span class="px-3">Industry: '.$revision_industy_tag.$revision_other_industry.'</span><br /><span class="px-3">Sector: '.$revision_sector_tag.$revision_other_sector.'</span><br /><span class="px-3">Subsector: '.$revision_subsector_tag.$revision_other_subsector.'</span></p>';

                          if( $revision_id == $revision_parent_id ) : $active_action = 'Added'; else : $active_action = 'Edited'; endif;

                          echo '<p><b>'.$active_action.' on:</b> '.date_format( $revision_entry_date, "d-M-Y H:i" ).' by '.$revision_display_name.'</p>';

                        endforeach; ?>

                      </div>

                    </div>
                  </div>
                </div>

              </td>
              <td> <?php

                if( $view_id == $view_parent_id ) : $view_active_action = 'Added'; else : $view_active_action = 'Edited'; endif;

                echo $view_active_action.' on '.date_format( $view_entry_date, "d-M-Y" ).' by '.$view_display_name ?>

              </td>
            </tr> <?php

          endforeach; ?>

        </tbody>
      </table>
    </div> <?php

  endif; ?>

</section> <?php