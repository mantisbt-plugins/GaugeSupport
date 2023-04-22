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

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin( 'config_page.php' );
print_manage_menu( 'manage_plugin_page.php' );

$t_plugin = plugin_get();
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">

<?php if( !$t_plugin->isChartJsAvailable() ) { ?>
<div class="alert alert-warning">
    <?php echo $t_plugin->missingMantisGraph(); ?>
</div>
<?php } ?>

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
	$t_configs = array_keys( $t_plugin->config() );
	foreach( $t_configs as $t_config_id ) {
		# Retrieve current config and convert array values to integer
		$t_config_values = array_map(
			'intval',
			explode( ',', plugin_config_get( $t_config_id ) )
		);

		# Get the corresponding enum
		list( , $t_enum ) = explode( '_', $t_config_id );
?>
						<tr>
							<td class="category width-30">
								<label for="<?php echo $t_config_id; ?>">
									<?php echo plugin_lang_get( $t_config_id ) ?>
								</label>
							</td>
							<td>
								<select id="<?php echo $t_config_id; ?>" name="<?php echo $t_config_id; ?>[]"
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
