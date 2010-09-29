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
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses filter_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'filter_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

require_js( 'queryStore.js' );

auth_ensure_user_authenticated();

compress_enable();

html_page_top();

$f_source_query_id = gpc_get_int( 'source_query_id', 0 );
if( $f_source_query_id ) {
	$t_query_to_store = MantisStoredQuery::getById( $f_source_query_id );
} else {
	$t_query_to_store = MantisStoredQuery::getCurrent();
}
$t_query_to_store_arr = filter_deserialize( $t_query_to_store->filter_string );
$t_access_level = $t_query_to_store->access_level;
$t_public = ( $t_query_to_store->is_public ? ' checked="checked" ' : '' );
$t_name = '';
$t_source_query_id = 0;
$t_allow_overwrite = false;
if( array_key_exists( '_source_query_id', $t_query_to_store_arr) && $t_cookie_id != $t_query_to_store_arr['_source_query_id'] ) {
	# user has requested to save changes to an existing stored query
	$t_original_query = MantisStoredQuery::getById( $t_query_to_store_arr['_source_query_id'] );

	if( $t_original_query && MantisStoredQuery::canUpdate( $t_original_query->id ) ) {
		$t_name = $t_original_query->name;
		$t_access_level = $t_original_query->access_level;
		$t_source_query_id = $t_query_to_store_arr['_source_query_id'];
		$t_allow_overwrite = true;
		$t_public = ( $t_original_query->is_public ? ' checked="checked" ' : '' );
	}
}
$t_filter_string = string_attribute( $t_query_to_store->filter_string );
?>
<br />
<div id="save-filter">
<?php
if( $t_original_query ) {
	print '<div id="query-exists-msg">' . lang_get( 'query_exists' ) . ' (' . $t_original_query->name . ')</div>';
}

# Check for an error
$t_error_msg = strip_tags( gpc_get_string( 'error_msg', null ) );
if ( $t_error_msg != null ) {
	echo '<div class="error-msg"><pre>' . $t_error_msg . '</pre></div>';
}

?>
<form method="post" id="query-store-form" name="query_store" action="query_store.php">
	<input type="hidden" name="filter_string" value="<?php echo $t_filter_string; ?>" />
	<?php echo form_security_field( 'query_store' );
if( $t_source_query_id ) {
	print '<input type="hidden" name="source_query_id" value="' . $t_source_query_id . '" /> ';
}
print lang_get( 'query_name_label' ) . lang_get( 'word_separator' );
?>
	<input type="text" name="query_name" size="40" value="<?php echo $t_name; ?>" /><br />
	<div id="all-projects">
		<label for="all-projects-field"><?php print lang_get( 'all_projects_label' ); ?></label>
		<input type="checkbox" id="all-projects-field" name="all_projects" value="on" <?php check_checked( ALL_PROJECTS == helper_get_current_project() ) ?> />
	</div>

<?php
$t_filter_preferences = new MantisStoredQueryPreferences();
?>
	<div id="mylist">
		<label for="mylist-field"><?php echo string_attribute( lang_get( 'add_query_to_mylist' ) ); ?></label>
		<input type="checkbox" id="mylist-field" name="mylist" value="on" <?php check_checked( $t_filter_preferences->myListHasFilter( $t_source_query_id ) ); ?> />
	</div>
	<div id="myview">
		<label for="myview-field"><?php echo string_attribute( lang_get( 'add_query_to_myview' ) ); ?></label>
		<input type="checkbox" id="myview-field" name="myview" value="on" <?php check_checked( $t_filter_preferences->myViewHasFilter( $t_source_query_id ) ); ?> />
	</div>
<?php

if ( access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
?>
	<div id="filter-access">
		<div id="filter-access-public">
			<label for="filter-is-public">
			<?php echo lang_get( 'make_public_label' ); ?>
			</label>
			<input type="checkbox" id="filter-is-public" name="is_public" value="on" <?php echo $t_public; ?> />
		</div>
		<div id="filter-access-level">
			<label id="filter-access-level-label" for="filter-access-level">
			<?php echo lang_get( 'access_level_label' ); ?>
			</label>
			<select id="filter-access-level" name="access_level">
			<?php print_access_level_filter_option_list( $t_access_level ); ?>
			</select>
		</div>
	</div>
<?php
}
?>
	<div id="filter-buttons">
<?php
if( $t_allow_overwrite ) {
	print '<input type="submit" name="overwrite_query" class="button" value="' . lang_get( 'overwrite_query' ) . '" />';
}
if( $t_original_query->name != '' ) {
	echo '<input type="submit" name="save_new_query" class="button" value="' . lang_get( 'save_new_query' ) . '" />';
} else {
	echo '<input type="submit" name="save_query" class="button" value="' . lang_get( 'save_query' ) . '" />';
}
?>
	</div>
</form>
<form id="filter-go-back" action="view_all_bug_page.php">
<?php # CSRF protection not required here - form does not result in modifications ?>
<input type="submit" class="button" value="<?php print lang_get( 'go_back' ); ?>" />
</form>
<?php
echo '</div>';
html_page_bottom();
