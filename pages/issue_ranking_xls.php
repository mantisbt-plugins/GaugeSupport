<?php
$t_ratings = plugin_get()->getRatings();

$t_export_title = "Issue_Ranking_excel";
$t_export_title = preg_replace( '[\/:*?"<>|]', '', $t_export_title );

# Make sure that IE can download the attachments under https.
header( 'Pragma: public' );
header( 'Content-Type: application/vnd.ms-excel' );
header( 'Content-Disposition: attachment; filename="' . $t_export_title . '.xls"' );


// ==== PAGE GENERATION STARTS HERE ====
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<style id="Classeur1_16681_Styles">
</style>
<div id="Classeur1_16681" align=center x:publishsource="Excel">
<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style='border-collapse:collapse'>
<tr>
<td class=xl2316681 style='border-left:none'>bug-id</td>
<td class=xl2316681 style='border-left:none'>summary</td>
<td class=xl2316681 style='border-left:none'>Total Ratings</td>
<td class=xl2316681 style='border-left:none'>Absolute Community Support</td>
<td class=xl2316681 style='border-left:none'>Average Support per User</td>
<td class=xl2316681 style='border-left:none'>Highest rating</td>
<td class=xl2316681 style='border-left:none'>Lowest Rating</td>
</tr>
<?php
foreach( $t_ratings as $bugid => $data ) {
	$bug = bug_get($bugid);
	$countval['high'] = array_key_exists(2, $data['ratings']) ? $data['ratings'][2]['count'] : 0;
	$countval['normal'] = array_key_exists(1, $data['ratings']) ? $data['ratings'][1]['count'] : 0;
	$countval['low'] = array_key_exists(-1, $data['ratings']) ? $data['ratings'][-1]['count'] : 0;
	$countval['none'] = array_key_exists(-2, $data['ratings']) ? $data['ratings'][-2]['count'] : 0;
?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $bug->id ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $bug->summary ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['no_of_ratings'] ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['sum_of_ratings'] ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['avg_rating'] ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['highest_rating'] ?></td>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'><?php echo $data['lowest_rating'] ?></td>
	</tr>
	<?php
}
?>
</table>
</div>
