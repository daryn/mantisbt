<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MantisFilterSearch
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Class that implements Text search filter functionality
 * and integration with MantisBT.
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterSearch extends MantisFilterString {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		 parent::__construct( $p_field_name, $p_filter_input );
		$this->title = 'search';
		$this->default = ''; 
		$this->size = 16;
	}

	public function query() {
		# Text search
		if( !is_blank( $this->filter_value ) ) {
			$t_filter = $this->bug_filter;
			$t_bug_table = $t_filter->tables['bug'];
			$t_bug_text_table = $t_filter->tables['bug_text'];
			$t_bugnote_table = $t_filter->tables['bugnote'];
			$t_bugnote_text_table = $t_filter->tables['bugnote_text'];

			# break up search terms by spacing or quoting
			preg_match_all( "/-?([^'\"\s]+|\"[^\"]+\"|'[^']+')/", $this->filter_value, $t_matches, PREG_SET_ORDER );

			# organize terms without quoting, paying attention to negation
			$t_search_terms = array();
			foreach( $t_matches as $t_match ) {
				$t_search_terms[ trim( $t_match[1], "\'\"" ) ] = ( $t_match[0][0] == '-' );
			}

			# build a big where-clause and param list for all search terms, including negations
			$t_first = true;
			$t_textsearch_where_clause = "( ";
			foreach( $t_search_terms as $t_search_term => $t_negate ) {
				if ( !$t_first ) {
					$t_textsearch_where_clause .= ' AND ';
				}

				if ( $t_negate ) {
					$t_textsearch_where_clause .= 'NOT ';
				}

				$c_search = '%' . $t_search_term . '%';
				$t_textsearch_where_clause .= '( ' . db_helper_like( 'summary' ) .
					' OR ' . db_helper_like( "$t_bug_text_table.description" ) .
					' OR ' . db_helper_like( "$t_bug_text_table.steps_to_reproduce" ) .
					' OR ' . db_helper_like( "$t_bug_text_table.additional_information" ) .
					' OR ' . db_helper_like( "$t_bugnote_text_table.note" );

				$t_filter->addQueryElement('where_params', $c_search );
				$t_filter->addQueryElement('where_params', $c_search );
				$t_filter->addQueryElement('where_params', $c_search );
				$t_filter->addQueryElement('where_params', $c_search );
				$t_filter->addQueryElement('where_params', $c_search );

				if( is_numeric( $t_search_term ) ) {
					$c_search_int = (int) $t_search_term;
					$t_textsearch_where_clause .= " OR $t_bug_table.id = " . db_param();
					$t_textsearch_where_clause .= " OR $t_bugnote_table.id = " . db_param();
					$t_filter->addQueryElement('where_params', $c_search_int );
					$t_filter->addQueryElement('where_params', $c_search_int );
				}

				$t_textsearch_where_clause .= ' )';
				$t_first = false;
			}
			$t_textsearch_where_clause .= ' )';

			# add text query elements to arrays
			if ( !$t_first ) {
				$t_filter->addQueryElement('from_clauses', "$t_bug_text_table" );
				$t_filter->addQueryElement('where_clauses', "$t_bug_table.bug_text_id = $t_bug_text_table.id" );
				$t_filter->addQueryElement('where_clauses', $t_textsearch_where_clause );
				$t_filter->addTableJoin( $t_bug_table, $t_bugnote_table, " LEFT JOIN $t_bugnote_table ON $t_bug_table.id = $t_bugnote_table.bug_id" );
#				$t_filter->addQueryElement('join_clauses', " LEFT JOIN $t_bugnote_table ON $t_bug_table.id = $t_bugnote_table.bug_id" );
				$t_filter->addTableJoin( $t_bugnote_table, $t_bugnote_text_table, " LEFT JOIN $t_bugnote_text_table ON $t_bugnote_table.bugnote_text_id = $t_bugnote_text_table.id", "$t_bugnote_table" );
#				$t_filter->addQueryElement('join_clauses', " LEFT JOIN $t_bugnote_text_table ON $t_bugnote_table.bugnote_text_id = $t_bugnote_text_table.id", "$t_bugnote_table" );
			}
		}
	}
}
