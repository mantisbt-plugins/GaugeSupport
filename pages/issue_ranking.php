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
	$project['name'] = project_get_name(helper_get_current_project()); // fucking PHP doesn't accept static variables initialized by functions x_x
	$project['id'] = helper_get_current_project();
	
	function generateIssueRow($bug) {
		// behold the extensive customization offered!
		// this should probably be moved into the mantis-configuration in the web-frontend
		define('PROPORTION_BAR_WIDTH', 350);
		define('PROPORTION_BAR_HEIGHT', 30);
		define('MIN_WIDTH_FOR_LABEL', 50);
		
		// calculate the width/size of each stance section of the bar
		$width['full'] = (($bug['stance']['do_it_now'] / $bug['userCount']) * PROPORTION_BAR_WIDTH);
		$width['medium'] = (($bug['stance']['do_it_later'] / $bug['userCount']) * PROPORTION_BAR_WIDTH);
		$width['little'] = (($bug['stance']['do_it_last'] / $bug['userCount']) * PROPORTION_BAR_WIDTH);
		$width['none'] = (($bug['stance']['do_it_never'] / $bug['userCount']) * PROPORTION_BAR_WIDTH);
		
		// only show descriptive label if the stance section is wide enough
		$text['full'] = $width['full'] > MIN_WIDTH_FOR_LABEL ? plugin_lang_get('do_it_now') : "" ;
		$text['medium'] = $width['medium'] > MIN_WIDTH_FOR_LABEL ? plugin_lang_get('do_it_later') : "" ;
		$text['little'] = $width['little'] > MIN_WIDTH_FOR_LABEL ? plugin_lang_get('do_it_last') : "" ;
		$text['none'] = $width['none'] > MIN_WIDTH_FOR_LABEL ? plugin_lang_get('do_it_never') : "" ;
		$stanceColumn = '
<table class="support_result_stances" align="center">
	<tr>
		<td width="'.$width['full'].'px" height="'.PROPORTION_BAR_HEIGHT.'px" class="support_result_full_support info" title="'.plugin_lang_get('do_it_now').'">'.$text['full'].'</td>
		<td width="'.$width['medium'].'px" height="'.PROPORTION_BAR_HEIGHT.'px" class="support_result_support info" title="'.plugin_lang_get('do_it_later').'">'.$text['medium'].'</td>
		<td width="'.$width['little'].'px" height="'.PROPORTION_BAR_HEIGHT.'px" class="support_result_little_support info" title="'.plugin_lang_get('do_it_last').'">'.$text['little'].'</td>
		<td width="'.$width['none'].'px" height="'.PROPORTION_BAR_HEIGHT.'px" class="support_result_no_support info" title="'.plugin_lang_get('do_it_never').'">'.$text['none'].'</td>
	</tr>
</table>';
		$row = '
<tr '.helper_alternate_class().'>
	<td class="bugname"><a href="./view.php?id='.$bug['id'].'">'.$bug['name'].'</a></td>
	<td>'.get_enum_element('resolution', $bug['reso']).'</td>
	<td>'.$stanceColumn.'</td>
	<td>'.$bug['totalSupport'].'</td>
	<td>'.$bug['userCount'].'</td>
	<td>'.number_format(($bug['totalSupport'] / $bug['userCount']), 2).'</td>
</tr>';
		return $row;
	}
	
	/* 	We don't want to list issues which have either already been rejected, or are already resolved
		We could probably make the selection configurable via mantis at some point
		Also, it should probably take the curent project into account, as well as accessible projects
		
		...did I mention this is a work in progress?
	*/
	function listThisIssue($issueResolution) {
		switch(intval($issueResolution)) {
			case OPEN: return true;
			case FIXED: return false;
			case REOPENED: return true;
			case UNABLE_TO_DUPLICATE: return false;
			case NOT_FIXABLE: return false;
			case DUPLICATE: return true;
			case NOT_A_BUG: return false;
			case SUSPENDED: return true;
			case WONT_FIX: return false;
			default:  return true;
		}
	}

	function helper_projectize_string($l10nstring) {
		global $project;
		return str_ireplace('{project}',$project['name'],plugin_lang_get($l10nstring));
	}
	
	function echoIssues($order, &$bugs, $count = "all") {
		if(is_numeric($count)) {
			// we only want to show a subset of bugs
			foreach(array_slice($order, 0, $count) as $id) {
				echo generateIssueRow($bugs[$id]);
			}
		} else {
			// we want to show all bugs
			foreach($order as $id) {
				echo generateIssueRow($bugs[$id]);
			}
		}
	}

	// ==== PAGE GENERATION STARTS HERE ====
	html_page_top(helper_projectize_string('menu_link'));

	// table and column headers
	$tableHeader = '<br><table class="width75 support_result_table" align="center"><caption style="font-weight: bold;">{viewmode}</caption><tr><th>'.lang_get('bug').'</th><th>'.lang_get('resolution').'</th><th width="354px">'.plugin_lang_get('sup_sta_dis').'</th><th title="'.plugin_lang_get('ACS_elab').'" class="info">'.plugin_lang_get('ACS_abbr').'</th><th>'.lang_get('users_link').'</th><th title="'.plugin_lang_get('ASPU_elab').'" class="info">'.plugin_lang_get('ASPU_abbr').'</th></tr>';
	
	// fetch collected data from DB
	$dbtable = plugin_table("support_data");
    $dbquery = $project['id'] > 0 ? "SELECT * FROM {$dbtable} WHERE project=$project[id]" : "SELECT * FROM {$dbtable}"; // if a project is set, limit the dataset to that project
	$dboutput = db_query_bound($dbquery);

	$resultset = array();
	while($row = db_fetch_array($dboutput)) {
		$resultset[intval($row['bugid'])] = unserialize($row['data']);
	}
	/*
		$resultset is now an array of arrays, in the form of 
		$resultset[ some issue id ][ some user id ][ that user's stance ]
		where each issue id will have multiple user ids below it, but each user id will only carry one stance
	*/

	$bugsToOutput = array();
	$bugsOrder = array();
	foreach($resultset as $bugid => $supportDataForBug) {
		// get bug data and figure out if we have to process it
		$bug = bug_get($bugid);
		if(!listThisIssue($bug->resolution)) continue;
		
		// weighting values for selections - should probably go into configuration
		$weight['do_it_now'] = 2;
		$weight['do_it_later'] = 1;
		$weight['do_it_last'] = -1;
		$weight['do_it_never'] = -2;
		
		// count the number of instances of each vote
		$bugSupportSum = 0;
		$allValues = array();
		foreach($supportDataForBug as $userStance) {
			// the sum of all weightings, to get the absolute support coefficient
			$bugSupportSum += $weight[$userStance];
			
			// collecting the individual stances, to count them
			array_push($allValues, $userStance);
		}
		// this array holds the bug data associated with the bug id, for output
		$bugsToOutput[$bugid] = array('id' => $bugid,'name' => $bug->summary, 'reso' => $bug->resolution, 'totalSupport' => $bugSupportSum, 'stance' => array_count_values($allValues), 'userCount' => count($supportDataForBug));
		// this array holds the absolute support value associated with the bug id, to sort the bugs by support
		$bugsOrder[$bugid] = $bugSupportSum;
	}
	
	// evaluate arguments and output
//	$noOfBugs = (isset($_GET['num']) && is_numeric($_GET['num'])) ? intval($_GET['num']) : 10; // default no of bugs to show should go into the configuration
	$noOfBugs = isset($_GET['num']) ? $_GET['num'] : 10; // default no of bugs to show should go into the configuration
	
	if(!isset($_GET['show']) || $_GET['show'] == 'top' || $_GET['show'] == 'both') {
		// output table header and column headers
		echo str_ireplace('{viewmode}',helper_projectize_string(is_numeric($noOfBugs) ? 'ranking_pos_title' : 'ranking_title'),$tableHeader);
		
		// sort bugs by support, highest first
		arsort($bugsOrder);
		
		// output issues
		echoIssues(array_keys($bugsOrder), $bugsToOutput, $noOfBugs);
		
		echo "</table>";
		
		if(is_numeric($noOfBugs)) echo '<p class="show_all_links"><a href="'.plugin_page( 'issue_ranking' ).'&amp;show=top&amp;num=all">'.helper_projectize_string('show_all_pos').'</a></p>';
	}
	
	if($_GET['show'] == 'bottom' ||  ( (!isset($_GET['show']) || ($_GET['show'] == 'both')) && (is_numeric($noOfBugs) && (count($bugsOrder) > intval($noOfBugs)) ) ) ) {
		// output table header and column headers
		echo str_ireplace('{viewmode}',helper_projectize_string('ranking_neg_title'),$tableHeader);
		
		// sort bugs by support, lowest first
		asort($bugsOrder);
		
		// output issues
		echoIssues(array_keys($bugsOrder), $bugsToOutput, $noOfBugs);
		
		echo "</table>";
		echo '<p class="show_all_links"><a href="'.plugin_page( 'issue_ranking' ).'&amp;show=bottom&amp;num=all">'.helper_projectize_string('show_all_neg').'</a></p>';
	}
	
	// finish page rendering
	html_page_bottom();
?>
