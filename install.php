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
