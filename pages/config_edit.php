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
form_security_validate( 'GaugeSupport_config' );

$t_reset = gpc_get( 'action', '' ) == 'reset';

$t_redirect_url = 'manage_plugin_page.php';
layout_page_header( null, $t_redirect_url );
layout_page_begin();

# Retrieve all configs
$t_plugin = plugin_get();
$t_configs = $t_plugin->config();

foreach( array_keys( $t_configs ) as $t_config ) {
	$f_value = gpc_get_string_array( $t_config, array() );
	$t_value = implode( ',', $f_value );

	# If config is different than default then set the new value, otherwise delete it
	if( !$t_reset && $t_value != $t_configs[$t_config] ) {
		plugin_config_set( $t_config, $t_value );
	} else {
		plugin_config_delete( $t_config );
	}
}

html_operation_successful(
	$t_redirect_url,
	plugin_lang_get( 'config_updated' )
);
layout_page_end();
