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

class GaugeSupportPlugin extends MantisPlugin {

	const VERSION = '2.6.1';

	const MANTISGRAPH = 'MantisGraph';
	const MANTISGRAPH_VERSION = '2.25.0';

	/**
	 * @var bool $mantisgraph_loaded True if MantisGraph plugin is available
	 */
	private $mantisgraph_loaded = false;
	
	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
		$this->page = 'config';
		$this->version = self::VERSION;
		$this->requires = array(
			'MantisCore' => '2.0.0',
			);

		# MantisGraph required so we don't need to bundle chart.js ourselves
		$this->uses = array(
			self::MANTISGRAPH => self::MANTISGRAPH_VERSION,
		);

		$this->author = "Cas (based upon Charly Kiendl's work), Damien Regad";
		$this->contact = 'Cas@nuy.info';
		$this->url = 'https://github.com/mantisbt-plugins/GaugeSupport';
	}

	/*** Default plugin configuration.	 */
	function config() {
		return array(
			'excl_status'     => '80,90',
			'excl_resolution' => '20,40,50,60,70,90',
			'incl_severity'   => '10,50,60,70,80',
			);
	} 
	
	function hooks() {
		return array(
			'EVENT_LAYOUT_RESOURCES' => 'resources',
			'EVENT_MENU_MAIN' => 'menuLinks',
			'EVENT_MENU_ISSUE' => 'issueVoteLink',
			'EVENT_VIEW_BUG_EXTRA' => 'renderBugSnippet',
		);
	}

	/**
	 * Initialize plugin
	 *
	 * Check soft dependency on MantisGraph plugin.
	 */
	public function init() {
		if( plugin_is_registered( self::MANTISGRAPH ) ) {
			$t_version_check = plugin_dependency(
				self::MANTISGRAPH,
				self::MANTISGRAPH_VERSION
			);
			$this->mantisgraph_loaded = $t_version_check == 1;
		}
		if( !$this->mantisgraph_loaded ) {
			log_event( LOG_PLUGIN, $this->missingMantisGraph() );
		}
	}

	/**
	 * @return string
	 */
	public function missingMantisGraph() {
		return sprintf(
			plugin_lang_get( 'mantisgraph_missing' ),
			self::MANTISGRAPH_VERSION
		);
	}

	/**
	 * Include javascript for chart.js if needed.
	 *
	 * Scripts are only loaded on pages that need them (currently, view.php).
	 *
	 * @return void
	 */
	function resources() {
		$t_page = basename( $_SERVER['SCRIPT_FILENAME'] );
		if( $this->mantisgraph_loaded && $t_page == 'view.php' ) {
			$t_mantisgraph = plugin_get( self::MANTISGRAPH );
			/** @var MantisGraphPlugin $t_mantisgraph */
			$t_mantisgraph->include_chartjs();

			echo "\t", '<script src="' . plugin_file( "GaugeSupport.js" ) . '"></script>', "\n";
		}
	}

	/**
	 * @return bool True if ChartJs library is available;
	 */
	public function isChartJsAvailable() {
		return $this->mantisgraph_loaded;
	}

	/**
	 * @noinspection PhpUnused PhpUnusedParameterInspection
	 */
	function menuLinks( $p_event ) {
		return array(
			array(
				'title' => plugin_lang_get( 'menu_link' ),
				'access_level' => '',
				'url' => plugin_page( 'issue_ranking', true ),
				'icon' => 'fa-line-chart'
			),
		);
	}

	/**
	 * Event hook to display the voting button on View Issue page if necessary.
	 *
	 * @param string $p_event  Event ID
	 * @param int    $p_bug_id Bug ID
	 *
	 * @return array
	 *
	 * @noinspection PhpUnused PhpUnusedParameterInspection
	 */
	function issueVoteLink( $p_event, $p_bug_id ) {
		if( $this->isVotingAllowed( $p_bug_id ) ) {
			return array( plugin_lang_get( 'title' ) => '#rating' );
		}
		return array();
	}

	/**
	 * @noinspection PhpUnused PhpUnusedParameterInspection
	 */
	function renderBugSnippet( $p_event, $bugid ) {
		include plugin_file_path( 'gauge_form.php', $this->basename );
	}

	function schema() {
		require_once( __DIR__ . '/install.php' );

		return array(
			0 => array( "CreateTableSQL",
				array(
					plugin_table( "support_data" ),
					"
						bugid	I	NOTNULL UNSIGNED PRIMARY,
						userid	I	NOTNULL UNSIGNED PRIMARY,
						rating	I	NOTNULL SIGNED DEFAULT 0
					",
					array( "mysql" => "DEFAULT CHARSET=utf8" )
				),
			),
			1 => array( 'UpdateFunction', 'convert_config_names' ),
		);
	}

	/**
	 * Retrieve aggregated ratings from the database.
	 *
	 * @return array
	 */
	function getRatings() {
		# Build where clause
		$t_where = array();
		$t_param = array();

		# Project ID filter
		$t_project_id = helper_get_current_project();
		if( $t_project_id != 0 ) {
			$t_where[] = 'b.project_id = ' . db_param();
			$t_param[] = $t_project_id;
		}

		# Config filters
		foreach( array_keys( $this->config() ) as $t_config ) {
			$t_values = plugin_config_get( $t_config );
			list( $t_type, $t_field ) = explode( '_', $t_config );

			# If "include" config does not specify any values, then the query
			# will never return any data so we take a shortcut
			if( $t_type == 'incl' && empty( $t_values ) ) {
				return array();
			}
			if( $t_values ) {
				$t_in = $t_type == 'excl' ? 'NOT IN' : 'IN';
				$t_where[] = "b.$t_field $t_in ($t_values)";
			}
		}

		$t_where_clause = $t_where
			? 'WHERE ' . implode( ' AND ', $t_where )
			: '';

		# Retrieve rankings from the database
		$t_ratings_table = plugin_table( 'support_data' );
		$t_bug_table = db_get_table( 'bug' );

		$t_query = "SELECT
				sd.bugid as bugid,
				count(sd.rating) as no_of_ratings,
				sum(sd.rating) as sum_of_ratings,
				avg(sd.rating) as avg_rating,
				max(sd.rating) as highest_rating,
				min(sd.rating) as lowest_rating
			FROM $t_ratings_table sd
			INNER JOIN $t_bug_table b ON sd.bugid = b.id
			$t_where_clause
			GROUP BY sd.bugid
			ORDER BY sum(sd.rating) DESC, count(sd.rating) DESC, sd.bugid";
		$t_result = db_query( $t_query, $t_param );

		# Store rankings in an array
		$t_data = array();
		
		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_bug_id = intval( $t_row['bugid'] );

			$t_data[$t_bug_id] = array(
				'no_of_ratings' => $t_row['no_of_ratings'],
				'sum_of_ratings' => $t_row['sum_of_ratings'],
				'avg_rating' => $t_row['avg_rating'],
				'highest_rating' => $t_row['highest_rating'],
				'lowest_rating' => $t_row['lowest_rating'],
			);
		}
		
		return $t_data;
	}

	/**
	 * Return true if voting is allowed for the given issue.
	 *
	 * @param int $p_bug_id
	 *
	 * @return bool
	 */
	public function isVotingAllowed( $p_bug_id ) {
		foreach (array_keys($this->config()) as $t_config) {
			$t_values = explode(',', plugin_config_get($t_config));
			list($t_type, $t_field) = explode('_', $t_config);

			$t_is_in_values = in_array(bug_get_field($p_bug_id, $t_field), $t_values);

			if ($t_type == 'incl' xor $t_is_in_values) {
				return false;
			}
		}
		return true;
	}
}
