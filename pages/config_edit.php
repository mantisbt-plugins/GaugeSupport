<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
form_security_validate( 'GaugeSupport_config' );

$f_excl_status = gpc_get_string_array( 'excl_status' );
$f_incl_severity = gpc_get_string_array( 'incl_severity' );
$f_excl_resolution = gpc_get_string_array( 'excl_resolution' );

plugin_config_set('gaugesupport_excl_status', implode( ",", $f_excl_status) );
plugin_config_set('gaugesupport_incl_severity', implode( ",", $f_incl_severity) );
plugin_config_set('gaugesupport_excl_resolution', implode( ",", $f_excl_resolution) );

print_successful_redirect( plugin_page( 'config', true ) );
