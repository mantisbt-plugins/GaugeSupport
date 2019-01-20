<?php
	# ABORT CONDITIONs
	if(current_user_is_anonymous()){
		return;
	}
		
	$current= explode(",", plugin_config_get( 'gaugesupport_excl_resolution' ));
	if(in_array(bug_get_field($bugid, 'resolution'),$current)){
		return;
	}
	$current= explode(",", plugin_config_get( 'gaugesupport_incl_severity' ));
	if(!in_array(bug_get_field($bugid, 'severity'),$current)){
		return;
	}

	$current= explode(",", plugin_config_get( 'gaugesupport_excl_status' ));
	if(in_array(bug_get_field($bugid, 'status'),$current)){
		return;
	}
		$dbtable = plugin_table("support_data");
		$dbquery = "SELECT userid, rating FROM {$dbtable} WHERE bugid=$bugid";
		$dboutput = db_query($dbquery);

		$supporters = array();
		$opponents = array();
		
		// this is a bit ugly, but it was the easiest to add it to the existing code.
		$checked[2] = "";
		$checked[1] = "";
		$checked[-1] = "";
		$checked[-2] = "";

		if($dboutput->RecordCount() > 0) {
		    $data = $dboutput->GetArray();
			
			foreach($data as $row) {
				$row_uid = $row['userid'];
				$row_rating = $row['rating'];
				($row_rating > 0)? $type = &$supporters : $type = &$opponents;
				$class = (user_get_field( $row_uid, 'access_level' ) >= DEVELOPER) ? 'dev' : 'normal';
				array_push($type, '<a href="./view_user_page.php?id='.$row_uid.'" class="'.$class.'">'.user_get_name($row_uid).'</a>');
				
				if($row_uid == current_user_get_field('id')) {
					$checked[$row_rating] = ' checked="checked"';
				}
			}
		}
		$supporters = implode(', ', $supporters); # abusing untyped languages 101
		$opponents = implode(', ', $opponents);
		if(!strlen($supporters)) $supporters = plugin_lang_get('no_supporters');
		if(!strlen($opponents)) $opponents = plugin_lang_get('no_opponents');

		$title = plugin_lang_get('block_title');
		$supportersText = plugin_lang_get('supporters');
		$opponentsText = plugin_lang_get('opponents');
		$submitText = plugin_lang_get('submit_text');
		$highPriorityText = plugin_lang_get('do_it_now');
		$normalPriorityText = plugin_lang_get('do_it_later');
		$minimalPriorityText = plugin_lang_get('do_it_last');
		$noPriorityText = plugin_lang_get('do_it_never');
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" > 
<tr>
<td class="center" colspan="6">
<?php
$colspan=6;
?>
<tr>
</div>
</td>
</tr>
	<form name="voteadding" method="post" action="<?php echo plugin_page('submit_support') ?>">
	<input type="hidden" name="bugid" value="<?php echo $bugid; ?>">
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-text-width"></i>
		<?php echo $title . ': ' ?>
	</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive"> 
<table class="table table-bordered table-condensed table-striped"> 	
		
<tr class="row-category">

<td colspan=6>
<input type="radio" name="stance" value="2"<?php echo $checked[2];?>/> <?php echo $highPriorityText; ?>
	</td><td>
	<input type="radio" name="stance" value="1"<?php echo $checked[1];?>/><?php echo $normalPriorityText; ?>
	</td><td>
	<input type="radio" name="stance" value="-1"<?php echo$checked[-1]; ?>/> <?php echo $minimalPriorityText; ?>
	</td><td>
	<input type="radio" name="stance" value="-2"<?php echo $checked[-2]; ?>/><?php echo $noPriorityText; ?>
</td>
</tr>	
<tr>
<td colspan=4><div align="center">
	<input type="submit" name="submit" value="<?php echo $submitText; ?>">
	</div></td>
		</tr>
<br>
	<tr>
		<td class="category" ><?php echo $supportersText?></td>
		<td colspan=3><?php echo $supporters ?></td>
	</tr>
	<tr>
		<td class="category"><?php echo $opponentsText ?></td>
		<td colspan=3><?php echo $opponents ?></td>
	</tr>
</table>
</div>
</div>
</div>
</div>
</form>
</div>
</div></td>
</tr> 
