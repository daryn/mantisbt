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
 * Class that implements filter functionality for user fields
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterUserNoteBy extends MantisFilterUser {

	public function query() {
		$t_filter = $this->bug_filter;
		if( !$this->isAny() ) {
			$t_bugnote_table_alias = 'mbnt';
			$t_clauses = array();
			$t_filter->addQueryElement('from_clauses', $t_filter->tables['bugnote'] . " $t_bugnote_table_alias" );
			$t_filter->addQueryElement('where_clauses', "( {$t_filter->tables['bug']}.id = $t_bugnote_table_alias.bug_id )" );
			foreach( $this->filter_value as $t_note_user_id ) {
				if( $this->isMyself( $t_note_user_id ) ) {
					array_push( $t_clauses, $t_filter->filter_user_id );
				} else {
					array_push( $t_clauses, $t_note_user_id );
				}
			}
			if( 1 < count( $t_clauses ) ) {
				$t_where_tmp = array();
				foreach( $t_clauses as $t_clause ) {
					$t_filter->addQueryElement('where_params', $t_clause ); 
					$t_where_tmp[] = db_param();
				}
				$t_filter->addQueryElement('where_clauses', "( $t_bugnote_table_alias.reporter_id IN (" . implode( ', ', $t_where_tmp ) . ") )" );
			} else {
				$t_filter->addQueryElement('where_params', $t_clauses[0] ); 
				$t_filter->addQueryElement('where_clauses', "( $t_bugnote_table_alias.reporter_id=" . db_param() . " )" );
			}
		}
	}
}

