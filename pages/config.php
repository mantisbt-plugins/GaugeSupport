<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
layout_page_header( plugin_lang_get( 'plugin_title' ) );
layout_page_begin( 'config_page.php' );
print_manage_menu();

/**
 * Retrieves plugin configuration.
 * Converts array values to integer, to avoid type mismatch errors.
 * @param $p_option
 * @return
 */
function get_current_config( $p_option ) {
    $t_values = explode( ',', plugin_config_get( 'gaugesupport_excl_status' ) );
	return array_map( 'intval', $t_values );
}

?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" > 
<br/>
<form action="<?php echo plugin_page( 'config_edit' ) ?>" method="post">
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-text-width"></i>
		<?php echo plugin_lang_get( 'plugin_title') . ': ' . lang_get( 'plugin_format_config' )?>
	</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive"> 
<table class="table table-bordered table-condensed table-striped"> 
<tr>
	<td class="category" >
		<?php echo plugin_lang_get( 'excl_status' ) ?>
	</td>
	<td>
	<?php
		$current = get_current_config('gaugesupport_excl_status');
		echo '<td><select multiple ' . helper_get_tab_index() . ' id="excl_status" name="excl_status[]" class="input-sm">';
		print_enum_string_option_list( 'status', $current );
		echo '</select></td>'; 		
			?>
	</td>
</tr>
<tr>
	<td class="category" >
		<?php echo plugin_lang_get( 'incl_severity' ) ?>
	</td>
	<td>
	<?php
    	$current = get_current_config('gaugesupport_incl_severity');
		echo '<td><select multiple ' . helper_get_tab_index() . ' id="incl_severity" name="incl_severity[]" class="input-sm">';
		print_enum_string_option_list( 'severity', $current );
		echo '</select></td>'; 
	?>
	</td>
</tr>
<tr>
	<td class="category" >
		<?php echo plugin_lang_get( 'excl_resolution' ) ?>
	</td>
	<td>
	<?php
    	$current = get_current_config('gaugesupport_excl_resolution');
		echo '<td><select multiple ' . helper_get_tab_index() . ' id="excl_resolution" name="excl_resolution[]" class="input-sm">';
		print_enum_string_option_list( 'resolution', $current );
		echo '</select></td>'; 		
	?>
	</td>
</tr>

</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'change_configuration' )?>" />
</div>
</div>
</div>
</form>
</div>
</div>
 <?php
layout_page_end();