<?php
$bugId = gpc_get_int('bugid');
$stance = gpc_get_int('stance');
$dbtable = plugin_table("support_data","GaugeSupport");
$dbquery = "INSERT INTO {$dbtable} (bugid, userid, rating) VALUES (".db_param().",".db_param().",".db_param().") ON DUPLICATE KEY UPDATE rating = ".db_param();
$dboutput = db_query_bound($dbquery, array($bugId, current_user_get_field("id"), $stance, $stance));
print_successful_redirect( 'view.php?id=' . $bugId );