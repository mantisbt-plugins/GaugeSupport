<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin( 'config_page.php' );
print_manage_menu( 'manage_plugin_page.php' );
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">

<form action="<?php echo plugin_page( 'config_edit' ) ?>" method="post">
	<?php echo form_security_field( 'GaugeSupport_config' ); ?>

	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-text-width"></i>
				<?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config_title' ) ?>
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
							<td class="category width-30">
								<label for="<?php echo $t_name; ?>">
									<?php echo plugin_lang_get( $t_name ) ?>
								</label>
							</td>
							<td>
								<select id="<?php echo $t_name; ?>" name="<?php echo $t_name; ?>[]"
										class="input-sm" size="6" multiple <?php echo helper_get_tab_index() ?> >
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
				<button name="action" type="submit"
						class="btn btn-primary btn-white btn-round">
					<?php echo lang_get( 'change_configuration' ); ?>
				</button>
				<button name="action" type="submit" value="reset"
						class="btn btn-primary btn-white btn-round">
					<?php echo plugin_lang_get( 'config_reset' ); ?>
				</button>
				<?php print_link_button( 'manage_plugin_page.php', lang_get( 'go_back' ) ); ?>
			</div>
		</div>
	</div>
</form>

</div>
</div>

 <?php
layout_page_end();
