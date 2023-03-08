/* if( $_FILES['csv']['size'] > 0 && $_FILES['csv']['type'] == 'text/csv' ) :

$file = $_FILES['csv']['tmp_name'];
$fileHandle = fopen($file, "r");
$i=0;

$loc_name = $_POST['loc_name'];
$utility_type = (int)$_POST['utility_type']; // Get these from page
$employee_type = (int)$_POST['employee_type'];
$donation_type = (int)$_POST['donation_type'];

while( ( $cell = fgetcsv( $fileHandle, 0, "," ) ) !== FALSE ) :

    $i++;
    $cell0_check = $cell[0];
    $cell1_check = $cell[1];

    if( $measure_toggle == 86 && $employee_type != 69 && $employee_type != 70 && $employee_type != 71 && $employee_type != 228 && ( empty( $cell0_check ) || empty( $cell1_check ) ) ) :

        $cell_check = 0;

    elseif( $measure_toggle == 84 && $mod_query == 1 && $calendar == 231 && ( empty( $cell0_check ) || empty( $cell1_check ) ) ) :

        $cell_check = 0;

    elseif( $measure_toggle == 86 && $employee_type != 72 && $employee_type != 73 && empty( $cell0_check ) ) :

        $cell_check = 0;

    elseif( $measure_toggle != 86 && empty( $cell0_check ) ) :

        $cell_check = 0;

    else :

        $cell_check = 1;

    endif;

    if( $i>1 && !empty( $cell_check ) ) :

        csv_upload( $cell, $mod_query, $utility_type, $employee_type, $donation_type, $loc_name );

    endif;

endwhile;

header ( "Location: $site_url/$slug/?mod=$mod_query" );

elseif( $_FILES['csv']['size'] > 0 ) :

echo 'The file you tried to upload is empty';

elseif( $_FILES['csv']['type'] == 'text/csv' ) :

echo 'The file you tried to upload is not a csv';

endif; */