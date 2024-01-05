<?php
/**
 * GaugeSupport - a MantisBT plugin allowing users to vote on issues.
 *
 * Copyright (c) 2010  Charly Kiendl
 * Copyright (c) 2017  Cas Nuy
 * Copyright (c) 2019  Damien Regad
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

form_security_validate( 'GaugeSupport_submit_vote' );

$f_vote = gpc_get_string( 'vote', '' );
$t_cast_vote = $f_vote != 'withdraw';

$f_bug_id = gpc_get_int( 'bugid' );

$t_user_id = auth_get_current_user_id();
$t_table = plugin_table( 'support_data', 'GaugeSupport' );

# Delete user's current vote
$t_query = "DELETE FROM $t_table
		WHERE bugid = " . db_param() . " AND userid = " . db_param();
$t_param = array( $f_bug_id, $t_user_id );
db_query( $t_query, $t_param );

if( $t_cast_vote ) {
	$f_stance = gpc_get_int( 'stance' );

	$t_query = "INSERT INTO {$t_table} (bugid, userid, rating) 
		VALUES (" . db_param() . "," . db_param() . "," . db_param() . ")";
	$t_param = array(
		$f_bug_id,
		$t_user_id,
		$f_stance
	);
	$t_result = db_query( $t_query, $t_param );
}

print_header_redirect( 'view.php?id=' . $f_bug_id );
