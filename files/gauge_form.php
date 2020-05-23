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

/** @var integer $bugid */
$t_plugin = plugin_get();
if( !$t_plugin->isVotingAllowed( $bugid ) ) {
	return;
}

# RETRIEVE RATINGS DATA
$dbtable = plugin_table("support_data");
$dbquery = "SELECT userid, rating FROM {$dbtable} WHERE bugid=$bugid";
$dboutput = db_query($dbquery);

$supporters = array();
$opponents = array();
$t_active_rating = 0;

if( db_num_rows( $dboutput ) ) {
	# @TODO retrieving data should be done with MantisBT API
	# not with ADOdb native methods
	$data = $dboutput->GetArray();

	foreach($data as $row) {
		$row_uid = $row['userid'];
		$row_rating = $row['rating'];
		($row_rating > 0)? $type = &$supporters : $type = &$opponents;

		$t_user = prepare_user_name( $row_uid );
		# Users with access level >= DEVELOPER are shown in bold
		if(    user_exists( $row_uid )
			&& user_get_field( $row_uid, 'access_level' ) >= DEVELOPER
		) {
			$t_user = "<strong>$t_user</strong>";
		}
		array_push($type, $t_user );

		if( $row_uid == auth_get_current_user_id() ) {
			$t_active_rating = (int)$row_rating;
		}
	}
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

$t_ratings = array(
	+2 => 'do_it_now',
	+1 => 'do_it_later',
	-1 => 'do_it_last',
	-2 => 'do_it_never',
);
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

	<div class="widget-body">
		<div class="widget-main no-padding table-responsive">
			<table class="table table-bordered table-condensed table-striped">
				<tr>
					<th class="category" width="15%">
						<?php echo plugin_lang_get( 'supporters' ); ?>
					</th>
					<td colspan=3><?php echo $supporters ?></td>
				</tr>
				<tr>
					<th class="category">
						<?php echo plugin_lang_get( 'opponents' ); ?>
					</th>
					<td colspan=3><?php echo $opponents ?></td>
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
