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
 * MantisFilterInt
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Base class that implements basic filter functionality
 * and integration with MantisBT.
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterRelationship extends MantisFilterString {
	private $relationship_type = -1;
	private $relationship_bug = null;

	public function __construct( $p_field_name, $p_value=null ) {
		$this->field = $p_field_name;
		$this->title = 'bug_relationships_label';
		$this->title = 'bug_relationships';
		$this->default = null;
		$this->template = 'relationship';
		if( $p_value && is_array( $p_value ) && array_key_exists( FILTER_PROPERTY_RELATIONSHIP_TYPE, $p_value ) ) {
			if( !is_null( $p_value[FILTER_PROPERTY_RELATIONSHIP_TYPE] ) ) {
				$this->relationship_type = $p_value[FILTER_PROPERTY_RELATIONSHIP_TYPE];
			}
			$this->relationship_bug = $p_value[FILTER_PROPERTY_RELATIONSHIP_BUG];
			$this->filter_value = array( FILTER_PROPERTY_RELATIONSHIP_TYPE=>$this->relationship_type, FILTER_PROPERTY_RELATIONSHIP_BUG=>$this->relationship_bug );
		}
	}
	/**
	 *	Get and normalize any POST/GET value(s) sent for this field
	 *	Assign the result to the value member if no value is sent, use
	 *	the existing value as the default.
	 *	@return bool true if value is valid, false if not
	 */
	public function processGPC() {
		$this->relationship_bug = gpc_get_string( FILTER_PROPERTY_RELATIONSHIP_BUG, $this->relationship_bug );
		$this->relationship_type = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_TYPE, $this->relationship_type );
		$this->filter_value = array( FILTER_PROPERTY_RELATIONSHIP_TYPE=>$this->relationship_type, FILTER_PROPERTY_RELATIONSHIP_BUG=>$this->relationship_bug );
	}

	/**
	 * Build the SQL query elements 'join', 'where', and 'params'
	 */
	public function query() {
		$t_filter = $this->bug_filter;
		# isAny for relationships means that neither the type or bug is set.
		if( !$this->isAny() ) {
			# use the complementary type
			$t_comp_type = relationship_get_complementary_type( $this->relationship_type );
			$t_clauses = array();
			$t_alias = 'relationship';

			/* @todo... don't think this is going to work correctly */
			if( $this->relationship_type > 0 && $this->relationship_bug == 0 ) {
				# query for all bugs with the selected relationship
				$t_filter->addQueryElement( 'join_clauses',  "LEFT JOIN {$t_filter->tables['bug_relationship']} $t_alias ON {$t_filter->tables['bug']}.id = $t_alias.destination_bug_id AND $t_alias.relationship_type=" . $t_comp_type );
				$t_filter->addQueryElement( 'join_clauses',  "LEFT JOIN {$t_filter->tables['bug_relationship']} ${t_alias}2 ON {$t_filter->tables['bug']}.id = ${t_alias}2.source_bug_id AND {$t_alias}2.relationship_type=" . $this->relationship_type );
			} else if( $this->relationship_type == 0 && is_numeric( $this->relationship_bug ) && $this->relationship_bug > 0 ) {

			} else {
				$t_filter->addQueryElement( 'join_clauses',  "LEFT JOIN {$t_filter->tables['bug_relationship']} $t_alias ON {$t_filter->tables['bug']}.id = $t_alias.destination_bug_id" );
				$t_filter->addQueryElement( 'join_clauses',  "LEFT JOIN {$t_filter->tables['bug_relationship']} ${t_alias}2 ON {$t_filter->tables['bug']}.id = ${t_alias}2.source_bug_id" );
				// get reverse relationships
				$t_filter->addQueryElement( 'where_params', $t_comp_type );
				$t_filter->addQueryElement( 'where_params', $this->relationship_bug );
				$t_filter->addQueryElement( 'where_params', $this->relationship_type );
				$t_filter->addQueryElement( 'where_params', $this->relationship_bug );

				array_push( $t_clauses, "($t_alias.relationship_type=" . db_param() . " AND $t_alias.source_bug_id=" . db_param() . ')' );
				array_push( $t_clauses, "($t_alias" . "2.relationship_type=" . db_param() . " AND $t_alias" . "2.destination_bug_id=" . db_param() . ')' );
				$t_filter->addQueryElement( 'where_clauses', '(' . implode( ' OR ', $t_clauses ) . ')' );
			}
		}
	}
	public function display() {
		$t_display['values'] = array( 
			array( 'name'=>FILTER_PROPERTY_RELATIONSHIP_BUG, 'value'=>string_attribute( $this->relationship_bug ) ),
			array( 'name'=>FILTER_PROPERTY_RELATIONSHIP_TYPE, 'value'=>string_attribute( $this->relationship_type ) ),
		);

		if( $this->isAny() ) { 
			$t_display['labels'] = lang_get( 'any' );
		} else {
			$t_display['labels'] = relationship_get_description_for_history( $this->relationship_type ) . ' ' . $this->relationship_bug;
		}
		return $t_display;
	}

	/**
	 * For list type filters, define a keyed-array of possible filter options
	 * @return array Filter options keyed by value=>display */
	public function options() {
		global $g_relationships;

		foreach( $g_relationships as $t_type => $t_relationship ) {
			$t_selected = ( $this->relationship_type == $t_type ? ' selected="selected"' : '' );
			$t_label = lang_get( $t_relationship['#description'] );
			$t_option = array( 'label'=>$t_label, 'value'=>$t_type, 'selected'=>$t_selected );
			$t_options[$t_type] = $t_option;
		}
		return $t_options;
	}

	public function isAny() {
		if( ( -1 == $this->relationship_type || $this->relationship_type == META_FILTER_ANY ) && 0 == $this->relationship_bug ) {
			return true;
		}
		return false;
	}
}
