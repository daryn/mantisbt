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
 * MantisFilterProfile
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Class that implements filter functionality for Profile id field
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterProfile extends MantisFilterMultiString {
	public function __construct( $p_field_name, $p_filter_input= null ) {
		parent::__construct( $p_field_name, $p_filter_input );
		$this->title = 'profile_label';
		$this->enabled = ( ON == config_get( 'enable_profiles' ) ? true : false );
		$this->has_none = true;
	}

	/**
	 *  Returns array of template values for a multi-value filter field.
	 *  @return array An assoc array of labels and values to display in the filter
	 *	@todo this doesn't display none...check to see if it really should have it or if has_none should be false
	 */
	public function display() {
		$t_display_arr = array();
		if( $this->has_any && $this->isAny() ) {
			$t_display['values'] = array(
				array( 'name'=>$this->field, 'value'=>string_attribute( META_FILTER_ANY ) ),
			);
			$t_display['labels'] = string_display( lang_get( 'any' ) ); 
		} else {
			$t_values = array();
			$t_labels = array();
			$this->filter_value = is_array( $this->filter_value ) ? $this->filter_value : array( $this->filter_value );
			foreach( $this->filter_value as $t_current ) {
            	$t_profile = profile_get_row_direct( stripslashes( $t_current ) );
            	$t_this_string = "{$t_profile['platform']} {$t_profile['os']} {$t_profile['os_build']}";
				$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( $t_current ) );
				$t_labels[] = string_display( $t_this_string );
			}
			$t_display['values'] = $t_values; 
			$t_display['labels'] = $t_labels; 
		}
		return $t_display;
	}

	public function options() {
		$t_filter = $this->bug_filter;
		$t_project_field = $t_filter->getFields( FILTER_PROPERTY_PROJECT_ID );

		$t_profiles = array();
		foreach( $t_project_field->filter_value AS $t_project_id ) {
			$t_profiles = array_merge( $t_profiles, profile_get_all_for_project( $t_project_id ) );
		}

		foreach( $t_profiles as $t_profile ) {
			$t_selected = false;
			$t_platform = string_attribute( $t_profile['platform'] );
			$t_os = string_attribute( $t_profile['os'] );
			$t_os_build = string_attribute( $t_profile['os_build'] );
			if( in_array( $t_profile['id'], $this->filter_value ) ) {
				$t_selected = true;
			}
			$t_options[] = array( 'value'=>$t_profile['id'], 'label'=>$t_platform . ' ' . $t_os . ' ' . $t_os_build, 'selected'=>$t_selected  );
		}
		return $t_options;
	}
}
