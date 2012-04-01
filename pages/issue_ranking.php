<?php
/*
	Gauge Support - a MantisBT plugin allowing users to express their stance on individual issues.
	Copyright (C) 2010  Charly Kiendl

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
#die('This part of ICS is currently being improved. Please check back at a later point in time. (Last touched: 27.01.11)');
	$project['name'] = project_get_name(helper_get_current_project()); // fucking PHP doesn't accept static variables initialized by functions x_x
	$project['id'] = helper_get_current_project();
	
	// generates an issue row from the given issue data
	// $bugId is an int with the issue number
	// $issueSupportData must be one issue's row from $resultset
	function generateIssueRow($bugId, $issueSupportData) {
		// get the issue data from mantis
		$bug = bug_get($bugId);
		$countval['high'] = array_key_exists(2, $issueSupportData['ratings']) ? $issueSupportData['ratings'][2]['count'] : 0;
		$countval['normal'] = array_key_exists(1, $issueSupportData['ratings']) ? $issueSupportData['ratings'][1]['count'] : 0;
		$countval['low'] = array_key_exists(-1, $issueSupportData['ratings']) ? $issueSupportData['ratings'][-1]['count'] : 0;
		$countval['none'] = array_key_exists(-2, $issueSupportData['ratings']) ? $issueSupportData['ratings'][-2]['count'] : 0;
		
		$counttotal = $countval['high'] + $countval['normal'] + $countval['low'] + $countval['none']; // if counttotal is 0 at some point, weird shit is happening
		if(!$counttotal) echo "<strong>".$bugId." ".print_r($issueSupportData, true)." ".print_r($countval, true)."</strong>";
		$width['high'] = $countval['high'] / $counttotal * 100;
		$width['normal'] = $countval['normal'] / $counttotal * 100;
		$width['low'] = $countval['low'] / $counttotal * 100;
		$width['none'] = $countval['none'] / $counttotal * 100;
		
		return '<li class="support_result_issue">
		<h2><a href="/view.php?id='.$bug->id.'">#'.$bug->id.' '.$bug->summary.'</a></h2>
		<!-- <p class="support_result_basics">
			<span class="support_result_submitter">Submitted by <a href="/view_user_page.php?id='.$bug->reporter_id.'">'.user_get_name($bug->reporter_id).'</a></span>
		</p>-->
        <table class="support_result_data">
			<colgroup span="2" class="support_result_data_left"></colgroup>
			<colgroup span="2" class="support_result_data_right"></colgroup>
			<tr>
					<th class="support_result_data_label">Total Ratings:</th>
					<td class="support_result_data_value srd_l">'.$issueSupportData['no_of_ratings'].'</td>
					<td colspan="2" class="support_result_data_value srd_r">
						<table class="support_result_stances">
								<tr> 
										<td class="support_result_full_support info" style="width: '.$width['high'].'%; height: 20px;" title="'.plugin_lang_get('do_it_now').'"></td>
										<td class="support_result_support info" style="width: '.$width['normal'].'%; height: 20px;" title="'.plugin_lang_get('do_it_later').'"></td>
										<td class="support_result_little_support info" style="width: '.$width['low'].'%; height: 20px;" title="'.plugin_lang_get('do_it_last').'"></td>
										<td class="support_result_no_support info" style="width: '.$width['none'].'%; height: 20px;" title="'.plugin_lang_get('do_it_never').'"></td>
								</tr>
						</table>
					</td> 
			</tr>
			<tr>
					<th class="support_result_data_label">'.plugin_lang_get('ACS_elab').':</th>
					<td class="support_result_data_value srd_l"><span class="acs">'.$issueSupportData['sum_of_ratings'].'</span></td>
					<th class="support_result_data_label srd_r">'.plugin_lang_get('ASPU_elab').':</th>
					<td class="support_result_data_value">'.sprintf('%1.2f',$issueSupportData['avg_rating']).'</td>
			</tr>
			<tr> 
					<th class="support_result_data_label">Highest Rating:</th>
					<td class="support_result_data_value srd_l">'.$issueSupportData['highest_rating'].'</td>
					<th class="support_result_data_label srd_r">Lowest Rating:</th>
					<td class="support_result_data_value">'.$issueSupportData['lowest_rating'].'</td>
			</tr>
        </table>

	</li>';
	}

	function helper_projectize_string($l10nstring) {
		global $project;
		return str_ireplace('{project}',$project['name'],plugin_lang_get($l10nstring));
	}

	// ==== CONTENT GENERATION STARTS HERE ====

	// get lookup parameters
	$noOfBugs = gpc_get_int('num', 10);
	$order = (strtolower(gpc_get_string('order', 'desc')) == 'desc') ? 'DESC' : 'ASC';
	$start_ = gpc_get_int('start', 0);
	$start = $start_ . ',';
	// craft WHERE
	$where_clause = $project['id'] > 0 ? "WHERE b.project_id = ".$project['id'] : "";
	$skipThese = "b.resolution NOT IN (" . implode(",", array(FIXED,UNABLE_TO_DUPLICATE,NOT_FIXABLE,NOT_A_BUG,WONT_FIX)) . ")";
	if(strlen($where_clause) < 1) {
		$where_clause = "WHERE {$skipThese} AND b.status != " . CLOSED;
	} else {
		$where_clause .= " AND {$skipThese} AND b.status != " . CLOSED;
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
	ORDER BY sum(sd.rating) {$order}
	LIMIT {$start}{$noOfBugs}";
	//echo "<p>$dbquery</p>";
	//die();
	$dboutput = db_query_bound($dbquery);
	$noOfRowsWeGot = db_num_rows($dboutput);

	$resultset = array();
	// load listable issues into array
	while($row = db_fetch_array($dboutput)) {
		$row_bug_id = intval($row['bugid']);
		if(!is_array($resultset[$row_bug_id])) {
			$resultset[$row_bug_id] = array();
		}
		if(!is_array($resultset[$row_bug_id]['ratings'])) {
			$resultset[$row_bug_id]['ratings'] = array();
		}
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
	
	$selectionForm = '<form action="/plugin.php" method="GET" class="support_pagination_form">
		<label for="order">Order by: </label>
		<label for="order">most supported first</label>
		<input type="radio" name="order" value="desc"'.(($order == 'DESC') ? 'checked="checked"' : '').'>
		<label for="order">least supported first</label>
		<input type="radio" name="order" value="asc"'.(($order == 'ASC') ? 'checked="checked"' : '').'> <span class="support_pagination_form_spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> 
		<label for="num">Number of issues: </label>
		<input type="text" name="num" maxlength="4" size="4" value="'.$noOfBugs.'"> <span class="support_pagination_form_spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> 
		<label for="start">Starting from row:</label>
		<input type="text" name="start" maxlength="4" size="4" value="'.$start_.'">
		<input type="hidden" name="page" value="GaugeSupport/issue_ranking">
		<input type="submit" value="show issues">
	</form>';
	
	$prevStart = ($start_ - $noOfBugs) >= 0 ? ($start_ - $noOfBugs) : 0;
	$prevLink = '<a href="/plugin.php?page=GaugeSupport/issue_ranking&num='.$noOfBugs.'&order='.strtolower($order).'&start='.$prevStart.'">&laquo; previous '.$noOfBugs.'</a>';
	$nextStart = ($noOfRowsWeGot >= $noOfBugs) ? ($start_ + $noOfBugs) : $start_;
	$nextLink = '<a href="/plugin.php?page=GaugeSupport/issue_ranking&num='.$noOfBugs.'&order='.strtolower($order).'&start='.$nextStart.'">next '.$noOfBugs.' &raquo;</a>';
	
	$showPrev = ($start_ > 0);
	$showNext = ($noOfRowsWeGot >= $noOfBugs); // if we got at least as many rows as requested, there's a good chance we're not on the last page yet
	
	$pagination = '';
	
	if($showPrev && $showNext) {
		$pagination = $prevLink . ' | ' . $nextLink;
	} elseif($showPrev) {
		$pagination = $prevLink;
	} elseif($showNext) {
		$pagination = $nextLink;
	}
	
	$pagination = '<p class="support_pagination">' . $pagination . '</p>';

	
	$topic = (($order == "DESC") ? "Most" : "Least") . " supported " . $project['name'] . " issues " . $start_ . " - " . ($start_ + $noOfRowsWeGot - 1);
	
	// ==== PAGE GENERATION STARTS HERE ====
	html_page_top($topic);
	
	// output issues
	echo $selectionForm;
	echo '<h1 class="support_header">'.$topic.'</h1>';
	echo $pagination;
	echo '<ol class="support_result_list">';
	foreach($resultset as $bugid => $data) {
		echo generateIssueRow($bugid, $data);
	}
	echo "</ol>";
	echo $pagination;
	echo $selectionForm;
	
	// finish page rendering
	html_page_bottom();
?>
