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
 * MantisFilterTagString
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
class MantisFilterTags extends MantisFilterString {
	private $string;
	private $select;
	private $separator; 

	/**
	 *	@todo figure this out
	 */
	public function __construct( $p_field_name, $p_filter_input=null ) {
		parent::__construct( $p_field_name, $p_filter_input );
		$this->separator = config_get( 'tag_separator' );
		$this->title = 'tags_label';
		$this->template = 'tags';
		$this->size = 40;
		$this->enabled = access_has_global_level( config_get( 'tag_view_threshold' ) );
		$this->string = $p_filter_input[FILTER_PROPERTY_TAG_STRING];
		$this->select = $p_filter_input[FILTER_PROPERTY_TAG_SELECT];
		$this->filter_value = array( FILTER_PROPERTY_TAG_STRING=>$this->string, FILTER_PROPERTY_TAG_SELECT=>$this->select );
	}

	public function __get( $p_field ) {
		switch( $p_field ) {
			case 'separator':
				return $this->separator;
			break;
			case 'string':
				return $this->string;
			break;
			case 'select':
				return $this->select;
			break;
			default:
					return parent::__get( $p_field );
			break;
			
		}
	}

	public function __set( $p_field, $p_value ) {
        switch( $p_field ) {
            case 'filter_value':
                if( $p_value && is_array( $p_value ) && array_key_exists( FILTER_PROPERTY_TAG_STRING, $p_value ) ) {
					$this->filter_value = array( FILTER_PROPERTY_TAG_STRING=>$this->string, FILTER_PROPERTY_TAG_SELECT=>$this->select );
                }
            break;
            default:
                parent::__set( $p_field, $p_value );
            break;
        }
	}

	public function processGPC() {
		$this->string = gpc_get_string( FILTER_PROPERTY_TAG_STRING, '' );
		$this->select = gpc_get_int( FILTER_PROPERTY_TAG_SELECT, '0' );

		$t_tag_string = $this->string;
		if( $this->select != 0 && tag_exists( $this->select ) ) {
			$t_tag_string .= ( is_blank( $t_tag_string ) ? '' : $this->separator );
			$t_tag_string .= tag_get_field( $this->select, 'name' );
		}
		$this->string = $t_tag_string; 
		$this->filter_value = array( FILTER_PROPERTY_TAG_STRING=>$this->string, FILTER_PROPERTY_TAG_SELECT=>$this->select );
	}

	public function display() {
		$t_display['values'] = array(
			array( 'name'=>FILTER_PROPERTY_TAG_STRING, 'value'=>string_attribute( $this->string ) ),
		);
		$t_tag_string = ( empty( $this->string ) ? '&nbsp;' : $this->string );
		$t_display['labels'] = string_html_specialchars( $t_tag_string );

		return $t_display;
	}

	/**
	 * For list type filters, define a keyed-array of possible filter options
	 * @return array Filter options keyed by value=>display
	 */
	public function options() {
		if ( !$this->enabled ) {
			return;
    	}

		$t_rows = tag_get_candidates_for_bug( 0 );
		$t_selected = '';
		$t_option = array( 'label'=>lang_get( 'tag_existing' ), 'value'=>0, 'selected'=>$t_selected );
		foreach ( $t_rows as $t_row ) {
			$t_selected = ( $this->select == $t_row['id'] ? ' selected="selected"' : '' );
			$t_string = $t_row['name'];
			if ( !empty( $t_row['description'] ) ) {
				$t_string .= ' - ' . utf8_substr( $t_row['description'], 0, 20 );
			}
			$t_option = array( 'label'=>string_attribute( $t_string ), 'value'=>$t_row['id'], 'selected'=>$t_selected );
			$t_options[$t_row['id']] = $t_option;
		}
		return $t_options;
	}

	/** 
	 *	Encodes a field and it's value for the filter URL.  This handles the URL encoding
	 *	and arrays.
	 *	@return string url encoded string
	 */
	public function urlEncodeField() {
		if( $this->has_any && $this->isAny() ) {
			return '';
		}
		if( !empty( $this->select ) ) {
			$t_query_array[] = urlencode( FILTER_PROPERTY_TAG_SELECT ) . '=' . urlencode( $this->select );
		}
		if( !empty( $this->string ) ) {
			$t_query_array[] = urlencode( FILTER_PROPERTY_TAG_STRING ) . '=' . urlencode( $this->string );
		}
		if( count( $t_query_array ) == 2 ) {
			return implode( $t_query_array, '&' );
		} else {
			return $t_query_array[0];
		}
	}

	public function query() {
		$t_filter = $this->bug_filter;
		# tags
		$c_tag_string = trim( $this->string );
		$c_tag_select = trim( $this->select );
		if( is_blank( $c_tag_string ) && !is_blank( $c_tag_select ) && $c_tag_select != 0 ) {
			$t_tag = tag_get( $c_tag_select );
			$c_tag_string = $t_tag['name'];
		}

		if( !is_blank( $c_tag_string ) ) {
			$t_tags = tag_parse_filters( $c_tag_string );
			if( count( $t_tags ) ) {
				$t_tags_all = array();
				$t_tags_any = array();
				$t_tags_none = array();
				foreach( $t_tags as $t_tag_row ) {
					switch( $t_tag_row['filter'] ) {
						case 1:
							$t_tags_all[] = $t_tag_row;
						break;
						case 0:
							$t_tags_any[] = $t_tag_row;
						break;
						case -1:
							$t_tags_none[] = $t_tag_row;
						break;
					}
				}

				if( 0 < $this->select && tag_exists( $this->select ) ) {
					$t_tags_any[] = tag_get( $this->select );
				}

				$t_bug_tag_table = db_get_table( 'bug_tag' );
				if( count( $t_tags_all ) ) {
					$t_clauses = array();
					foreach( $t_tags_all as $t_tag_row ) {
						array_push( $t_clauses, "{$t_filter->tables['bug']}.id IN ( SELECT bug_id FROM {$t_filter->filters['bug_tag']} WHERE {$t_filter->tables['bug_tag']}.tag_id = {$t_tag_row[id]} )" );
					}
					$t_filter->addQueryElement( 'where_clauses', '(' . implode( ' AND ', $t_clauses ) . ')' );
				}

				if( count( $t_tags_any ) ) {
					$t_clauses = array();
					foreach( $t_tags_any as $t_tag_row ) {
						array_push( $t_clauses, "{$t_filter->tables['bug_tag']}.tag_id = {$t_tag_row[id]}" );
					}
					$t_filter->addQueryElement( 'where_clauses',  "{$t_filter->tables['bug']}.id IN ( SELECT bug_id FROM {$t_filter->tables['bug_tag']} WHERE ( " . implode( ' OR ', $t_clauses ) . ') )' );
				}

				if( count( $t_tags_none ) ) {
					$t_clauses = array();
					foreach( $t_tags_none as $t_tag_row ) {
						array_push( $t_clauses, "{$t_filter->tables['bug_tag']}.tag_id = {$t_tag_row[id]}" );
					}
					$t_filter->addQueryElement( 'where_clauses', "{$t_filter->tables['bug']}.id NOT IN ( SELECT bug_id FROM {$t_filter->tables['bug_tag']} WHERE ( " . implode( ' OR ', $t_clauses ) . ') )' );
				}
			}
		}
	}
}
