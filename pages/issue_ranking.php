<?php
	$project['name'] = project_get_name(helper_get_current_project()); // fucking PHP doesn't accept static variables initialized by functions x_x
	$project['id'] = helper_get_current_project();

	// craft WHERE
	$where_clause = $project['id'] > 0 ? "WHERE b.project_id = ".$project['id'] : "";
	$skipresolution = plugin_config_get( 'gaugesupport_excl_resolution' );
	$skipThese1 = "b.resolution NOT IN (" . implode(",", array($skipresolution)) . ")";

	$skipstatus = config_get( 'plugin_GaugeSupport_gaugesupport_excl_status' );
	$skipThese2 = "b.status NOT IN (" . implode(",", array($skipstatus)) . ")";
	
	if(strlen($where_clause) < 1) {
		$where_clause = "WHERE {$skipThese1} AND {$skipThese2} " ;
	} else {
		$where_clause .= " AND {$skipThese1} AND {$skipThese2} " ;
	}
	// fetch collected data from DB
	$plugin_table = plugin_table("support_data");
	$bug_table = db_get_table('mantis_bug_table');
	$dbquery = "SELECT
		max(sd.bugid) as bugid,
		count(sd.rating) as no_of_ratings,
		sum(sd.rating) as sum_of_ratings,
		avg(sd.rating) as avg_rating,
		max(sd.rating) as highest_rating,
		min(sd.rating) as lowest_rating,
		IFNULL(bm2_count,0) AS bm2_count,
		IFNULL(bm2_sum,0) AS bm2_sum,
		IFNULL(bm1_count,0) AS bm1_count,
		IFNULL(bm1_sum,0) AS bm1_sum,
		IFNULL(b2_count,0) AS b2_count,
		IFNULL(b2_sum,0) AS b2_sum,
		IFNULL(b1_count,0) AS b1_count,
		IFNULL(b1_sum,0) AS b1_sum
	FROM {$plugin_table} sd
	INNER JOIN {$bug_table} b ON sd.bugid = b.id
	LEFT OUTER JOIN (SELECT bugid, count(rating) as bm2_count, sum(rating) as bm2_sum FROM {$plugin_table} GROUP BY bugid, rating HAVING rating = -2) bm2 ON sd.bugid = bm2.bugid
	LEFT OUTER JOIN (SELECT bugid, count(rating) as bm1_count, sum(rating) as bm1_sum FROM {$plugin_table} GROUP BY bugid, rating HAVING rating = -1) bm1 ON sd.bugid = bm1.bugid
	LEFT OUTER JOIN (SELECT bugid, count(rating) as b2_count, sum(rating) as b2_sum FROM {$plugin_table} GROUP BY bugid, rating HAVING rating = 2) b2 ON sd.bugid = b2.bugid
	LEFT OUTER JOIN (SELECT bugid, count(rating) as b1_count, sum(rating) as b1_sum FROM {$plugin_table} GROUP BY bugid, rating HAVING rating = 1) b1 ON sd.bugid = b1.bugid
	{$where_clause}
	GROUP BY sd.bugid
	ORDER BY sum(sd.rating) DESC ";
	// echo "<p>$dbquery</p>";
	//die();
	$dboutput = db_query_bound($dbquery);
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
// ==== PAGE GENERATION STARTS HERE ====
layout_page_header( );
layout_page_begin( );
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" > 
<br/>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
<i class="ace-icon fa fa-text-width"></i>
<?php echo plugin_lang_get( 'block_title' ) . ': ' . plugin_lang_get( 'plugin_title' )?>
</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
<tr>
<?php 
echo"==>>";
?>
<a href="plugins/GaugeSupport/pages/issue_ranking_xls.php">XLS-Download</a>
</tr>
<div class="table-responsive"> 
<table class="table table-bordered table-condensed table-striped"> 
<tr>
<td>Bug-id</td>
<td>Summary</td>
<td>Total Ratings</td>
<td>Absolute Community Support</td>
<td>Average Support per User</td>
<td>Highest rating</td>
<td>Lowest Rating</td>
</tr>
<?php
foreach($resultset as $bugid => $data) {
	$bug = bug_get($bugid);
	$countval['high'] = array_key_exists(2, $data['ratings']) ? $data['ratings'][2]['count'] : 0;
	$countval['normal'] = array_key_exists(1, $data['ratings']) ? $data['ratings'][1]['count'] : 0;
	$countval['low'] = array_key_exists(-1, $data['ratings']) ? $data['ratings'][-1]['count'] : 0;
	$countval['none'] = array_key_exists(-2, $data['ratings']) ? $data['ratings'][-2]['count'] : 0;
?>
	<tr>
	<td><a href="view.php?id=<?php echo $bug->id ?>."><?php echo $bug->id ?></td>
	<td><?php echo $bug->summary ?></td>
	<td><?php echo $data['no_of_ratings'] ?></td>
	<td><?php echo $data['sum_of_ratings'] ?></td>
	<td><?php echo $data['avg_rating'] ?></td>
	<td><?php echo $data['highest_rating'] ?></td>
	<td><?php echo $data['lowest_rating'] ?></td>
	</tr>
	<?php
}
?>
</table>
</div>
</div>
<div>
</div>
</div>
</div>
</form>
</div>
</div>	
<?php
layout_page_end();