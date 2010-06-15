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
 * MantisFilterProject
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Class that implements filter functionality for projects 
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterProject extends MantisFilterMultiInt {
	protected $has_any = false;
	protected $has_none = false;
	protected $has_current = true;

	public function __construct( $p_field_name, $p_filter_input= null ) {
		parent::__construct( $p_field_name, $p_filter_input );
		# @todo this label should be changed but it was what was used in filter_api.php
		$this->title = 'email_project_label';
		$this->column_title = 'email_project';
		$this->default = helper_get_current_project();
		if( is_null( $p_filter_input ) ) {
			$this->filter_value = array( $this->default );
		}
	}

	/**
	 *  Returns array of template values for a multi-value filter field.
	 *  @return array An assoc array of labels and values to display in the filter
	 */
	public function display() {
		$t_filter = $this->bug_filter;
		$t_bug_count = $t_filter->bug_count;
		$t_values = array();
		$t_labels = array();
		$this->filter_value = is_array( $this->filter_value ) ? $this->filter_value : array( $this->filter_value );
		foreach( $this->filter_value as $t_project_id ) {
			if( $this->isCurrent( $t_project_id ) ) {
				$t_current_project_id = helper_get_current_project();
				$t_this_name = lang_get( 'current' );
				if( $t_bug_count == 0 ) {
					$t_class_string = 'current_project_name_highlight';
				} else {
					$t_class_string = 'current_project_name';
				}
				$t_this_name .= '<span class="' . $t_class_string . '">(' . string_display( project_get_name( $t_current_project_id, false ) ) . ')</span>';
			} else {
				$t_this_name = string_display( project_get_name( $t_project_id, false ) );
			}
			$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( $t_project_id ) );
			$t_labels[] = $t_this_name;
		}
		$t_display['values'] = $t_values;
		$t_display['labels'] = $t_labels;

		return $t_display;
	}

	/**
	 *  Checks the field value to see if it is a CURRENT value.
	 *  @return bool true for "CURRENT" values and false for others.
	 */
	public function isCurrent( $p_value = null ) {
		if( is_null( $p_value ) ) {
			$p_value = $this->filter_value;
		}
		
		$t_current_project_id = helper_get_current_project();
		if( is_array( $p_value ) ) {
			foreach( $p_value as $t_value ) {
				if( is_numeric( $t_value ) && ( ( META_FILTER_CURRENT == $t_value ) || ( $t_value == $t_current_project_id ) ) ) {
					return true;
				}
			}
		} else {
			if( is_string( $p_value ) && is_blank( $p_value ) ) {
				return false;
			}
			if( is_numeric( $p_value ) && ( ( META_FILTER_CURRENT == $p_value ) || ( $p_value == $t_current_project_id ) ) ) {
				return true;
			}
		}
		return false;
	}

	public function options() {
		$t_current_user = MantisUser::getCurrent();
		$t_projects = MantisProject::getAccessibleByUserId( $t_current_user->id );

		foreach( $t_projects AS $t_project ) {
			$t_selected = in_array( $t_project->id, $this->filter_value );
			$t_options[] = array('value'=>$t_project->id, 'label'=>$t_project->name, 'selected'=>$t_selected);
		}
		return $t_options;
	}

	/**
	 *	Encodes a field and it's value for the filter URL.  This handles the URL encoding
	 *	and arrays.
	 *	@return string url encoded string
	 */
	public function urlEncodeField() {
		if( $this->isAny() ) {
			return '';
		}

		$t_project_id = $this->filter_value;
		if( !is_array( $t_project_id ) ) {
			$t_project_id = array( $t_project_id, );
		}

		$t_query_array = array();
		$t_count = count( $t_project_id );
		if( $t_count > 1 ) {
			foreach( $t_project_id as $t_value ) {
				$t_query_array[] = urlencode( $this->field . '[]' ) . '=' . urlencode( $t_value );
			}
		} else if( $t_count == 1 && $t_project_id[0] != ALL_PROJECTS ) {
			$t_query_array[] = urlencode( $this->field . '[]' ) . '=' . urlencode( $t_project_id[0] );
		} else {
			return '';
		}
		return implode( $t_query_array, '&' );
	}

	public function query() {
		$t_filter = $this->bug_filter;
		$t_current_project_id = helper_get_current_project();

		# normalize the project filtering into an array $t_project_ids
		# This only includes sub projects if one project is selected and it is the current project
		if( 'simple' == $t_filter->_view_type ) {
			log_event( LOG_FILTERING, 'Simple Filter' );
			$t_include_sub_projects = true;
		} else {
			log_event( LOG_FILTERING, 'Advanced Filter' );
        	$t_include_sub_projects = (( count( $this->filter_value ) == 1 ) && ( $this->isCurrent() ) );
    	}

		log_event( LOG_FILTERING, 'project_ids = @P' . implode( ', @P', $this->filter_value ) );
		log_event( LOG_FILTERING, 'include sub-projects = ' . ( $t_include_sub_projects ? '1' : '0' ) );

		# if the array has ALL_PROJECTS, then reset the array to only contain ALL_PROJECTS.
		if( in_array( ALL_PROJECTS, $this->filter_value ) || ( ALL_PROJECTS == $t_current_project_id && $this->isCurrent() )) {
			log_event( LOG_FILTERING, 'all projects selected' );
			if( user_is_administrator( $t_filter->filter_user_id ) ) {
				log_event( LOG_FILTERING, 'all projects + administrator, hence no project filter.' );
				return;
			} else {
				$t_project_ids = user_get_accessible_projects( $t_filter->filter_user_id );
			}
		} else {
			$t_new_project_ids = array();
			foreach( $this->filter_value as $t_pid ) {
				if( $this->isCurrent( $t_pid ) ) {
					# use the actual project id for the query rather than the meta value
					$t_pid = $t_current_project_id;
				}
				// filter out inaccessible projects.
				if( !access_has_project_level( VIEWER, $t_pid, $t_filter->filter_user_id ) ) {
					continue;
				}

				$t_new_project_ids[] = $t_pid;
    		}
			$t_project_ids = $t_new_project_ids;
		}

        // expand project ids to include sub-projects
		if( $t_include_sub_projects ) {
			$t_top_project_ids = $t_project_ids;
			foreach( $t_top_project_ids as $t_pid ) {
				log_event( LOG_FILTERING, 'Getting sub-projects for project id @P' . $t_pid );
				$t_project_ids = array_merge( $t_project_ids, user_get_all_accessible_subprojects( $t_filter->filter_user_id, $t_pid ) );
			}
			$t_project_ids = array_unique( $t_project_ids );
        }

		# if no projects are accessible, then throw an error.
		if( count( $t_project_ids ) == 0 ) {
			log_event( LOG_FILTERING, 'no accessible projects' );
			throw new Exception( 'No projects are accessible.' );
		}
		log_event( LOG_FILTERING, 'project_ids after including sub-projects = @P' . implode( ', @P', $t_project_ids ) );

	    # this array is to be populated with project ids for which we only want to show public issues.  This is due to the limited
        # access of the current user.
        $t_public_only_project_ids = array();

        # this array is populated with project ids that the current user has full access to.
		$t_private_and_public_project_ids = array();

		$t_access_required_to_view_private_bugs = config_get( 'private_bug_threshold' );
		foreach( $t_project_ids as $t_pid ) {
			if( access_has_project_level( $t_access_required_to_view_private_bugs, $t_pid, $t_filter->filter_user_id ) ) {
				$t_private_and_public_project_ids[] = $t_pid;
			} else {
				$t_public_only_project_ids[] = $t_pid;
			}
		}

		log_event( LOG_FILTERING, 'project_ids (with public/private access) = @P' . implode( ', @P', $t_private_and_public_project_ids ) );
		log_event( LOG_FILTERING, 'project_ids (with public access) = @P' . implode( ', @P', $t_public_only_project_ids ) );
		$t_count_private_and_public_project_ids = count( $t_private_and_public_project_ids );
		if( $t_count_private_and_public_project_ids == 1 ) {
			$t_private_and_public_query = "( {$t_filter->tables['bug']}.project_id = " . $t_private_and_public_project_ids[0] . " )";
		} else if( $t_count_private_and_public_project_ids > 1 ) {
			$t_private_and_public_query = "( {$t_filter->tables['bug']}.project_id in (" . implode( ', ', $t_private_and_public_project_ids ) . ") )";
		} else {
			$t_private_and_public_query = null;
		}

		$t_count_public_only_project_ids = count( $t_public_only_project_ids );
		$t_public_view_state_check = "( ( {$t_filter->tables['bug']}.view_state = " . VS_PUBLIC . " ) OR ( {$t_filter->tables['bug']}.reporter_id = {$t_filter->filter_user_id} ) )";
		if( $t_count_public_only_project_ids == 1 ) {
			$t_public_only_query = "( ( {$t_filter->tables['bug']}.project_id = " . $t_public_only_project_ids[0] . " ) AND $t_public_view_state_check )";
		} else if( $t_count_public_only_project_ids > 1 ) {
			$t_public_only_query = "( ( {$t_filter->tables['bug']}.project_id in (" . implode( ', ', $t_public_only_project_ids ) . ") ) AND $t_public_view_state_check )";
		} else {
			$t_public_only_query = null;
		}

        # both queries can't be null, so we either have one of them or both.
		if( $t_private_and_public_query === null ) {
			$t_project_query = $t_public_only_query;
		} else if( $t_public_only_query === null ) {
			$t_project_query = $t_private_and_public_query;
		} else {
			$t_project_query = "( $t_public_only_query OR $t_private_and_public_query )";
		}

        log_event( LOG_FILTERING, 'project query = ' . $t_project_query );
		$t_filter->addQueryElement('where_clauses', $t_project_query );
	}
}
