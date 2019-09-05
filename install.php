<?php
/**
 * Install UpdateFunction to convert config names.
 *
 * Config option names were changed in 2.5.0. For each old config,
 * set the new one to the old's value and delete the old.
 *
 * @return int 2 if success
 *
 */
function install_convert_config_names() {
	$t_config_map = array(
		'gaugesupport_excl_status'     => 'excl_status',
		'gaugesupport_excl_resolution' => 'excl_resolution',
		'gaugesupport_incl_severity'   => 'incl_severity',
	);

	# Get config default values
	$t_gaugesupport_plugin = new GaugeSupportPlugin(plugin_get_current());
	$t_default_values = $t_gaugesupport_plugin->config();

	foreach( $t_config_map as $t_old => $t_new ) {
		$t_value = plugin_config_get( $t_old );

		# Set the new value if different than default
		if( isset( $t_default_values[$t_new] ) && $t_value != $t_default_values[$t_new] ) {
			plugin_config_set( $t_new, $t_value );
		}

		plugin_config_delete( $t_old );
	}

	return 2;
}
