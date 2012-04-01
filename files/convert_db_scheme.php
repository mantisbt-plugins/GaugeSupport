<?php
/* This file converts Gauge Support's existing table format into the one used by post-0.13
  PROCEDURE:
	- Make backups of your database
	- Uninstall Gauge Support <= 0.13. This should NOT delete its database table.
	- Find the database table, rename it.
	- Execute >>> DELETE FROM mantis_config_table WHERE config_id = 'plugin_GaugeSupport_schema'; <<< on your database, adjust mantis_config_table if necessary.
	- Install Gauge Support 0.14+. This should generate a new, empty database table.
	- Enter all DB/table data below.
	- Run this script.
*/

#access DB
#read out current scores
#unserialize
#create new table
#insert proper data into table
#kill old table

define("DB_SERVER", "YOUR DATABASE SERVER");
define("DB_USER", "YOUR DATABASE USER");
define("DB_PASS", "YOUR DATABASE PASSWORD");
define("DB_NAME", "YOUR DATABASE NAME");
define("DB_TABLE_OLD", "mantis_plugin_GaugeSupport_support_data_table_old"); // this may need fixing
define("DB_TABLE_NEW", "mantis_plugin_GaugeSupport_support_data_table"); // this may need fixing

// weighting for previous values
$weight['do_it_now'] = 2;
$weight['do_it_later'] = 1;
$weight['do_it_last'] = -1;
$weight['do_it_never'] = -2;

// Connecting, selecting database
$link = mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die('Could not connect: ' . mysql_error());
echo '<p>Connected successfully</p>';
mysql_select_db(DB_NAME) or die('Could not select database');

// Performing SQL query
$query = 'SELECT * FROM ' . DB_TABLE_OLD . ';';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

// Updating the new table
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$data = unserialize($line['data']);
	foreach($data as $user => $stance) {
		$qr = "INSERT IGNORE INTO " .DB_TABLE_NEW. " (bugid, userid, rating) VALUES (".$line['bugid'].", ".$user.", ".$weight[$stance].");";
		mysql_query($qr) or die("<p>Writing ".$line['bugid'].", ".$user.", ".$weight[$stance]." failed: ". mysql_error() . "</p>\n");
	}
}
echo DB_TABLE_NEW." should be filled!";

// Free resultset
mysql_free_result($result);

// Closing connection
mysql_close($link);
