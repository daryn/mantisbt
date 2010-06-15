<?php
# MantisBT - A PHP based bugtracking system

# Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.

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
 * Class that implements filter functionality for enumerated lists
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterEnumViewState extends MantisFilterInt {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		parent::__construct( $p_field_name, $p_filter_input );
		$this->size = 0;
		$this->template = 'select';
		$this->title = 'view_status_label';
		$this->column_title = 'view_status';
		if( is_array( $p_filter_input) ) {
			# view state should only have one value 
			$this->filter_value= $p_filter_input[0];
		}
	}

	public function getLocalizedSelectedValues() {
		$t_values = array();
		if( $this->filter_value > 0 ) {
			$t_config_var_name = $this->field . '_enum_string';
			$t_config_var = config_get( $t_config_var_name );
			$t_string_var = lang_get( $t_config_var_name );
			$t_label = MantisEnum::getLocalizedLabel( $t_config_var, $t_string_var, $this->filter_value);
			$t_values[$this->filter_value] = $t_label;
		}
		return $t_values;
	}

	/**
	 *	For list type filters, define a keyed-array of possible
	 *	filter options excluding any meta options,
	 *	@return array Filter options keyed by value=>display
	 */
	public function options() {
		$t_config_var_name = $this->field . '_enum_string';
		$t_config_var = config_get( $t_config_var_name );
		$t_string_var = lang_get( $t_config_var_name );

		$t_enum_values = MantisEnum::getValues( $t_config_var );
		foreach ( $t_enum_values as $t_key ) {
			$t_label = MantisEnum::getLocalizedLabel( $t_config_var, $t_string_var, $t_key );
			if( is_array( $this->filter_value ) ) {
				$t_selected = in_array( $t_key, $this->filter_value );
			} else {
				$t_selected = ( $t_key == $this->filter_value ? true : false );
			}
			$t_option = array( 'label'=>$t_label, 'value'=>$t_key,'selected'=>$t_selected );
			$t_options[$t_key] = $t_option;
		}
		return $t_options;
	}

	/**
	 *  Returns array of template values
	 *	@param  string $p_field_name
	 *	@param mixed $p_field_value
	 *	@return array An assoc array of labels and values to display in the filter
	 */
	public function display() {
		$t_values = array();
		$t_labels = array();
		$this->filter_value = is_array( $this->filter_value ) ? array_pop( $this->filter_value ) : $this->filter_value;
		$t_current = stripslashes( $this->filter_value );
		if( $this->has_any && $this->isAny() ) {
			$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( META_FILTER_ANY) );
			$t_labels[] = string_display( lang_get( 'any' ) );
		} else {
			$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( $t_current ) );
			$t_labels = string_display( get_enum_element( $this->field, $t_current ) );
		}
		$t_display['values'] = $t_values; 
		$t_display['labels'] = $t_labels; 
		return $t_display;
	}

	public function query() {
		$t_filter = $this->bug_filter;
    	if( !$this->isAny() ) {
        	$t_view_state_query = "( {$t_filter->tables['bug']}.view_state=" . db_param() . ')';
        	log_event( LOG_FILTERING, 'view_state query = ' . $t_view_state_query );
			$t_filter->addQueryElement('where_params', $this->filter_value );
			$t_filter->addQueryElement('where_clauses', $t_view_state_query );
    	} else {
        	log_event( LOG_FILTERING, 'no view_state query' );
    	}
	}
}
