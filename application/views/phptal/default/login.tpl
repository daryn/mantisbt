<!--
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
-->
<html xmlns="http://www.w3.org/1999/xhtml" metal:use-macro="macros/layout.tpl/layout">


if( $f_error || $f_cookie_error ) {
	<div tal:condition="php: error OR cookie_error" class="important-msg">
		<ul>

	# Display short greeting message
	# echo lang_get( 'login_page_info' ) . <br />

	if ( $f_error ) {
		<li tal:condition="error" tal:content="lang/login_error">login_error </li>
	}
	if ( $f_cookie_error ) {
		<li>' . lang_get( 'login_cookies_disabled' ) . '</li>
	}
	</ul>
	</div>
}
?>

<!-- Login Form BEGIN -->
<div id="login-div" class="form-container">
	<form id="login-form" method="post" action="login.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'login_title' ) ?></span></legend>
			<?php
			if ( !is_blank( $f_return ) ) {
				echo '<input type="hidden" name="return" value="', string_html_specialchars( $f_return ), '" />';
			}
			# CSRF protection not required here - form does not result in modifications
			echo '<ul id="login-links">';

			if ( ON == config_get( 'allow_anonymous_login' ) ) {
				echo '<li><a href="login_anon.php?return=' . string_url( $f_return ) . '">' . lang_get( 'login_anonymously' ) . '</a></li>';
			}

			if ( ( ON == config_get_global( 'allow_signup' ) ) &&
				( LDAP != config_get_global( 'login_method' ) ) &&
				( ON == config_get( 'enable_email_notification' ) )
			) {
				echo '<li><a href="signup_page.php">', lang_get( 'signup_link' ), '</a></li>';
			}
			# lost password feature disabled or reset password via email disabled -> stop here!
			if ( ( LDAP != config_get_global( 'login_method' ) ) &&
				( ON == config_get( 'lost_password_feature' ) ) &&
				( ON == config_get( 'send_reset_password' ) ) &&
				( ON == config_get( 'enable_email_notification' ) ) ) {
				echo '<li><a href="lost_pwd_page.php">', lang_get( 'lost_password_link' ), '</a></li>';
			}
			?>
			</ul>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="username"><span><?php echo lang_get( 'username' ) ?></span></label>
				<span class="input"><input id="username" type="text" name="username" size="32" maxlength="<?php echo USERLEN;?>" value="<?php echo string_attribute( $f_username ); ?>" class="<?php echo $t_username_field_autofocus ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="password"><span><?php echo lang_get( 'password' ) ?></span></label>
				<span class="input"><input id="password" type="password" name="password" size="16" maxlength="<?php echo PASSLEN;?>" class="<?php echo $t_password_field_autofocus ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="remember-login"><span><?php echo lang_get( 'save_login' ) ?></span></label>
				<span class="input"><input id="remember-login" type="checkbox" name="perm_login" <?php echo ( $f_perm_login ? 'checked="checked" ' : '' ) ?>/></span>
				<span class="label-style"></span>
			</div>
			<?php if ( $t_session_validation ) { ?>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label id="secure-session-label" for="secure-session"><span><?php echo lang_get( 'secure_session' ) ?></span></label>
				<span class="input">
					<input id="secure-session" type="checkbox" name="secure_session" <?php echo ( $t_default_secure_session ? 'checked="checked" ' : '' ) ?>/>
					<span id="session-msg"><?php echo lang_get( 'secure_session_long' ); ?></span>
				</span>
				<span class="label-style"></span>
			</div>
			<?php } ?>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'login_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>
	<tal:block tal:condition="config/admin_checks"
		<div tal:condition="warnings" class="important-msg">
			<ul>
				<li tal:repeat="warning warnings" tal:content="warning">warning</li>
			</ul>
		</div>
	</tal:block>
</html>
