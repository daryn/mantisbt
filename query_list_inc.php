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
 */

if ( !defined( 'QUERY_LIST_INC_ALLOW' ) ) {
	return;
}

# Special case: if we've deleted our last query, we have nothing to show here.
if ( count( $t_query_arr ) < 1 ) {
	#print_header_redirect( 'view_all_bug_page.php' );
	return;
}
?>
<div id="manage-<?php echo $t_list; ?>-stored-queries" class="manage-stored-queries">
	<div class="query-list-title"><?php echo sprintf( lang_get( 'query_list_title'), $t_access_label ); ?></div>
	<div class="container">
	<ul class="list">

<?php
$i=0;
$t_count = count( $t_query_arr );
foreach( $t_query_arr as $t_query_id => $t_name ) {
	if( $i == ceil( $t_count/2 ) ) {
		# split into a second list
		echo '</ul>';
		echo '<ul class="list">';
		$i=0;
	}
	$t_class = ( $i%2 == 0 ? 'odd' : 'even' );
	?><li class="manage-stored-query <?php echo $t_class; ?>"><?php
	if ( OFF != $t_rss_enabled ) {
		?><div class="manage-stored-query-rss"><?php
		if( $t_list != 'default' ) {
			# Use the "new" RSS link style.
			print_rss( rss_get_issues_feed_url( null, null, $t_query_id ), lang_get( 'rss' ) );
		}
		?></div><?php
	}
	?>
	<div class="manage-stored-query-link">
	<?php
		if( $t_list != 'default' ) {
			$t_query_id = db_prepare_int( $t_query_id );
			print_link( "view_all_set.php?type=3&source_query_id=$t_query_id", $t_name );
		} else {
			$t_filter = MantisStoredQuery::getNamedDefault( $t_query_id );
			$t_url = filter_get_temporary_url( filter_deserialize( $t_filter->filter_string ) );
			print_link( $t_url, $t_name );
		}
	?>
	</div>
	<?php
	if ( $t_list != 'default' && MantisStoredQuery::canDelete( $t_query_id ) ) {
		$t_action = string_attribute( "query_delete_page.php?source_query_id=$t_query_id" );
		$t_id = string_attribute( 'query-delete-' . $t_query_id );
		$t_name = string_attribute( 'query-delete-page' );
		$t_label = string_attribute( lang_get( 'delete_query' ) );
		?>
		<div class="manage-stored-query-delete">
			<form method="post" action="<?php echo $t_action; ?>">
				<?php echo form_security_field( 'query_delete_page' ); ?>
				<input type="submit" class="delete-button" value="<?php echo $t_label ;?>" />
			</form>
		</div>
	<?php
	}
	if ( $t_list != 'default' && MantisStoredQuery::canUpdate( $t_query_id ) ) {
		$t_action = string_attribute( "query_store_page.php?source_query_id=$t_query_id" );
		$t_id = string_attribute( 'query-store-' . $t_query_id );
		$t_name = string_attribute( 'query-store-page' );
		$t_label = string_attribute( lang_get( 'save_query' ) );
	?>
		<div class="manage-stored-query-edit">
			<form method="post" action="<?php echo $t_action; ?>">
				<?php echo form_security_field( 'query_store_page' ); ?>
				<input type="submit" class="save-button" value="<?php echo $t_label ;?>" />
			</form>
		</div>
	<?php
	}
	# the label and image depends on whether filter is already on the list or not
	if( $t_filter_preferences->myListHasFilter( $t_query_id ) ) {
		$t_label = string_attribute( lang_get( 'remove_query_from_mylist' ) );
		$t_class= 'list-has-filter';
	} else {
		$t_label = string_attribute( lang_get( 'add_query_to_mylist' ) );
		$t_class= 'list-filter-removed';
	}
	$t_action = 'account_filter_prefs_set.php';
	if( $t_list != 'default' ) {
	?>
		<div class="manage-stored-query-list">
			<form name="stored_query_mylist" method="post" action="<?php echo $t_action; ?>">
				<input type="hidden" name="filter_id" value="<?php echo $t_query_id; ?>" />
				<input type="hidden" name="mylist" value="1" />
				<?php echo form_security_field( 'query_store_mylist_' . $t_query_id ); ?>
				<input title="<?php echo $t_label; ?>" type="submit" class="<?php echo $t_class; ?>" value="<?php echo $t_label ;?>" />
			</form>
		</div>
	<?php
	}

	if( $t_filter_preferences->myViewHasFilter( $t_query_id ) ) {
		$t_label = string_attribute( lang_get( 'remove_query_from_myview' ) );
		$t_class= 'myview-has-filter';
	} else {
		$t_label = string_attribute( lang_get( 'add_query_to_myview' ) );
		$t_class= 'myview-filter-removed';
	}
	?>
		<div class="manage-stored-query-myview">
			<form name="stored_query_myview" method="post" action="<?php echo $t_action; ?>">
				<input type="hidden" name="filter_id" value="<?php echo $t_query_id; ?>" />
				<input type="hidden" name="myview" value="1" />
				<?php echo form_security_field( 'query_store_myview_' . $t_query_id ); ?>
				<input title="<?php echo $t_label; ?>" type="submit" class="<?php echo $t_class; ?>" value="<?php echo $t_label ;?>" />
			</form>
		</div>
	</li>
<?php
	$i++;
}
?>
		</ul>
	</div>
</div>
