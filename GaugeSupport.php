<?php

class GaugeSupportPlugin extends MantisPlugin {

	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
		$this->page = 'config';
		$this->version = '2.5.0-dev';
		$this->requires = array(
			'MantisCore' => '2.0.0',
			);

		$this->author = 'Cas (based upon Renegade@RenegadeProjects.com)';
		$this->contact = 'Cas@nuy.info';
		$this->url = 'http://www.nuy.info';
	}

	/*** Default plugin configuration.	 */
	function config() {
		return array(
			'gaugesupport_excl_status'			=> '80,90',
			'gaugesupport_incl_severity'		=> '10,50,60,70,80',
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
		$t_excl_status = plugin_config_get( 'gaugesupport_excl_status' );
		$t_excl_resolution = plugin_config_get( 'gaugesupport_excl_resolution' );
		$t_incl_severity = plugin_config_get( 'gaugesupport_incl_severity' );

		$t_where[] = "b.status NOT IN ( {$t_excl_status} )";
		$t_where[] = "b.resolution NOT IN ( {$t_excl_resolution} )";
		$t_where[] = "b.severity IN ( {$t_incl_severity} )";

		$t_where_clause = 'WHERE ' . implode( ' AND ', $t_where );

		# Retrieve rankings from the database
		$t_ratings_table = plugin_table( 'support_data' );
		$t_bug_table = db_get_table( 'bug' );

		$t_query = "SELECT
				max(sd.bugid) as bugid,
				count(sd.rating) as no_of_ratings,
				sum(sd.rating) as sum_of_ratings,
				avg(sd.rating) as avg_rating,
				max(sd.rating) as highest_rating,
				min(sd.rating) as lowest_rating,
				IFNULL(bm2_count,0) AS bm2_count,
				IFNULL(bm2_sum,0) AS bm2_sum,
				IFNULL(bm1_count,0) AS bm1_count,
				IFNULL(bm1_sum,0) AS bm1_sum,
				IFNULL(b2_count,0) AS b2_count,
				IFNULL(b2_sum,0) AS b2_sum,
				IFNULL(b1_count,0) AS b1_count,
				IFNULL(b1_sum,0) AS b1_sum
			FROM {$t_ratings_table} sd
			INNER JOIN {$t_bug_table} b ON sd.bugid = b.id
			LEFT OUTER JOIN (
					SELECT bugid, count(rating) as bm2_count, sum(rating) as bm2_sum 
					FROM {$t_ratings_table} 
					GROUP BY bugid, rating HAVING rating = -2) bm2 
				ON sd.bugid = bm2.bugid
			LEFT OUTER JOIN (
					SELECT bugid, count(rating) as bm1_count, sum(rating) as bm1_sum 
					FROM {$t_ratings_table} 
					GROUP BY bugid, rating 
					HAVING rating = -1) bm1 
				ON sd.bugid = bm1.bugid
			LEFT OUTER JOIN (
					SELECT bugid, count(rating) as b2_count, sum(rating) as b2_sum 
					FROM {$t_ratings_table} 
					GROUP BY bugid, rating HAVING rating = 2) b2 
				ON sd.bugid = b2.bugid
			LEFT OUTER JOIN (
					SELECT bugid, count(rating) as b1_count, sum(rating) as b1_sum 
					FROM {$t_ratings_table} 
					GROUP BY bugid, rating HAVING rating = 1) b1 
				ON sd.bugid = b1.bugid
			{$t_where_clause}
			GROUP BY sd.bugid, bm2_count, bm2_sum, bm1_count, bm1_sum, b2_count, b2_sum, b1_count, b1_sum
			ORDER BY sum(sd.rating) DESC";
		$t_result = db_query( $t_query, $t_param );

		# Store rankings in an array
		$t_data = array();
		
		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_bug_id = intval( $t_row['bugid'] );

			$t_data[$t_bug_id] = array(
				'ratings' => array(
					-2 => array('count' => $t_row['bm2_count'], 'sum' => $t_row['bm2_sum']),
					-1 => array('count' => $t_row['bm1_count'], 'sum' => $t_row['bm1_sum']),
					+1 => array('count' => $t_row['b1_count'], 'sum' => $t_row['b1_sum']),
					+2 => array('count' => $t_row['b2_count'], 'sum' => $t_row['b2_sum']),
				),
				'no_of_ratings' => $t_row['no_of_ratings'],
				'sum_of_ratings' => $t_row['sum_of_ratings'],
				'avg_rating' => $t_row['avg_rating'],
				'highest_rating' => $t_row['highest_rating'],
				'lowest_rating' => $t_row['lowest_rating'],
			);
		}
		
		return $t_data;
	}
}
