<?php
form_security_validate( 'GaugeSupport_submit_vote' );

$f_bug_id = gpc_get_int( 'bugid' );
$f_stance = gpc_get_int( 'stance' );

$t_table = plugin_table( 'support_data', 'GaugeSupport' );
$t_query = "INSERT INTO {$t_table} (bugid, userid, rating) 
	VALUES (" . db_param() . "," . db_param() . "," . db_param() . ") 
	ON DUPLICATE KEY UPDATE rating = " . db_param();
$t_param = array(
	$f_bug_id,
	current_user_get_field( "id" ),
	$f_stance,
	$f_stance
);
$t_result = db_query( $t_query, $t_param );

print_successful_redirect( 'view.php?id=' . $f_bug_id );
