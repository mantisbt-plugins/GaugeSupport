<?php
require_once(__DIR__ . "/../queries.php");

$bugId = gpc_get_int('bugid');
$stance = gpc_get_int('stance');
$dbtable = plugin_table("support_data","GaugeSupport");
$dbquery = insert_vote();
$dboutput = db_query_bound($dbquery, array($bugId, current_user_get_field("id"), $stance, $stance));
print_successful_redirect( 'view.php?id=' . $bugId );