<?php
form_security_validate( 'GaugeSupport_submit_vote' );

$f_vote = gpc_get_string( 'vote', '' );
$t_withdraw_vote = $f_vote == 'withdraw';

$f_bug_id = gpc_get_int( 'bugid' );
if( !$t_withdraw_vote ) {
	$f_stance = gpc_get_int( 'stance' );
}

$t_user_id = auth_get_current_user_id();
$t_table = plugin_table( 'support_data', 'GaugeSupport' );

if( $t_withdraw_vote ) {
	# Delete user's current vote
	$t_query = "DELETE FROM $t_table 
		WHERE bugid = " . db_param() . " AND userid = " . db_param();
	$t_param = array( $f_bug_id, $t_user_id );
	db_query( $t_query, $t_param );
} else {
	$t_query = "INSERT INTO {$t_table} (bugid, userid, rating) 
		VALUES (" . db_param() . "," . db_param() . "," . db_param() . ") 
		ON DUPLICATE KEY UPDATE rating = " . db_param();
	$t_param = array(
		$f_bug_id,
		$t_user_id,
		$f_stance,
		$f_stance
	);
	$t_result = db_query( $t_query, $t_param );
}

print_successful_redirect( 'view.php?id=' . $f_bug_id );
