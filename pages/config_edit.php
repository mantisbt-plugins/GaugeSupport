<?php
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
	if( $t_value != $t_configs[$t_config] ) {
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
