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
	form_security_validate( 'camelot' );
	define('ALLOW_ANONS', false);
	
	auth_ensure_user_authenticated();
	if(current_user_is_anonymous() && !ALLOW_ANONS) die();
	$bugId = gpc_get_int('bugid');
	$stance = gpc_get_string('stance');
	

	$dbtable = plugin_table("support_data");
	$dbquery = "SELECT data FROM {$dbtable} WHERE bugid=".db_param();
	$dboutput = db_query_bound($dbquery, array($bugId), 1);

	if($dboutput->fields) { // had count() here, but API "helpfully" returns a row "false" x_x
		$data = unserialize($dboutput->fields['data']);
		$data[current_user_get_field("id")] = $stance;
		$dbquery = "UPDATE {$dbtable} SET data=".db_param()." WHERE bugid=".db_param();
	} else {
		$data = array(current_user_get_field("id") => $stance);
		$dbquery = "INSERT INTO {$dbtable} (data, bugid, project) VALUES (".db_param().",".db_param().",".db_param().")";
	}
	if(isset($data) && isset($dbquery)) db_query_bound($dbquery, array(serialize($data), $bugId, bug_get_field($bugId, 'project_id')));

	html_page_top( "Supporting stance for issue #" . $bugId . " submitted");
	echo '<p style="text-align: center"><strong>Your stance on issue #', $bugId, ' has been saved.</strong> You should be automatically directed back there, if not, <a href="./view.php?id=', $bugId, '">click here to return</a>.</p>';
	
	form_security_purge( 'camelot' );
	print_successful_redirect( './view.php?id=' . $bugId );
	html_page_bottom();
?>
