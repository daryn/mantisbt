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
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses file_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses helper_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 */

if ( !defined( 'MY_VIEW_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'file_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'helper_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );

$t_filter_arr = filter_deserialize( $t_filter->filter_string );
$t_rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $t_filter_arr );

# Improve performance by caching category data in one pass
if( helper_get_current_project() == 0 ) {
	$t_categories = array();
	foreach( $t_rows as $t_row ) {
		$t_categories[] = $t_row->category_id;
	}

	category_cache_array_rows( array_unique( $t_categories ) );
}

# -- ====================== BUG LIST ========================= --
?>

<div class="my-buglist <?php echo $t_box_css; ?>">
    <div class="my-buglist-nav">
	<span class="my-buglist-title"><?php print_link( $t_filter->getUrl(), $t_filter->name, false, 'subtle' ); ?></span>
<?php
		$t_bug_row_count = count( $t_rows );
		if( $t_bug_row_count > 0 ) {
			$v_start = $t_filter_arr[FILTER_PROPERTY_ISSUES_PER_PAGE] * ( $f_page_number - 1 ) + 1;
			$v_end = $v_start + count( $t_rows ) - 1;
		} else {
			$v_start = 0;
			$v_end = 0;
		} ?>

	<span class="my-buglist-count">(<?php echo $v_start; ?> - <?php echo $v_end; ?> / <?php echo $t_bug_count; ?>)</span>
	</div>
<?php
$t_count = count( $t_rows );
if( $t_count > 0 ) {
	echo "\t<ul class=\"buglist\">\n";
	for( $j = 0;$j < $t_count; $j++ ) {
		$t_bug = $t_rows[$j];

		$t_summary = string_display_line_links( $t_bug->summary );
		$t_last_updated = date( config_get( 'normal_date_format' ), $t_bug->last_updated );

		$t_bug_classes = 'my-buglist-bug';
		# choose color based on status
		$status_color = get_status_color( $t_bug->status );
		$t_bug_classes .= ' bug-status-' . MantisEnum::getLabel( config_get( 'status_enum_string' ), $t_bug->status );

		# Check for attachments
		$t_attachment_count = 0;
		if(( file_can_view_bug_attachments( $t_bug->id ) ) ) {
			$t_attachment_count = file_bug_attachment_count( $t_bug->id );
		}

		# grab the project name
		$project_name = project_get_field( $t_bug->project_id, 'name' );

		if ( VS_PRIVATE == $t_bug->view_state ) {
			$t_bug_classes .= ' my-buglist-private';
		}

		$t_priority_classes = 'buglist-priority priority-' . MantisEnum::getLabel( config_get( 'priority_enum_string' ), $t_bug->priority );
		$t_priority_classes .= ( ON == config_get( 'show_priority_text' ) ? '-text' : '-icon' );
		$t_priority_classes .= ( $t_bug->is_significant() ? ' significant' : '' );
		$t_pri_str = MantisEnum::getLocalizedLabel( config_get( 'priority_enum_string' ), lang_get( 'priority_enum_string' ), $t_bug->priority );


		$t_bug_url = string_get_bug_view_url( $t_bug->id, null );
		$t_bug_url_title = string_html_specialchars( sprintf( lang_get( 'label' ), lang_get( 'issue_id' ) . $t_bug->id ) . lang_get( 'word_separator' ) . $t_bug->summary );
		# include project name if viewing 'all projects' or bug is in subproject
		$t_bug_category = string_display_line( category_full_name( $t_bug->category_id, true, $t_bug->project_id ) );
		$t_modified_classes = 'last-modified';
		if( $t_bug->last_updated > strtotime( '-' . $t_filter_arr[FILTER_PROPERTY_HIGHLIGHT_CHANGED] . ' hours' ) ) {
			$t_modified_classes .= ' changed-recently';
		}
?>
		<li class="<?php echo $t_bug_classes; ?>">
			<span class="buglist-id"><?php print_bug_link( $t_bug->id ); ?></span>
			<span class="buglist-summary"><a href="<?php echo $t_bug_url; ?>" title="<?php echo $t_bug_url_title; ?>"><?php echo $t_summary; ?></a></span>
			<span class="buglist-icons">
<?php
		if( !bug_is_readonly( $t_bug->id ) && access_has_bug_level( $t_update_bug_threshold, $t_bug->id ) ) {
?>
				<span class="buglist-edit"><a class="buglist-edit-link" href="<?php echo string_get_bug_update_url( $t_bug->id ); ?>"><?php echo lang_get( 'update_bug_button' ); ?></a></span><?php
		}
?>
				<span class="<?php echo $t_priority_classes; ?>"><?php echo $t_pri_str; ?></span>
<?php	if ( $t_attachment_count > 0 ) {
			$t_href = string_get_bug_view_url( $t_bug->id ) . '#attachments';
			$t_href_title = sprintf( lang_get( 'view_attachments_for_issue' ), $t_attachment_count, $t_bug->id );
			$t_alt_text = $t_attachment_count . lang_get( 'word_separator' ) . lang_get( 'attachments' );
?>
				<span class="buglist-attachments"><a class="buglist-attachments-link" href="<?php echo $t_href; ?>" title="<?php echo $t_href_title; ?>"><?php echo $t_alt_text; ?></a></span>
<?php
		}

		if( VS_PRIVATE == $t_bug->view_state ) {
?>
				<span class="buglist-private"><?php echo lang_get('private' ); ?></span>
<?php
		}
?>
			</span>
<?php
		 if( ON == config_get( 'show_bug_project_links' ) && helper_get_current_project() != $t_bug->project_id ) {
?>
			<span class="buglist-project"><?php echo string_display_line( project_get_name( $t_bug->project_id ) ); ?></span>
<?php
		}
?>
			<span class="buglist-category"><?php echo $t_bug_category; ?></span>
				<span class="<?php echo $t_modified_classes; ?>"><?php echo $t_last_updated; ?></span><br />
		</li>
<?php # -- end of Repeating bug row --
	} # -- ====================== end of BUG LIST ========================= --

	echo '</ul>';
} else {
	echo '<br/>';
}
echo '</div>';
# Free the memory allocated for the rows in this box since it is not longer needed.
unset( $t_rows );
