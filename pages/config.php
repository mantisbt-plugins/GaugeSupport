<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
layout_page_header( plugin_lang_get( 'plugin_title' ) );
layout_page_begin( 'config_page.php' );
print_manage_menu();

function print_enum_string_option_list1( $p_enum_name, $p_val) {
	$t_config_var_name = $p_enum_name . '_enum_string';
	$t_config_var_value = config_get( $t_config_var_name );

	if( is_array( $p_val ) ) {
		$t_val = $p_val;
	} else {
		$t_val = (int)$p_val;
	}

	$t_enum_values = MantisEnum::getValues( $t_config_var_value );

	foreach ( $t_enum_values as $t_key ) {
		$t_elem2 = get_enum_element( $p_enum_name, $t_key );

		echo '<option value="' . $t_key . '"';
		check_selected1( $t_val, $t_key );
		echo '>' . string_html_specialchars( $t_elem2 ) . '</option>';
	}
} 
function check_selected1( $p_var, $p_val = true, $p_strict = true ) {
	if( is_array( $p_var ) ) {
		foreach ( $p_var as $t_this_var ) {
			if( helper_check_variables_equal( intval($t_this_var), $p_val, $p_strict ) ) {
				echo ' selected="selected"';
				return;
			}
		}
	} else {
		if( helper_check_variables_equal( intval($p_var), $p_val, $p_strict ) ) {
			echo ' selected="selected"';
		}
	}
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
		$current= explode(",", plugin_config_get( 'gaugesupport_excl_status' ));
		echo '<td><select multiple ' . helper_get_tab_index() . ' id="excl_status" name="excl_status[]" class="input-sm">';
		print_enum_string_option_list1( 'status', $current );
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
		$current= explode(",", plugin_config_get( 'gaugesupport_incl_severity' ));
		echo '<td><select multiple ' . helper_get_tab_index() . ' id="incl_severity" name="incl_severity[]" class="input-sm">';
		print_enum_string_option_list1( 'severity', $current );
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
		$current= explode(",", plugin_config_get( 'gaugesupport_excl_resolution' ));
		echo '<td><select multiple ' . helper_get_tab_index() . ' id="excl_resolution" name="excl_resolution[]" class="input-sm">';
		print_enum_string_option_list1( 'resolution', $current );
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