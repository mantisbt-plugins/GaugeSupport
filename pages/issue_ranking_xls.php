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

$t_ratings = plugin_get()->getRatings();

$t_export_title = "Issue_Ranking_excel";
$t_export_title = preg_replace( '[\/:*?"<>|]', '', $t_export_title );

# Make sure that IE can download the attachments under https.
header( 'Pragma: public' );
header( 'Content-Type: application/vnd.ms-excel' );
header( 'Content-Disposition: attachment; filename="' . $t_export_title . '.xls"' );
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
	  xmlns:x="urn:schemas-microsoft-com:office:excel"
	  xmlns="https://www.w3.org/TR/html401/">
<div align=center x:publishsource="Excel">
	<table border=0 cellpadding=0 cellspacing=0 width=100% style='border-collapse:collapse'>
		<tr>
			<td>bug-id</td>
			<td>summary</td>
			<td>Total Ratings</td>
			<td>Absolute Community Support</td>
			<td>Average Support per User</td>
			<td>Highest rating</td>
			<td>Lowest Rating</td>
		</tr>
<?php
foreach( $t_ratings as $bugid => $data ) {
	$bug = bug_get($bugid);
?>
		<tr>
			<td><?php echo $bug->id ?></td>
			<td><?php echo $bug->summary ?></td>
			<td><?php echo $data['no_of_ratings'] ?></td>
			<td><?php echo $data['sum_of_ratings'] ?></td>
			<td><?php echo $data['avg_rating'] ?></td>
			<td><?php echo $data['highest_rating'] ?></td>
			<td><?php echo $data['lowest_rating'] ?></td>
		</tr>
<?php
}
?>
	</table>
</div>
</html>
