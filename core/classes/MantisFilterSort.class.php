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
 * MantisFilterSortField
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
class MantisFilterSort extends MantisFilter {
	private $sort_field = array();
	private $sort_direction = array();

	public function __construct( $p_field_name, $p_filter_input=null ) {
		$this->field = $p_field_name;
		$this->title = 'sort_label';
		$this->template = 'sort';
		$this->default = array( FILTER_PROPERTY_SORT_FIELD_NAME=>'last_updated', FILTER_PROPERTY_SORT_DIRECTION=>'DESC');

		if( is_null( $p_filter_input ) || empty( $p_filter_input ) ) {
			$this->sort_field[] = $this->default[FILTER_PROPERTY_SORT_FIELD_NAME];
			$this->sort_direction[] = $this->default[FILTER_PROPERTY_SORT_DIRECTION];
			$this->filter_value[] = array( FILTER_PROPERTY_SORT_FIELD_NAME=>$this->default[FILTER_PROPERTY_SORT_FIELD_NAME], FILTER_PROPERTY_SORT_DIRECTION=>$this->default[FILTER_PROPERTY_SORT_DIRECTION]);
		} else {
			foreach( $p_filter_input AS $t_sort ) {
				$this->sort_field[] = $t_sort[FILTER_PROPERTY_SORT_FIELD_NAME];
				$this->sort_direction[] = $t_sort[FILTER_PROPERTY_SORT_DIRECTION];
				$this->filter_value[] = array( FILTER_PROPERTY_SORT_FIELD_NAME=>$t_sort[FILTER_PROPERTY_SORT_FIELD_NAME], FILTER_PROPERTY_SORT_DIRECTION=>$t_sort[FILTER_PROPERTY_SORT_DIRECTION]);
			}
		}
	}

	public function __get( $p_field ) {
		switch( $p_field ) {
			case 'sort_field':
				return $this->sort_field;
			break;
			case 'sort_direction':
				return $this->sort_direction;
			break;
			default:
				return parent::__get( $p_field );	
			break;
		}
	}

	/**
	 *	Get and normalize any POST/GET value(s) sent for this field
	 *	Assign the result to the value member if no value is sent, use
	 *	the existing value as the default.
	 *	@return bool true if value is valid, false if not
	 */
	public function processGPC() {
		gpc_make_array( FILTER_PROPERTY_SORT_FIELD_NAME );
		gpc_make_array( FILTER_PROPERTY_SORT_DIRECTION );

		$this->sort_field = gpc_get_string_array( FILTER_PROPERTY_SORT_FIELD_NAME, array() );
		$this->sort_direction = gpc_get_string_array( FILTER_PROPERTY_SORT_DIRECTION, array() );
		$this->filter_value = array();
		$t_count = count( $this->sort_field );
		for( $i=0; $i < $t_count; $i++ ) {
			$t_sort = $this->sort_field[$i];
			$t_dir = $this->sort_direction[$i];
			$this->filter_value[] = array( FILTER_PROPERTY_SORT_FIELD_NAME=>$t_sort, FILTER_PROPERTY_SORT_DIRECTION=>$t_dir );
		}
	}

	public function validate() {
		$t_filter = $this->bug_filter;

		$t_fields = helper_get_columns_to_view();
		$t_count = count( $t_fields );
		for( $i = 0; $i < $t_count; $i++ ) {
			if( isset( $t_fields[$i] ) && in_array( $t_fields[$i], array( 'selection', 'edit', 'bugnotes_count', 'attachment' ) ) ) {
				unset( $t_fields[$i] );
			}
		}

		$t_count = count( $this->filter_value );
		for( $i=0; $i < $t_count; $i++ ) {
			$t_sort = $this->filter_value[$i];
			$t_field_name = $t_sort[FILTER_PROPERTY_SORT_FIELD_NAME];
			$t_dir = $t_sort[FILTER_PROPERTY_SORT_DIRECTION];
			$t_drop = false;
			$t_is_custom_field = ( strpos( $t_field_name, 'custom_' ) === 0 );
			if( $t_is_custom_field ) {
				$t_field = $t_filter->getField( $t_field_name );
				$t_cf_name = 'custom_' . strtolower( $t_field->field_info['name'] );
			}

			if( ( !in_array( $t_cf_name, $t_fields ) && !in_array( $t_field_name, $t_fields ) ) || ( !in_array( $t_dir, array( "ASC", "DESC" ) ) ) ) {
				unset( $this->filter_value[$i] );
				continue;
			}
		}
	}

	public function urlEncodeField() {
		if( is_array( $this->filter_value ) ) {	
			foreach( $this->filter_value AS $t_sort ) {
				$t_query_array[] = urlencode( $this->htmlSortFieldName() ) . '=' . urlencode( $t_sort[FILTER_PROPERTY_SORT_FIELD_NAME] );
				$t_query_array[] = urlencode( $this->htmlSortDirectionName() ) . '=' . urlencode( $t_sort[FILTER_PROPERTY_SORT_DIRECTION] );
			}
			return implode( $t_query_array, '&' );
		}
		return '';
	}

	public function options() {
		# get all of the displayed fields for sort, then drop ones that
		#  are not appropriate and translate the rest
		$t_fields = helper_get_columns_to_view();

		$t_count = count( $t_fields );
		for( $i = 0;$i < $t_count;$i++ ) {
			if( !in_array( $t_fields[$i], array( 'selection', 'edit', 'bugnotes_count', 'attachment' ) ) ) {
				if( strpos( $t_fields[$i], 'custom_' ) === 0 ) {
					$t_field_name = str_replace( 'custom_','',$t_fields[$i] );
					$t_cf_id = custom_field_get_id_from_name( $t_field_name );
					$t_field_name = ucfirst( string_display( lang_get_defaulted( $t_field_name ) ) );
					$t_key = 'custom_field_' . $t_cf_id;
				} else {
					$t_field_name = string_get_field_name( $t_fields[$i] );
					$t_key = $t_fields[$i];
				}
				$t_options[] = array( 'label'=>$t_field_name, 'value'=>$t_key );
			}
		}
		return $t_options;
	}

	public function directionOptions() {
		$t_options[] = array( 'label'=>lang_get( 'bugnote_order_asc' ), 'value'=>'ASC' );
		$t_options[] = array( 'label'=>lang_get( 'bugnote_order_desc' ), 'value'=>'DESC' );

		return $t_options;	
	}

	public function display() {
		$t_filter = $this->bug_filter;
		$t_count = count( $this->sort_field );
		for( $i=0; $i<$t_count; $i++ ) {
			$t_sort = $this->sort_field[$i];
			$t_dir = $this->sort_direction[$i];

			if( strpos( $this->sort_field[$i], 'custom_' ) === 0 ) {
				$t_cf_id = str_replace( 'custom_field_','',$this->sort_field[$i] );
				$t_cf_field = $t_filter->getField( $this->sort_field[$i] );	
				$t_field_name = ucfirst( string_display( lang_get_defaulted( $t_cf_field->field_info['name'] ) ) );
				$t_key = 'custom_field_' . $t_cf_id;
			} else {
				$t_field_name = string_get_field_name( $this->sort_field[$i] );
				$t_key = $this->sort_field[$i];
			}
		
			$t_values[] = array( 'name'=>$this->htmlSortFieldName(), 'value'=>string_attribute( $t_key ) );
			$t_values[] = array( 'name'=>$this->htmlSortDirectionName(), 'value'=>string_attribute( $t_dir ) );
			$t_dir = ( $t_dir == 'ASC' ? lang_get( 'bugnote_order_asc' ) : lang_get( 'bugnote_order_desc' ) );
			$t_labels[] = string_html_entities( $t_field_name ) . ' ' . string_html_entities( $t_dir );
		}
		$t_display['values'] = $t_values;
		$t_display['labels'] = $t_labels;

		return $t_display;
	}

	/**
	 *	Build the SQL query elements 'join', 'where', and 'params'
	 *	as used by MantisBugFilter to create the filter query.
	 */
	public function query() {
		$t_filter = $this->bug_filter;	

		$t_plugin_columns = columns_get_plugin_columns();

		if ( $t_filter->showSticky() ) {
			$t_filter->addQueryElement( 'order_clauses', "{$t_filter->tables['bug']}.sticky DESC" );
		}
		$t_count = count( $this->sort_field );
		for( $i = 0; $i < $t_count; $i++ ) {
			$t_sort = $this->sort_field[$i]; 
			$t_dir = $this->sort_direction[$i]; 

			if( strpos( $t_sort, 'custom_' ) === 0 ) {
				/**
				 *	@todo evaluate how custom fields are sorted.  We already have the data
				 *		for custom fields in the filter.  No need to requery but we may need
				 *		to store the sort field in the filter as custom_field_{id} rather than
				 *		custom_field_{name}
				 */
				#$t_custom_field = utf8_substr( $t_sort, utf8_strlen( 'custom_field_' ) );
				$t_custom_field = str_replace( 'custom_field_' , '', $t_sort );
				$t_custom_field = str_replace( 'custom_' , '', $t_custom_field );
				if( is_numeric( $t_custom_field ) ) {
					$t_custom_field_id = $t_custom_field;
				} else {
					$t_custom_field_id = custom_field_get_id_from_name( $t_custom_field );
				}
				$t_field = $t_filter->getField( 'custom_field_'. $t_custom_field_id );
				$t_field->sortParams();	
			#	$t_cf_alias = str_replace( ' ', '_', $t_custom_field );
			#	$t_cf_table_alias = 'cf_string' . $t_custom_field_id;
			#	$t_cf_select = "$t_cf_table_alias.value $t_cf_alias";

			#	$t_select_clauses = $t_filter->select_clauses;
				# check to be sure this field wasn't already added to the query.
			#	if( !in_array( $t_cf_select, $t_select_clauses ) ) {
			#		$t_filter->addQueryElement( 'select_clauses', $t_cf_select ); 
			#		$t_filter->addQueryElement( 'join_clauses', "LEFT JOIN {$t_filter->tables['cf_string']} $t_cf_table_alias ON {$t_filter->tables['bug']}.id  = $t_cf_table_alias.bug_id AND $t_cf_table_alias.field_id = $t_custom_field_id" );
			#	}
			#	$t_filter->addQueryElement( 'order_clauses', "$t_cf_alias $t_dir" ); #@todo this might need the table alias also
			} else if ( isset( $t_plugin_columns[ $t_sort ] ) ) {
				$t_column_object = $t_plugin_columns[ $t_sort ];
				if ( $t_column_object->sortable ) {
					$t_clauses = $t_column_object->sortquery( $t_dir );
					if ( is_array( $t_clauses ) ) {
						if ( isset( $t_clauses['join'] ) ) {
							$t_filter->addQueryElement( 'join_clauses', $t_clauses['join'] ); 
						}
						if ( isset( $t_clauses['order'] ) ) {
							$t_filter->addQueryElement( 'order_clauses', $t_clauses['order'] ); 
						}
					}
				}
			} else {
				# standard column
				if( $t_filter->fieldExists( $t_sort ) ) {
					$t_field = $t_filter->getFields( $t_sort );
					$t_field->sortParams();
				} else {
					$t_filter->addQueryElement( 'order_clauses', "{$t_filter->tables['bug']}.$t_sort $t_dir" );
				}
			}
		}

		# add basic sorting if necessary
		if( !in_array( 'last_updated', $this->sort_field ) ) {
			$t_filter->addQueryElement( 'order_clauses', "{$t_filter->tables['bug']}.last_updated DESC" ); 
		}
		if( !in_array( 'date_submitted', $this->sort_field ) ) {
			$t_filter->addQueryElement( 'order_clauses', "{$t_filter->tables['bug']}.date_submitted DESC" ); 
		}
	}

	public function getSortableColumnHeadings( $p_columns, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
		$t_valid_sort_directions = array( 'ASC', 'DESC' );
		$t_filter = $this->bug_filter;
		foreach( $p_columns AS $t_column ) {
			if( strpos( $t_column, 'custom_' ) !== false ) {
				$t_cf_name = str_replace( 'custom_','',$t_column );
				$t_cf_id = custom_field_get_id_from_name( $t_cf_name );
				$t_column = 'custom_field_' . $t_cf_id;
			}
			$t_sort_index = array_search( $t_column, $this->sort_field );
			$t_is_sorted = ( $t_sort_index !== false ? true : false );

			# defaults
			$t_sort_field = $t_column;
			if( !$t_is_sorted ) {
				$t_sort_direction = 'ASC';
			} else {
				$t_sort_direction = $this->sort_direction[$t_sort_index] == 'DESC' ? 'ASC' : 'DESC';
			}

			switch( $t_column ) {
				case 'bugnotes_count':
					$t_label = lang_get_defaulted( '#' );
					$t_url = '';
				break;
				case 'selection':
					$t_label = '';
					$t_url = '';
				break;
				case 'edit':
					$t_label = '';
					$t_url = '';
				break;
				default:
					if( $t_filter->fieldExists( $t_column ) ) {
						$t_field = $t_filter->getField( $t_column );
						# the lang string stored with the filter classes is for the punctuation inluded. 
						# For the heading, remove the _label portion of the string if it exists
						$t_label = $t_field->__get( 'column_title' );
						# don't use the html field name for the url.
						$t_sort_field = $t_field->field;

						if( !$t_is_sorted ) {
							$t_sort_direction = in_array( $t_field->default_sort_direction, $t_valid_sort_directions ) ? $t_field->default_sort_direction : 'ASC';
						} else {
							$t_sort_direction =  $this->sort_direction[$t_sort_index] == 'DESC' ? 'ASC' : 'DESC';
						}
					} else {
						$t_label = lang_get_defaulted( $t_column );
					}
					if( $p_columns_target == COLUMNS_TARGET_PRINT_PAGE ) {
						$t_url = "view_all_set.php?sort=$t_sort_field&dir=$t_sort_direction&type=2&print=1";
					} else if( $p_columns_target == COLUMNS_TARGET_VIEW_PAGE ) {
						$t_url  = "view_all_set.php?sort=$t_sort_field&dir=$t_sort_direction&type=2";
					} else {
						$t_url = '';
					}
				break;
			}
			$t_sort_field = rawurlencode( $t_sort_field );
			$t_column_heading_data[] = array( 'url'=>$t_url, 'label'=>$t_label, 'sorted'=>$t_is_sorted, 'direction'=>$this->sort_direction[$t_sort_index] );
		}

		return $t_column_heading_data;
	}

	public function htmlSortFieldName() {
		return string_attribute( FILTER_PROPERTY_SORT_FIELD_NAME . '[]' );
	}

	public function htmlSortDirectionName() {
		return string_attribute( FILTER_PROPERTY_SORT_DIRECTION . '[]'  );
	}
}
