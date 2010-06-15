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
class MantisFilterHideStatus extends MantisFilterInt {
	public function __construct( $p_field_name, $p_filter_input=null ) {
 		$this->default = config_get( 'hide_status_default' );
		$this->template = 'select';
		parent::__construct( $p_field_name, $p_filter_input );

		if( is_array( $p_filter_input) ) {
			# Hide should only have one value 
			$this->filter_value= $p_filter_input[0];
		}

		$this->has_any = false;
		$this->has_none = true;
	}

	/**
	 *	Hide status is only enabled on the simple filter view
	 */
	public function __get( $p_field ) {
		$t_filter = $this->bug_filter;
		switch( $p_field ) {
			case 'enabled':
				$t_view_type = $t_filter->getField('_view_type');
				return ( $t_view_type->filter_value == 'simple' ? true : false ); 
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
	 */
	public function processGPC() {
		$t_status = $this->bug_filter->getField( FILTER_PROPERTY_STATUS );
		$t_is_hide_status_submitted = gpc_isset( FILTER_PROPERTY_HIDE_STATUS );
		$t_is_status_submitted = gpc_isset( FILTER_PROPERTY_STATUS );
		$t_current_value = $this->filter_value;
		parent::processGPC();
		$t_new_value = $this->filter_value;
		if( $t_current_value != $new_value ) {
			$this->__set( 'changed', true );
		} else {
			$this->__set( 'changed', false );
		}

		if( $t_status->changed === true ) {
			$this->filter_value = META_FILTER_NONE;
		}
	}

	public function getLocalizedSelectedValues() {
		$t_values = array();
		if( $this->filter_value > 0 ) {
			$t_config_var_name = FILTER_PROPERTY_STATUS . '_enum_string';
			$t_config_var = config_get( $t_config_var_name );
			$t_string_var = lang_get( $t_config_var_name );
			$t_label = MantisEnum::getLocalizedLabel( $t_config_var, $t_string_var, $this->filter_value);
			$t_values[$this->filter_value] = $t_label . ' (' . lang_get( 'and_above' ) . ')';
		}
		return $t_values;
	}

	/**
	 *	For list type filters, define a keyed-array of possible
	 *	filter options excluding any meta options,
	 *	@return array Filter options keyed by value=>display
	 */
	public function options() {
		$t_config_var_name = FILTER_PROPERTY_STATUS . '_enum_string';
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
	 *  Returns array of template values for a multi-value enum field.
	 *	@return array An assoc array of labels and values to display in the filter
	 */
	public function display() {
		$t_values = array();
		$t_labels = array();
		$this->filter_value = is_array( $this->filter_value ) ? array_pop( $this->filter_value ) : $this->filter_value;
		$t_current = stripslashes( $this->filter_value );
		if( $this->has_none && $this->isNone() ) {
			$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( META_FILTER_NONE ) );
			$t_labels[] = string_display( lang_get( 'none' ) );
		} else {
			$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( $t_current ) );
			$t_labels = string_display( get_enum_element( FILTER_PROPERTY_STATUS, $t_current ) . ' ' . lang_get('and_above') );
		}
		$t_display['values'] = $t_values; 
		$t_display['labels'] = $t_labels; 
		return $t_display;
	}

	/**
	 * Validate the filter input, returning true if input is
	 * valid, or returning false if invalid.  Invalid inputs will
	 * be replaced with the filter's default value.
	 * @param multi Filter field input
	 * @return boolean Input valid (true) or invalid (false)
	 */
	public function validate() {
		parent::validate();
		$t_statuses = MantisEnum::getValues( config_get( 'status_enum_string' ) );
		/**
		 *  Remove any statuses that should be excluded by the hide_status field
		 */
		$t_view_type = $this->bug_filter->getField( '_view_type' );
		$t_status = $this->bug_filter->getField( FILTER_PROPERTY_STATUS );
		/**
		 *  If a status is selected in the status and the hide status field, remove it from
		 *  hide status
		 */
		if( $t_view_type->filter_value == "simple" && $this->filter_value > 0 ) {
			# if there's only one status selected hide status is invalid
			# if dhtml filters is not enabled it's possible for an old value to be selected which causes this to
			# set hide status to none even when it shouldn't.  Add changed check to be sure it only sets to none
			# if the status is actually changed to a new value.
			if( $t_status->changed === true && !$t_status->isAny() && count( $t_status->filter_value ) == 1 ) {
				$this->filter_value = META_FILTER_NONE;
			#} else if( count( $t_status->filter_value ) > 1 && !$this->isNone() ) {
			#	$t_status->filter_value = array( META_FILTER_ANY );
			}
		}
		return true;
	}

	public function query() {
		$t_filter = $this->bug_filter;
		$t_status = $t_filter->getField( FILTER_PROPERTY_STATUS );
		# if status isAny just use hide status field
		# if status has selections...ignore hide status... this should work for either advanced or simple views
		if( $t_status->isAny() && !$this->isNone() ) {
			$t_filter->addQueryElement( 'where_params', $this->filter_value );
			$t_filter->addQueryElement( 'where_clauses', "( {$t_filter->tables['bug']}.status < " . db_param() . " )" );
		}
	}
}
