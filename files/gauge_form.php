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

# ABORT CONDITIONs
if(current_user_is_anonymous()){
	return;
}

/**
 * @var GaugeSupportPlugin $t_plugin
 * @var integer $bugid
 */
$t_plugin = plugin_get();
if( !$t_plugin->isVotingAllowed( $bugid ) ) {
	return;
}

# RETRIEVE RATINGS DATA
$t_ratings = array(
	+2 => 'do_it_now',
	+1 => 'do_it_later',
	-1 => 'do_it_last',
	-2 => 'do_it_never',
);

$dbtable = plugin_table("support_data");
$dbquery = "SELECT userid, rating FROM $dbtable WHERE bugid=$bugid";
$dboutput = db_query($dbquery);

$supporters = array();
$opponents = array();
$data = array();
$t_active_rating = 0;
$t_acs = $t_aspu = 0;
$t_stats = array_fill_keys( array_keys( $t_ratings ), 0 );

if( db_num_rows( $dboutput ) ) {
	/**
	 * @TODO retrieving data should be done with MantisBT API, not with ADOdb native methods
	 * @var ADORecordSet $dboutput
	 */
	$data = $dboutput->GetArray();

	foreach($data as $row) {
		$row_uid = $row['userid'];
		$row_rating = $row['rating'];
		$t_stats[$row_rating]++;
		$t_acs += $row_rating;

		($row_rating > 0)? $type = &$supporters : $type = &$opponents;

		$t_user = prepare_user_name( $row_uid );
		# Users with access level >= DEVELOPER are shown in bold
		if(    user_exists( $row_uid )
			&& user_get_field( $row_uid, 'access_level' ) >= DEVELOPER
		) {
			$t_user = "<strong>$t_user</strong>";
		}
		$type[] = $t_user;

		if( $row_uid == auth_get_current_user_id() ) {
			$t_active_rating = (int)$row_rating;
		}
	}
	$t_aspu = $t_acs / count( $data );
}

if( $supporters ) {
	$supporters = implode(', ', $supporters);
} else {
	$supporters = plugin_lang_get( 'no_supporters' );
}
if( $opponents ) {
	$opponents = implode(', ', $opponents);
} else {
	$opponents = plugin_lang_get( 'no_opponents' );
}

# Chart data
$t_chart_labels = json_encode(
	array_map( 'plugin_lang_get', array_values( $t_ratings ) )
);
$t_chart_values = json_encode( array_values( $t_stats ) );

?>

<div class="col-md-12 col-xs-12">
<a id="rating"></a>
<div class="space-10"></div>

<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-text-width"></i>
			<?php echo plugin_lang_get( 'title' ); ?>
		</h4>
	</div>

<?php if( $t_plugin->isChartJsAvailable() ) { ?>
	<div class="padding-8 pull-right position-relative">
		<canvas id="issue_gauge"
				width="400"
				data-labels="<?php echo htmlspecialchars( $t_chart_labels, ENT_QUOTES ) ?>"
				data-values="<?php echo htmlspecialchars( $t_chart_values, ENT_QUOTES ) ?>"
		>
		</canvas>
	</div>
<?php } ?>

	<div class="widget-body">
		<div id="gauge_rankings" class="widget-main no-padding table-responsive">
			<table class="table table-bordered table-condensed table-striped">
				<tr>
					<th class="category width-25">
						<?php echo plugin_lang_get( 'supporters' ); ?>
					</th>
					<td><?php echo $supporters ?></td>
				</tr>
				<tr>
					<th class="category">
						<?php echo plugin_lang_get( 'opponents' ); ?>
					</th>
					<td><?php echo $opponents ?></td>
				</tr>
				<tr>
					<th class="category">
						<?php echo plugin_lang_get( 'rating_count' ); ?>
					</th>
					<td><?php echo count( $data ); ?></td>
				</tr>
				<tr>
					<th class="category">
						<?php echo plugin_lang_get( 'ACS_label' ); ?>
					</th>
					<td><?php echo $t_acs; ?></td>
				</tr>
				<tr>
					<th class="category">
						<?php echo plugin_lang_get( 'ASPU_label' ); ?>
					</th>
					<td><?php echo number_format( $t_aspu, 4 ); ?></td>
				</tr>
			</table>
		</div>
	</div>

	<div class="widget-toolbox padding-8 clearfix form-container">
		<form name="voteadding" method="post" action="<?php echo plugin_page( 'submit_support' ); ?>">
			<?php echo form_security_field( 'GaugeSupport_submit_vote' ); ?>
			<input type="hidden" name="bugid" value="<?php echo $bugid; ?>">

<?php
	foreach( $t_ratings as $value => $label ) {
		$t_input = "stance_$label";
?>
			<label class="inline padding-right-8" for="<?php echo $t_input ?>">
				<input name="stance" id="<?php echo $t_input ?>"
					   type="radio" class="ace input-sm"
					   value="<?php echo $value; ?>"
					   <?php check_checked( $value, $t_active_rating ); ?>
				/>
				<span class="lbl padding-6">
					<?php echo plugin_lang_get( $label ); ?>
				</span>
			</label>
<?php
	}
?>
			<button name="vote" type="submit"
					class="btn btn-primary btn-sm btn-white btn-round">
				<?php echo plugin_lang_get( 'submit_text' ); ?>
			</button>
<?php
	if( $t_active_rating ) {
?>
			<button name="vote" type="submit" value="withdraw"
					class="btn btn-primary btn-sm btn-white btn-round">
				<?php echo plugin_lang_get( 'withdraw' ); ?>
			</button>
<?php
	}
?>
		</form>
	</div>
</div>

</div>
