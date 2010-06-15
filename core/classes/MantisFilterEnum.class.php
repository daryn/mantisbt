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
class MantisFilterEnum extends MantisFilterMultiInt {
	public function getLocalizedSelectedValues() {
		$t_values = array();
		$t_config_var_name = $this->name . '_enum_string';
		$t_config_var = config_get( $t_config_var_name );
		$t_string_var = lang_get( $t_config_var_name );
		foreach( $this->filter_value AS $t_enum_id ) {
			if( $t_enum_id > 0 ) {
				$t_label = MantisEnum::getLocalizedLabel( $t_config_var, $t_string_var, $t_enum_id );
				$t_values[$t_enum_id] =  $t_label;
			}
		}
		return $t_values;
	}

	/**
	 * For list type filters, define a keyed-array of possible
	 * filter options excluding any meta options,
	 * @return array Filter options keyed by value=>display
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
			$t_option = array( 'label'=>$t_label, 'value'=>$t_key, 'selected'=>$t_selected );
			$t_options[$t_key] = $t_option;
		}
		return $t_options;
	}

	/**
	 *  Returns array of template values for a multi-value enum field.
	 *	@return array An assoc array of labels and values to display in the filter
	 */
	public function display() {
		if( $this->has_any && $this->isAny() ) {
			$t_display['values'] = array(
				array( 'name'=>$this->htmlFieldName(), 'value'=>string_attribute( META_FILTER_ANY ) ),
			);
			$t_display['labels'] = string_display( lang_get( 'any' ) ); 
		} else {
			$t_values = array();
			$t_labels = array();
			$this->filter_value = is_array( $this->filter_value ) ? $this->filter_value : array( $this->filter_value );
			foreach( $this->filter_value as $t_current ) {
				$t_current = stripslashes( $t_current );
				if( $this->has_none && $this->isNone( $t_current ) ) {
					$t_values[] = array( 'name'=>$this->htmlFieldName(), 'value'=>string_attribute( META_FILTER_NONE ) );
					$t_labels[] = string_display( lang_get( 'none' ) );
				} else {
					$t_values[] = array( 'name'=>$this->htmlFieldName(), 'value'=>string_attribute( $t_current ) );
					$t_labels[] = string_display( get_enum_element( $this->field, $t_current ) );
				}
			}
			$t_display['values'] = $t_values; 
			$t_display['labels'] = $t_labels; 
		}
		return $t_display;
	}
}
