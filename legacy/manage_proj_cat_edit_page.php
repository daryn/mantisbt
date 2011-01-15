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
 * @uses category_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

auth_reauthenticate();

$f_category_id		= gpc_get_int( 'id' );
$f_project_id		= gpc_get_int( 'project_id' );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

$t_row = category_get_row( $f_category_id );
$t_assigned_to = $t_row['user_id'];
$t_project_id = $t_row['project_id'];
$t_name = $t_row['name'];

html_page_top();

print_manage_menu( 'manage_proj_cat_edit_page.php' );
?>

<br />
<div>
<form method="post" action="manage_proj_cat_update.php">
<?php echo form_security_field( 'manage_proj_cat_update' ) ?>
<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>"/>
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'edit_project_category_title' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<input type="hidden" name="category_id" value="<?php echo string_attribute( $f_category_id ) ?>" />
		<?php echo lang_get( 'category' ) ?>
	</td>
	<td>
		<input type="text" name="name" size="32" maxlength="128" value="<?php echo string_attribute( $t_name ) ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<th class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</th>
	<td>
		<select name="assigned_to">
			<option value="0"></option>
			<?php print_assign_to_option_list( $t_assigned_to, $t_project_id ) ?>
		</select>
	</td>
</tr>
<tr>
	<td>
		&#160;
	</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'update_category_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border center">
	<form method="post" action="manage_proj_cat_delete.php">
		<?php echo form_security_field( 'manage_proj_cat_delete' ) ?>
		<input type="hidden" name="category_id" value="<?php echo string_attribute( $f_category_id ) ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'delete_category_button' ) ?>" />
	</form>
</div>

<?php
html_page_bottom();
