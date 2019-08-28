<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
layout_page_header( plugin_lang_get( 'plugin_title' ) );
layout_page_begin( 'config_page.php' );
print_manage_menu();
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
<?php
	$t_configs = array(
		'excl_status',
		'incl_severity',
		'excl_resolution',
	);
	foreach( $t_configs as $t_name ) {
		# Retrieve current config and convert array values to integer
		$t_config_id = 'gaugesupport_' . $t_name;
		$t_config_values = array_map(
			'intval',
			explode( ',', plugin_config_get( $t_config_id ) )
		);
		list( , $t_enum ) = explode( '_', $t_name );
?>
		<tr>
			<td class="category" width="30%">
				<?php echo plugin_lang_get( $t_name ) ?>
			</td>
			<td>
				<select id="<?php echo $t_name; ?>" name="<?php echo $t_name; ?>[]"
						class="input-sm" multiple <?php echo helper_get_tab_index() ?> >
					<?php print_enum_string_option_list( $t_enum, $t_config_values ); ?>
				</select>
			</td>
		</tr>
<?php
	}
?>
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