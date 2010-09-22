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
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );

$f_filter_id = gpc_get_string( 'filter_id' );
$f_myview = gpc_get_bool( 'myview' );
$f_mylist = gpc_get_bool( 'mylist' );
$f_ajax = gpc_get_bool( 'ajax' );
$t_project_id = helper_get_current_project();
$f_project_id = gpc_get_int( 'project_id', $t_project_id );

# user should only be able to set preferences for a project that is accessible.
if ( $f_project_id != ALL_PROJECTS ) {
	access_ensure_project_level( VIEWER, $t_project_id );
}

$t_user_id = auth_get_current_user_id();

$t_filter_prefs = new MantisStoredQueryPreferences();
if( MantisStoredQuery::isFilterAvailable( $f_filter_id ) ) {
	# the filter is available.  See if it's already in the config or not and switch it.
	if( $f_myview ) {
		$t_field_name = 'myview';
		form_security_validate( "query_store_myview_$f_filter_id" );
		if( $t_filter_prefs->myViewHasFilter( $f_filter_id ) ) {
			$t_filter_prefs->removeMyViewFilter( $f_filter_id );
			$t_label = string_attribute( lang_get( 'add_query_to_myview' ) );
			$t_class= 'myview-filter-removed';
		} else {
			$t_filter_prefs->addMyViewFilter( $f_filter_id );
			$t_label = string_attribute( lang_get( 'remove_query_from_myview' ) );
			$t_class= 'myview-has-filter';
		}
		$t_filter_prefs->saveMyView();
		form_security_purge( "query_store_myview_$f_filter_id" );
	}
	if( $f_mylist ) {
		$t_field_name = 'mylist';
		form_security_validate( "query_store_mylist_$f_filter_id" );
		if( $t_filter_prefs->myListHasFilter( $f_filter_id ) ) {
			$t_filter_prefs->removeMyListFilter( $f_filter_id );
			$t_label = string_attribute( lang_get( 'add_query_to_mylist' ) );
			$t_class= 'list-filter-removed';
		} else {
			$t_filter_prefs->addMyListFilter( $f_filter_id );
			$t_label = string_attribute( lang_get( 'remove_query_from_mylist' ) );
			$t_class= 'list-has-filter';
		}

		$t_filter_prefs->saveMyList();
		form_security_purge( "query_store_mylist_$f_filter_id" );
	}
} else {
	# the filters are no longer available to the user. remove them if necessary
	if( $t_filter_prefs->myListHasFilter( $f_filter_id ) ) {
		$t_filter_prefs->removeMyListFilter( $f_filter_id );
	}
	if( $t_filter_prefs->myViewHasFilter( $f_filter_id ) ) {
		$t_filter_prefs->removeMyViewFilter( $f_filter_id );
	}
	$t_filter_prefs->saveMyView();
	$t_filter_prefs->saveMyList();
	form_security_purge( "query_store_myview_$f_filter_id" );
	form_security_purge( "query_store_mylist_$f_filter_id" );
	$f_ajax = false; # force a reload
}

if( $f_ajax ) {
	# only return the new form
?>
	<form name="stored_query_<?php echo $t_field_name; ?>" method="post" action="account_filter_prefs_set.php" class="hidden">
		<input type="hidden" name="filter_id" value="<?php echo $f_filter_id; ?>" />
		<input type="hidden" name="<?php echo $t_field_name; ?>" value="1" />
		<?php echo form_security_field( "query_store_{$t_field_name}_{$f_filter_id}" ); ?>
		<input title="<?php echo $t_label; ?>" type="submit" class="<?php echo $t_class; ?>" value="<?php echo $t_label ;?>" />
	</form>
<?php
} else {
?>
	<br />
	<div align="center">
	<?php
	$t_redirect_url = 'query_view_page.php';
	html_page_top( null, $t_redirect_url );
	echo '<br />';
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
	?>
	</div>
<?php
	html_page_bottom();
}
