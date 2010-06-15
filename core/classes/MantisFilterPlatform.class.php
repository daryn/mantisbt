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
class MantisFilterPlatform extends MantisFilterMultiString {
	public function __construct( $p_field_name, $p_filter_input=null) {
		parent::__construct( $p_field_name, $p_filter_input );
		$this->title = 'platform_label';
		$this->has_none = true;
		$this->enabled = ( ON == config_get( 'enable_profiles' ) ? true : false );
	}

	public function options() {
		log_event( LOG_FILTERING, 'Platform = ' . var_export( $this->filter_value, true ) );
		$t_platforms_array = profile_get_field_all_for_user( 'platform' );
		foreach( $t_platforms_array as $t_platform ) {
			$t_platform = string_attribute( $t_platform );
			$t_selected = in_array( $t_platform, $this->filter_value ) ? true : false;
			$t_options[] = array( 'value'=>string_attribute( $t_platform ), 'label'=>string_display( $t_platform), 'selected'=>$t_selected );
		}
		return $t_options;
	}
}
