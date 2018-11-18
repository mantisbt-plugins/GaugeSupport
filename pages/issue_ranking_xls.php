<?php
require_once( '../../../core.php' );
$t_core_path = config_get( 'core_path' );
require_once( $t_core_path.'current_user_api.php' );
require_once( $t_core_path.'bug_api.php' );
require_once( $t_core_path.'date_api.php' );
require_once( $t_core_path.'icon_api.php' );
require_once( $t_core_path.'string_api.php' );
require_once( $t_core_path.'columns_api.php' ); 
require_once( $t_core_path.'plugin_api.php' ); 
	$project['name'] = project_get_name(helper_get_current_project()); // fucking PHP doesn't accept static variables initialized by functions x_x
	$project['id'] = helper_get_current_project();

	// craft WHERE
	$where_clause = $project['id'] > 0 ? "WHERE b.project_id = ".$project['id'] : "";
	$skipresolution = config_get( 'plugin_GaugeSupport_gaugesupport_excl_resolution' );
	$skipThese1 = "b.resolution NOT IN (" . implode(",", array($skipresolution)) . ")";

	$skipstatus = config_get( 'plugin_GaugeSupport_gaugesupport_excl_status' );
	$skipThese2 = "b.status NOT IN (" . implode(",", array($skipstatus)) . ")";



	if(strlen($where_clause) < 1) {
		$where_clause = "WHERE {$skipThese1} AND {$skipThese2}" ;
	} else {
		$where_clause .= " AND {$skipThese1} AND {$skipThese2}" ;
	}

	// fetch collected data from DB
	if(config_get_global( 'db_type' ) == "mysqli")
	{
		$dbquery= "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))";
		//no need to store query result if you do not use it (cn)
		db_query($dbquery); 	
	}
	$plugin_table = plugin_table("support_data","GaugeSupport");
	$bug_table = db_get_table('mantis_bug_table');
	$db_query = get_vote_overview(); 
//	 echo "<p>$dbquery</p>";
//	die();
	$dboutput = db_query($dbquery);
	$noOfRowsWeGot = db_num_rows($dboutput);
	if ($noOfRowsWeGot==0){
		print_successful_redirect( 'my_view_page.php' );
	}
	$resultset = array();
	// load listable issues into array
	while($row = db_fetch_array($dboutput)) {
		$row_bug_id = intval($row['bugid']);
		$resultset[$row_bug_id] = array();
		$resultset[$row_bug_id]['ratings'] = array();
		$resultset[$row_bug_id]['ratings'][-2] = array('count' => $row['bm2_count'], 'sum' => $row['bm2_sum']);
		$resultset[$row_bug_id]['ratings'][-1] = array('count' => $row['bm1_count'], 'sum' => $row['bm1_sum']);
		$resultset[$row_bug_id]['ratings'][1] = array('count' => $row['b1_count'], 'sum' => $row['b1_sum']);
		$resultset[$row_bug_id]['ratings'][2] = array('count' => $row['b2_count'], 'sum' => $row['b2_sum']);
		$resultset[$row_bug_id]['no_of_ratings'] = $row['no_of_ratings'];
		$resultset[$row_bug_id]['sum_of_ratings'] = $row['sum_of_ratings'];
		$resultset[$row_bug_id]['avg_rating'] = $row['avg_rating'];
		$resultset[$row_bug_id]['highest_rating'] = $row['highest_rating'];
		$resultset[$row_bug_id]['lowest_rating'] = $row['lowest_rating'];
	}
$topic = "Most supported " . $project['name'] . " issues " ;	

$t_export_title = "Issue_Ranking_excel";
$t_export_title = ereg_replace( '[\/:*?"<>|]', '', $t_export_title );

# Make sure that IE can download the attachments under https.
header( 'Pragma: public' );
header( 'Content-Type: application/vnd.ms-excel' );
header( 'Content-Disposition: attachment; filename="' . $t_export_title . '.xls"' );
  
?>

// ==== PAGE GENERATION STARTS HERE ====

<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<style id="Classeur1_16681_Styles">
</style>
<div id="Classeur1_16681" align=center x:publishsource="Excel">
<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style='border-collapse:collapse'>
<tr>
<td class=xl2316681 style='border-left:none'>bug-id</td>
<td class=xl2316681 style='border-left:none'>summary</td>
<td class=xl2316681 style='border-left:none'>Total Ratings</td>
<td class=xl2316681 style='border-left:none'>Absolute Community Support</td>
<td class=xl2316681 style='border-left:none'>Average Support per User</td>
<td class=xl2316681 style='border-left:none'>Highest rating</td>
<td class=xl2316681 style='border-left:none'>Lowest Rating</td>
</tr>
<?php
foreach($resultset as $bugid => $data) {
	$bug = bug_get($bugid);
	$countval['high'] = array_key_exists(2, $data['ratings']) ? $data['ratings'][2]['count'] : 0;
	$countval['normal'] = array_key_exists(1, $data['ratings']) ? $data['ratings'][1]['count'] : 0;
	$countval['low'] = array_key_exists(-1, $data['ratings']) ? $data['ratings'][-1]['count'] : 0;
	$countval['none'] = array_key_exists(-2, $data['ratings']) ? $data['ratings'][-2]['count'] : 0;
?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $bug->id ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $bug->summary ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['no_of_ratings'] ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['sum_of_ratings'] ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['avg_rating'] ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['highest_rating'] ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['lowest_rating'] ?></td>
	</tr>
	<?php
}
?>
</table>
</div>
