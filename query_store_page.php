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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

auth_ensure_user_authenticated();

$t_cookie_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
$t_query_arr = MantisBugFilter::getAvailable();
$t_query_to_store = MantisBugFilter::getById( $t_cookie_id );

$t_query_exists_label = lang_get( 'query_exists' );
$t_error_msg = strip_tags( gpc_get_string( 'error_msg', null ) );
$t_query_name_label_string = lang_get( 'query_name_label' ) . lang_get( 'word_separator' );
$t_has_create_shared_stored_query_threshold = access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) );
$t_make_public_label = lang_get( 'make_public' );
$t_all_projects_label = lang_get( 'all_projects' );
$t_save_query_label = lang_get( 'save_query' );
$t_go_back_label = lang_get( 'go_back' );
$t_query_store_security_field = form_security_field( 'query_store' );

# Begin html output
compress_enable();
html_page_top(); ?>
	<br />
	<div align="center">
		<?php
		# Let's just see if any of the current filters are the
		# same as the one we're about the try and save
		foreach( $t_query_arr as $t_id => $t_query ) {
			if ( $t_query->filterStringExists( $t_query_to_store ) ) {
				echo $t_query_exists_label . ' (' . $t_query->name . ')<br />';
			}
		}

		# Check for an error
		if ( $t_error_msg != null ) {
			echo "<br />$t_error_msg<br /><br />";
		}

		echo $t_query_name_label_string; ?>
		<form method="post" action="query_store.php">
			<?php echo $t_query_store_security_field ?>
			<input type="text" name="query_name" /><br />
			<?php if ( $t_has_create_shared_stored_query_threshold ) { ?>
			<input type="checkbox" name="is_public" value="on" />
			<?php echo $t_make_public_label; ?>
			<br />
			<?php } ?>
			<input type="checkbox" name="all_projects" value="on" <?php check_checked( ALL_PROJECTS == helper_get_current_project() ) ?> >
			<?php echo $t_all_projects_label; ?><br /><br />
			<input type="submit" class="button" value="<?php echo $t_save_query_label; ?>" />
		</form>
		<form action="view_all_bug_page.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="submit" class="button" value="<?php echo $t_go_back_label; ?>" />
		</form>
	</div>
<?php
html_page_bottom();
