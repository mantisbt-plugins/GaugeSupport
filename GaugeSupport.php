<?php

class GaugeSupportPlugin extends MantisPlugin {
	function register() {
		$this->name = 'Gauge Issue Support';
		$this->description = 'This plugin gives community members the option to vote for higher or lower development priority of an issue.';
		$this->page = 'config';
		$this->version = '2.02';
		$this->requires = array(
			'MantisCore' => '2.0.0',
			);

		$this->author = 'Cas (based upon Renegade@RenegadeProjects.com)';
		$this->contact = 'Cas@nuy.info';
		$this->url = 'www.nuy.info';
	}

	/*** Default plugin configuration.	 */
	function config() {
		return array(
			'gaugesupport_excl_status'			=> '80,90' ,
			'gaugesupport_incl_severity'		=> '30,40',
			'gaugesupport_excl_resolution'		=> '20,40,50,60,70,90',
			);
	} 
	
	function init() {
		plugin_event_hook('EVENT_MENU_MAIN' , 'menuLinks');
		plugin_event_hook('EVENT_VIEW_BUG_EXTRA', 'renderBugSnippet');
	}

	function menuLinks($p_event) {
            return array(
                array( 
                    'title' => plugin_lang_get( 'menu_link' ),
                    'access_level' => '',
                    'url' => 'plugin.php?page=GaugeSupport/issue_ranking',
                    'icon' => 'fa-line-chart'
                ),
            ); 
	}

	function renderBugSnippet($p_event, $bugid) {
		include 'plugins/GaugeSupport/pages/gauge_form.php';
	}
	
	function schema() {
		return array(
			array(
				"CreateTableSQL",
				array(
					plugin_table( "support_data" ),
					"
						bugid	I	NOTNULL UNSIGNED PRIMARY,
						userid	I	NOTNULL UNSIGNED PRIMARY,
						rating	I	NOTNULL SIGNED DEFAULT 0
					",
					array( "mysql" => "DEFAULT CHARSET=utf8" )
				),
			)

		);
	}

}