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

	@author Charly Kiendl (Renegade@RenegadeProjects.com)
	@license GPL v3
	@description This plugin adds a support block at the bottom of each issue,
	which allows users to express their stance on that particular issue.

	An overview page then sheds light upon the question of which issues have
	which degree of support.

	The intention of this plugin is to give developers the ability to tailor
	their development schedules to the desires of the users, satisfying
	the maximum amount of people as quickly as possible by releasing
	the most-wanted features first, while ignoring the issues the community
	does not support.
	
	
	TODO
	- add configuration options/de-softcode
	- add ACS column option on view issues
	- pagination for list all pages
*/
class GaugeSupportPlugin extends MantisPlugin {
	function register() {
		$this->name = 'Gauge Issue Support';
		$this->description = 'This plugin gives community members the option to vote for higher or lower development priority of an issue.';
		$this->page = '';

		$this->version = '0.14';
		$this->requires = array(
			'MantisCore' => '1.2',
			);

		$this->author = 'Renegade';
		$this->contact = 'Renegade@RenegadeProjects.com';
		$this->url = '';
	}

	function hooks() {
		return array(
			'EVENT_MENU_MAIN' => 'menuLinks',
			'EVENT_VIEW_BUG_EXTRA' => 'renderBugSnippet',
			'EVENT_LAYOUT_RESOURCES' => 'css'
		);
	}

	function menuLinks($p_event) {
		return array('<a href="' . plugin_page( 'issue_ranking' ) . '">'.str_ireplace('{project}',project_get_name(helper_get_current_project()),plugin_lang_get('menu_link')).'</a>');
	}

	function css($p_event) {
		return '<link rel="stylesheet" type="text/css" href="'.plugin_file( 'style.css' ).'">';
	}

	function schema() {
		return array(
			array(
				"CreateTableSQL",
				array(
					plugin_table( "support_data" ),
					"
						bugid	I	NOTNULL UNSIGNED PRIMARY,
						userid	I	NOTNULL UNSIGNED PRIMARY,
						rating	I	NOTNULL SIGNED DEFAULT 0
					",
					array( "mysql" => "DEFAULT CHARSET=utf8" )
				),
			)

		);
	}

	function renderBugSnippet($p_event, $bugid) {
		# ABORT CONDITION
		if(bug_get_field($bugid, 'severity') != FEATURE) return;
		
		$dbtable = plugin_table("support_data");
		$dbquery = "SELECT userid, rating FROM {$dbtable} WHERE bugid=$bugid";
		$dboutput = db_query_bound($dbquery);

		$supporters = array();
		$opponents = array();
		
		// this is a bit ugly, but it was the easiest to add it to the existing code.
		$checked[2] = "";
		$checked[1] = "";
		$checked[-1] = "";
		$checked[-2] = "";

		if($dboutput->RecordCount() > 0) {
		    $data = $dboutput->GetArray();
			
			foreach($data as $row) {
				$row_uid = $row['userid'];
				$row_rating = $row['rating'];
				($row_rating > 0)? $type = &$supporters : $type = &$opponents;
				$class = (user_get_field( $row_uid, 'access_level' ) >= DEVELOPER) ? 'dev' : 'normal';
				array_push($type, '<a href="./view_user_page.php?id='.$row_uid.'" class="'.$class.'">'.user_get_name($row_uid).'</a>');
				
				if($row_uid == current_user_get_field('id')) {
					$checked[$row_rating] = ' checked="checked"';
				}
			}
		}
		$supporters = implode(', ', $supporters); # abusing untyped languages 101
		$opponents = implode(', ', $opponents);
		if(!strlen($supporters)) $supporters = plugin_lang_get('no_supporters');
		if(!strlen($opponents)) $opponents = plugin_lang_get('no_opponents');

		$title = plugin_lang_get('block_title');
		$supportersText = plugin_lang_get('supporters');
		$opponentsText = plugin_lang_get('opponents');
		$submitText = plugin_lang_get('submit_text');
		$highPriorityText = plugin_lang_get('do_it_now');
		$normalPriorityText = plugin_lang_get('do_it_later');
		$minimalPriorityText = plugin_lang_get('do_it_last');
		$noPriorityText = plugin_lang_get('do_it_never');
		$submitPage = plugin_page('submit_support');
		$formSecurity = form_security_field( 'camelot' );
		if(bug_is_resolved($bugid)) {
			$form = '<strong>'.plugin_lang_get('already_resolved').'</strong>';
		} else {
			$form_anon = '<strong>Only registered users can voice their support.</strong> <a href="./signup_page.php">Click here to register</a>, or <a href="./login_page.php?return=/view.php?id='.$bugid.'">here to log in</a>.';
			$form_normal = '<form method="POST" action="'.$submitPage.'" class="support_form">
	<p><input type="radio" name="stance" value="2"'.$checked[2].'> <span class="support_option">'.$highPriorityText.'</span>
	<input type="radio" name="stance" value="1"'.$checked[1].'> <span class="support_option">'.$normalPriorityText.'</span>
	<input type="radio" name="stance" value="-1"'.$checked[-1].'> <span class="support_option">'.$minimalPriorityText.'</span>
	<input type="radio" name="stance" value="-2"'.$checked[-2].'> <span class="support_option">'.$noPriorityText.'</span>
	<input type="hidden" name="bugid" value="'.$bugid.'">'.$formSecurity.'</p>
	<p><input type="submit" name="submit" value="'.$submitText.'"></p>
</form>';
			
			$form = current_user_is_anonymous() ? $form_anon : $form_normal;
		}
		$table = <<<TABLEDATA
<br>
<table class="width100">
	<caption><strong>$title</strong></caption>
	<tr class="row-1">
		<td colspan="2" class="support_form">
			$form
		</td>
	</tr>
	<tr class="row-2">
		<td class="category" width="100px">$supportersText:</td>
		<td class="userlist">$supporters</td>
	</tr>
	<tr class="row-1">
		<td class="category">$opponentsText:</td>
		<td class="userlist">$opponents</td>
	</tr>
</table>
TABLEDATA;
		echo $table;
	}
}
?>
