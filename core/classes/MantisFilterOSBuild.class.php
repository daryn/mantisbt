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
 * MantisFilterMutliString
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Class that implements filter functionality for multi select fields
 * and integration with MantisBT.
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterOSBuild extends MantisFilterMultiString {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		parent::__construct( $p_field_name, $p_filter_input );
		$this->title = 'os_version_label';
		$this->has_none = true;
		$this->enabled = ( ON == config_get( 'enable_profiles' ) ? true : false );
	}

	public function options() {
		log_event( LOG_FILTERING, 'OS Build = ' . var_export( $this->filter_value, true ) );
 		$t_os_build_array = profile_get_field_all_for_user( 'os_build' );
		foreach( $t_os_build_array as $t_os_build ) {
			$t_os_build = string_attribute( $t_os_build );
			$t_selected = in_array( $t_os_build, $this->filter_value ) ? true : false;
			$t_options[] = array( 'value'=>string_attribute( $t_os_build ), 'label'=>string_display( $t_os_build ), 'selected'=>$t_selected );
		}
		return $t_options;
	}
}
