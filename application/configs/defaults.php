<?php
# MantisBT - A PHP based bugtracking system

# @todo add new liense text

/**
 * Default Configuration Variables
 *
 * This file should not be changed. If you want to override any of the values
 * defined here, define them in a file called config_inc.php, which will
 * be loaded after this file.
 *
 * In general a value of OFF means the feature is disabled and ON means the
 * feature is enabled.  Any other cases will have an explanation.
 *
 * For more details see http://www.mantisbt.org/docs/master-1.2.x/
 *
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/******************************
 * MantisBT Database Settings *
 ******************************/

/**
 * hostname should be either a hostname or connection string to supply to adodb.
 * For example, if you would like to connect to a database server on the local machine,
 * set hostname to 'localhost'
 * If you need to supply a port to connect to, set hostname as 'localhost:3306'.
 * @global string $t_config['hostname']
 */
$t_config['hostname']				= 'localhost';
/**
 * User name to use for connecting to the database. The user needs to have
 * read/write access to the MantisBT database. The default user name is "root".
 * @global string $t_config['db_username']
 */
$t_config['db_username']			= 'root';
/**
 * Password for the specified user name. The default password is empty.
 * @global string $t_config['db_password']
 */
$t_config['db_password']			= '';
 /**
  * Name of database that contains MantisBT tables.
  * The default database name is "bugtracker".
  * @global string $t_config['database_name']
  */
$t_config['database_name']		= 'bugtracker';

/**
 * Database Schema Name - used in the case of db2.
 * @global string $t_config['db_schema']
 */
$t_config['db_schema']			= '';

/**
 * Defines the database type. The supported default is 'mysql'.
 * Supported types: 'mysql' or 'mysqli' for MySQL, 'pgsql' for PostgreSQL,
 * 'odbc_mssql', 'mssql' for MS SQL Server, 'oci8' for Oracle, and 'db2' for
 * DB2.
 * @global string $t_config['db_type']
 */
$t_config['db_type']				= 'mysql';

/**
 * adodb Data Source Name
 * This is an EXPERIMENTAL field.
 * If the above database settings, do not provide enough flexibilty, it is
 * possible to specify a dsn for the database connection. For further details,
 * currently, you need to see the adodb manual at
 * http://phplens.com/adodb/code.initialization.html#dsnsupport. For example,
 * if db_type is odbc_mssql. The following is an example dsn:
 * "Driver={SQL Server Native Client 10.0};SERVER=.\sqlexpress;DATABASE=bugtracker2;UID=mantis;PWD=passwd;"
 * NOTE: the installer does not yet fully support the use of dsn's
 */
$t_config['dsn'] = '';

/**
 * Database Connection Options
 * e.g. array( 'dbpersist' ) to use persistent PDO connections
 */
$t_config['db_options'] = array();

/**************************
 * MantisBT Path Settings *
 **************************/

if ( isset ( $_SERVER['SCRIPT_NAME'] ) ) {
	$t_protocol = 'http';
	if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
		$t_protocol= $_SERVER['HTTP_X_FORWARDED_PROTO'];
	} else if ( isset( $_SERVER['HTTPS'] ) && ( strtolower( $_SERVER['HTTPS'] ) != 'off' ) ) {
		$t_protocol = 'https';
	}

	# $_SERVER['SERVER_PORT'] is not defined in case of php-cgi.exe
	if ( isset( $_SERVER['SERVER_PORT'] ) ) {
		$t_port = ':' . $_SERVER['SERVER_PORT'];
		if ( ( ':80' == $t_port && 'http' == $t_protocol )
		  || ( ':443' == $t_port && 'https' == $t_protocol )) {
			$t_port = '';
		}
	} else {
		$t_port = '';
	}

	if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) { // Support ProxyPass
		$t_hosts = explode( ',', $_SERVER['HTTP_X_FORWARDED_HOST'] );
		$t_host = $t_hosts[0];
	} else if ( isset( $_SERVER['HTTP_HOST'] ) ) {
		$t_host = $_SERVER['HTTP_HOST'];
	} else if ( isset( $_SERVER['SERVER_NAME'] ) ) {
		$t_host = $_SERVER['SERVER_NAME'] . $t_port;
	} else if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
		$t_host = $_SERVER['SERVER_ADDR'] . $t_port;
	} else {
		$t_host = 'localhost';
	}

	$t_path = str_replace( basename( $_SERVER['PHP_SELF'] ), '', $_SERVER['PHP_SELF'] );
	$t_path = basename( $t_path ) == "admin" ? rtrim( dirname( $t_path ), '/\\' ) . '/' : $t_path;
	$t_path = basename( $t_path ) == "soap" ? rtrim( dirname( dirname( $t_path ) ), '/\\' ) . '/' : $t_path;

	$t_url	= $t_protocol . '://' . $t_host . $t_path;

} else {
	$t_path = '';
	$t_host = '';
	$t_protocol = '';
}

/**
 * path to your installation as seen from the web browser
 * requires trailing /
 * @global string $t_config['path']
 */
$t_config['path']	= isset( $t_url ) ? $t_url : 'http://localhost/mantisbt/';

/**
 * path to your images directory (for icons)
 * requires trailing /
 * @global string $t_config['icon_path']
 */
$t_config['icon_path'] = '%path%images/';

/**
 * Short web path without the domain name
 * requires trailing /
 * @global string $t_config['short_path']
 */
$t_config['short_path'] = $t_path;

/**
 * absolute path to your installation.  Requires trailing / or \
 * @global string $t_config['absolute_path']
 */
$t_config['absolute_path'] = realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' ) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;

/**
 * absolute path to custom strings file.
 * This file allows overriding of strings declared in the language file, or in plugin language files
 * Two formats are supported:
 * Legacy format: $s_*
 * New format: define a $s_custom_messages array as follows:
 * $s_custom_messages = array( 'en' => array( string => string ) ) ;
 * NOTE: you can not mix/merge old/new formats within this file.
 * @global string $t_config['custom_strings_file']
 */
$t_config['custom_strings_file'] = CONFIG_PATH . DIRECTORY_SEPARATOR . 'custom_strings_inc.php';

/**
 * Used to link to manual for User Documentation.
 * @global string $t_config['manual_url']
 */
$t_config['manual_url'] = 'http://www.mantisbt.org/docs/master-1.2.x/';

/**************
 * Web Server *
 **************/

/**
 * Session handler.  Possible values:
 *  'php' -> Default PHP filesystem sessions
 *  'adodb' -> Database storage sessions
 *  'memcached' -> Memcached storage sessions
 * @global string $t_config['session_handler']
 */
$t_config['session_handler'] = 'php';

/**
 * Session save path.  If false, uses default value as set by session handler.
 * @global bool $t_config['session_save_path']
 */
$t_config['session_save_path'] = false;

/**
 * Session validation
 * WARNING: Disabling this could be a potential security risk!!
 * @global int $t_config['session_validation']
 */
$t_config['session_validation'] = ON;

/**
 * Form security validation.
 * This protects against Cross-Site Request Forgery, but some proxy servers may
 * not correctly work with this option enabled because they cache pages
 * incorrectly.
 * WARNING: Disabling this is a security risk!!
 */
$t_config['form_security_validation'] = ON;

/*****************************
 * Security and Cryptography *
 *****************************/

/**
 * Master salt value used for cryptographic hashing throughout MantisBT. This
 * value must be kept secret at all costs. You must generate a unique and
 * random salt value for each installation of MantisBT you control. The
 * minimum length of this string must be at least 16 characters.
 *
 * The value you select for this salt should be a long string generated using
 * a secure random number generator. An example for Linux systems is:
 *    cat /dev/urandom | head -c 64 | base64
 * Note that the number of bits of entropy per byte of output from /dev/urandom
 * is not 8. If you're particularly paranoid and don't mind waiting a long
 * time, you could use /dev/random to get much closer to 8 bits of entropy per
 * byte. Moving the mouse (if possible) while generating entropy via
 * /dev/random will greatly improve the speed at which /dev/random produces
 * entropy.
 *
 * WARNING: This configuration option has a profound impact on the security of
 * your MantisBT installation. Failure to set this configuration option
 * correctly could lead to your MantisBT installation being compromised. Ensure
 * that this value remains secret. Treat it with the same security that you'd
 * treat the password to your MantisDB database.
 *
 * This setting is blank by default. MantisBT will not operate in this state.
 * Hence you are forced to change the value of this configuration option.
 *
 * @global string $t_config['crypto_master_salt']
 */
$t_config['crypto_master_salt'] = '';

/****************************
 * Signup and Lost Password *
 ****************************/

/**
 * allow users to signup for their own accounts.
 * Mail settings must be correctly configured in order for this to work
 * @global int $t_config['allow_signup']
 */
$t_config['allow_signup']			= ON;

/**
 * Max. attempts to login using a wrong password before lock the account.
 * When locked, it's required to reset the password (lost password)
 * Value resets to zero at each successfully login
 * Set to OFF to disable this control
 * @global int $t_config['max_failed_login_count']
 */
$t_config['max_failed_login_count'] = OFF;

/**
 * access level required to be notified when a new user has been created using
 * the "signup form"
 * @global int $t_config['notify_new_user_created_threshold_min']
 */
$t_config['notify_new_user_created_threshold_min'] = ADMINISTRATOR;

/**
 * if ON users will be sent their password when reset.
 * if OFF the password will be set to blank. If set to ON, mail settings must be
 * correctly configured.
 * @global int $t_config['send_reset_password']
 */
$t_config['send_reset_password']	= ON;

/**
 * use captcha image to validate subscription it requires GD library installed
 * @global int $t_config['signup_use_captcha']
 */
$t_config['signup_use_captcha']	= ON;

/**
 * absolute path (with trailing slash!) to folder which contains your
 * TrueType-Font files used to create the captcha image and since 0.19.3 for
 * the Relationship Graphs
 * @global string $t_config['system_font_folder']
 */
$t_config['system_font_folder']	= '';

/**
 * font name used to create the captcha image. i.e. arial.ttf
 * (the font file has to exist in the system_font_folder)
 * @global string $t_config['font_per_captcha']
 */
$t_config['font_per_captcha']	= 'arial.ttf';

/**
 * Setting to disable the 'lost your password' feature.
 * @global int $t_config['lost_password_feature']
 */
$t_config['lost_password_feature'] = ON;

/**
 * Max. simultaneous requests of 'lost password'
 * When this value is reached, it's no longer possible to request new password
 * reset. Value resets to zero at each successfully login
 * @global int $t_config['max_lost_password_in_progress_count']
 */
$t_config['max_lost_password_in_progress_count'] = 3;

/***************************
 * MantisBT Email Settings *
 ***************************/

/**
 * Webmaster email address. This is shown publicly at the bottom of each page
 * and thus may be suspectible to being detected by spam email harvesters.
 * @global string $t_config['webmaster_email']
 */
$t_config['webmaster_email']		= 'webmaster@example.com';

/**
 * the sender email, part of 'From: ' header in emails
 * @global string $t_config['from_email']
 */
$t_config['from_email']			= 'noreply@example.com';

/**
 * the sender name, part of 'From: ' header in emails
 * @global string $t_config['from_name']
 */
$t_config['from_name']			= 'Mantis Bug Tracker';

/**
 * the return address for bounced mail
 * @global string $t_config['return_path_email']
 */
$t_config['return_path_email']	= 'admin@example.com';

/**
 * Allow email notification.
 * Set to ON to enable email notifications, OFF to disable them. Note that
 * disabling email notifications has no effect on emails generated as part
 * of the user signup process. When set to OFF, the password reset feature
 * is disabled. Additionally, notifications of administrators updating
 * accounts are not sent to users.
 * @global int $t_config['enable_email_notification']
 */
$t_config['enable_email_notification']	= ON;


/**
 * The following two config options allow you to control who should get email
 * notifications on different actions/statuses.  The first option
 * (default_notify_flags) sets the default values for different user
 * categories.  The user categories are:
 *
 *      'reporter': the reporter of the bug
 *       'handler': the handler of the bug
 *       'monitor': users who are monitoring a bug
 *      'bugnotes': users who have added a bugnote to the bug
 *      'explicit': users who are explicitly specified by the code based on the
 *                  action (e.g. user added to monitor list).
 * 'threshold_max': all users with access <= max
 * 'threshold_min': ..and with access >= min
 *
 * The second config option (notify_flags) sets overrides for specific
 * actions/statuses. If a user category is not listed for an action, the
 * default from the config option above is used.  The possible actions are:
 *
 *             'new': a new bug has been added
 *           'owner': a bug has been assigned to a new owner
 *        'reopened': a bug has been reopened
 *         'deleted': a bug has been deleted
 *         'updated': a bug has been updated
 *         'bugnote': a bugnote has been added to a bug
 *         'sponsor': sponsorship has changed on this bug
 *        'relation': a relationship has changed on this bug
 *         'monitor': an issue is monitored.
 *        '<status>': eg: 'resolved', 'closed', 'feedback', 'acknowledged', etc.
 *                     this list corresponds to $t_config['status_enum_string
 *
 * If you wanted to have all developers get notified of new bugs you might add
 * the following lines to your config file:
 *
 * $t_config['notify_flags']['new']['threshold_min'] = DEVELOPER;
 * $t_config['notify_flags']['new']['threshold_max'] = DEVELOPER;
 *
 * You might want to do something similar so all managers are notified when a
 * bug is closed.  If you didn't want reporters to be notified when a bug is
 * closed (only when it is resolved) you would use:
 *
 * $t_config['notify_flags']['closed']['reporter'] = OFF;
 *
 * @global array $t_config['default_notify_flags']
 */

$t_config['default_notify_flags'] = array(
	'reporter'      => ON,
	'handler'       => ON,
	'monitor'       => ON,
	'bugnotes'      => ON,
	'explicit'      => ON,
	'threshold_min' => NOBODY,
	'threshold_max' => NOBODY
);

/**
 * We don't need to send these notifications on new bugs
 * (see above for info on this config option)
 * @todo (though I'm not sure they need to be turned off anymore
 *      - there just won't be anyone in those categories)
 *      I guess it serves as an example and a placeholder for this
 *      config option
 * @see $t_config['default_notify_flags']
 * @global array $t_config['notify_flags']
 */
$t_config['notify_flags']['new'] = array(
	'bugnotes' => OFF,
	'monitor'  => OFF
);

$t_config['notify_flags']['monitor'] = array(
	'reporter'      => OFF,
	'handler'       => OFF,
	'monitor'       => OFF,
	'bugnotes'      => OFF,
	'explicit'      => ON,
	'threshold_min' => NOBODY,
	'threshold_max' => NOBODY
);

/**
 * Whether user's should receive emails for their own actions
 * @global int $t_config['email_receive_own']
 */
$t_config['email_receive_own'] = OFF;

/**
 * set to OFF to disable email check
 * @global int $t_config['validate_email']
 */
$t_config['validate_email'] = ON;

/**
 * set to OFF to disable email check
 * @global int $t_config['check_mx_record']
 */
$t_config['check_mx_record'] = OFF;

/**
 * if ON, allow the user to omit an email field
 * note if you allow users to create their own accounts, they
 * must specify an email at that point, no matter what the value
 * of this option is.  Otherwise they wouldn't get their passwords.
 * @global int $t_config['allow_blank_email']
 */
$t_config['allow_blank_email'] = OFF;

/**
 * Only allow and send email to addresses in the given domain
 * For example:
 * $t_config['limit_email_domain		= 'users.sourceforge.net';
 * @global string|int $t_config['limit_email_domain']
 */
$t_config['limit_email_domain'] = OFF;

/**
 * This specifies the access level that is needed to get the mailto: links.
 * @global int $t_config['show_user_email_threshold']
 */
$t_config['show_user_email_threshold'] = NOBODY;

/**
 * This specifies the access level that is needed to see realnames on user view
 * page
 * @global int $t_config['show_user_realname_threshold']
 */
$t_config['show_user_realname_threshold'] = NOBODY;

/**
 * If use_x_priority is set to ON, what should the value be?
 * Urgent = 1, Not Urgent = 5, Disable = 0
 * Note: some MTAs interpret X-Priority = 0 to mean 'Very Urgent'
 * @global int $t_config['mail_priority']
 */
$t_config['mail_priority'] = 3;

/**
 * select the method to mail by:
 * PHPMAILER_METHOD_MAIL - mail()
 * PHPMAILER_METHOD_SENDMAIL - sendmail
 * PHPMAILER_METHOD_SMTP - SMTP
 * @global int $t_config['phpMailer_method']
 */
$t_config['phpMailer_method'] = PHPMAILER_METHOD_MAIL;

/**
 * This option allows you to use a remote SMTP host.  Must use the phpMailer script
 * One or more hosts, separated by a semicolon, can be listed.
 * You can also specify a different port for each host by using this
 * format: [hostname:port] (e.g. "smtp1.example.com:25;smtp2.example.com").
 * Hosts will be tried in order.
 * @global string $t_config['smtp_host']
 */
$t_config['smtp_host'] = 'localhost';

/**
 * These options allow you to use SMTP Authentication when you use a remote
 * SMTP host with phpMailer.  If smtp_username is not '' then the username
 * and password will be used when logging in to the SMTP server.
 * @global string $t_config['smtp_username']
 */
$t_config['smtp_username'] = '';

/**
 * SMTP Server Authentication password
 * @global string $t_config['smtp_password']
 */
$t_config['smtp_password'] = '';

/**
 * This control the connection mode to SMTP server. Can be 'ssl' or 'tls'
 * @global string $t_config['smtp_connection_mode']
 */
$t_config['smtp_connection_mode'] = '';

/**
 * The smtp port to use.  The typical SMTP ports are 25 and 587.  The port to
 * use will depend on the SMTP server configuration and hence others may be
 * used.
 * @global int $t_config['smtp_port']
 */
$t_config['smtp_port'] = 25;

/**
 * It is recommended to use a cronjob or a scheduler task to send emails. The
 * cronjob should typically run every 5 minutes.  If no cronjob is used,then
 * user will have to wait for emails to be sent after performing an action
 * which triggers notifications.  This slows user performance.
 * @global int $t_config['email_send_using_cronjob']
 */
$t_config['email_send_using_cronjob'] = OFF;

/**
 * Specify whether e-mails should be sent with the category set or not. This
 * is tested with Microsoft Outlook.  More testing for this feature + other
 * formats will be added in the future.
 * OFF, EMAIL_CATEGORY_PROJECT_CATEGORY (format: [Project] Category)
 * @global int $t_config['email_set_category']
 */
$t_config['email_set_category'] = OFF;

/**
 * email separator and padding
 * @global string $t_config['email_separator1']
 */
$t_config['email_separator1'] = str_pad('', 70, '=');
/**
 * email separator and padding
 * @global string $t_config['email_separator2']
 */
$t_config['email_separator2'] = str_pad('', 70, '-');
/**
 * email separator and padding
 * @global int $t_config['email_padding_length']
 */
$t_config['email_padding_length']	= 28;

/***************************
 * MantisBT Version String *
 ***************************/

/**
 * Set to off by default to not expose version to users
 * @global int $t_config['show_version']
 */
$t_config['show_version'] = OFF;

/**
 * String appended to the MantisBT version when displayed to the user
 * @global string $t_config['version_suffix']
 */
$t_config['version_suffix'] = '';

/**
 * Custom copyright and licensing statement shown at the footer of each page.
 * Can contain HTML elements that are valid children of the <address> element.
 * This string is treated as raw HTML and thus you must use &amp; instead of &.
 * @global string $t_config['copyright_statement']
 */
$t_config['copyright_statement'] = '';

/******************************
 * MantisBT Language Settings *
 ******************************/

/**
 * If the language is set to 'auto', the actual language is determined by the
 * user agent (web browser) language preference.
 * @global string $t_config['default_language']
 */
$t_config['default_language'] = 'auto';

/**
 * list the choices that the users are allowed to choose
 * @global array $t_config['language_choices_arr']
 */
$t_config['language_choices_arr']	= array(
	'auto',
	'afrikaans',
	'amharic',
	'arabic',
	'arabicegyptianspoken',
	'breton',
	'bulgarian',
	'catalan',
	'chinese_simplified',
	'chinese_traditional',
	'croatian',
	'czech',
	'danish',
	'dutch',
	'english',
	'estonian',
	'finnish',
	'french',
	'galician',
	'german',
	'greek',
	'hebrew',
	'hungarian',
	'icelandic',
	'italian',
	'japanese',
	'korean',
	'latvian',
	'lithuanian',
	'macedonian',
	'norwegian_bokmal',
	'norwegian_nynorsk',
	'occitan',
	'polish',
	'portuguese_brazil',
	'portuguese_standard',
	'ripoarisch',
	'romanian',
	'russian',
	'serbian',
	'slovak',
	'slovene',
	'spanish',
	'swissgerman',
	'swedish',
	'tagalog',
	'turkish',
	'ukrainian',
	'urdu',
	'volapuk',
);

/**
 * Browser language mapping for 'auto' language selection
 * @global array $t_config['language_auto_map']
 */
$t_config['language_auto_map'] = array(
	'af' => 'afrikaans',
	'am' => 'amharic',
	'ar' => 'arabic',
	'arz' => 'arabicegyptianspoken',
	'bg' => 'bulgarian',
	'br' => 'breton',
	'ca' => 'catalan',
	'zh-cn, zh-sg, zh' => 'chinese_simplified',
	'zh-hk, zh-tw' => 'chinese_traditional',
	'cs' => 'czech',
	'da' => 'danish',
	'nl-be, nl' => 'dutch',
	'en-us, en-gb, en-au, en' => 'english',
	'et' => 'estonian',
	'fi' => 'finnish',
	'fr-ca, fr-be, fr-ch, fr' => 'french',
	'gl' => 'galician',
	'gsw' => 'swissgerman',
	'de-de, de-at, de-ch, de' => 'german',
	'he' => 'hebrew',
	'hu' => 'hungarian',
	'hr' => 'croatian',
	'is' => 'icelandic',
	'it-ch, it' => 'italian',
	'ja' => 'japanese',
	'ko' => 'korean',
	'ksh' => 'ripoarisch',
	'lt' => 'lithuanian',
	'lv' => 'latvian',
	'mk' => 'macedonian',
	'no' => 'norwegian_bokmal',
	'nn' => 'norwegian_nynorsk',
	'oc' => 'occitan',
	'pl' => 'polish',
	'pt-br' => 'portuguese_brazil',
	'pt' => 'portuguese_standard',
	'ro-mo, ro' => 'romanian',
	'ru-mo, ru-ru, ru-ua, ru' => 'russian',
	'sr' => 'serbian',
	'sk' => 'slovak',
	'sl' => 'slovene',
	'es-mx, es-co, es-ar, es-cl, es-pr, es' => 'spanish',
	'sv-fi, sv' => 'swedish',
	'tl' => 'tagalog',
	'tr' => 'turkish',
	'uk' => 'ukrainian',
	'vo' => 'volapuk',
);

/**
 * Fallback for automatic language selection
 * @global string $t_config['fallback_language']
 */
$t_config['fallback_language'] = 'english';

/*****************************
 * MantisBT Display Settings *
 *****************************/

/**
 * browser window title
 * @global string $t_config['window_title']
 */
$t_config['window_title'] = 'MantisBT';

/**
 * title at top of html page (empty by default, since there is a logo now)
 * @global string $t_config['page_title']
 */
$t_config['page_title'] = '';

/**
 * Check for admin directory, database upgrades, etc.
 * @global int $t_config['admin_checks']
 */
$t_config['admin_checks'] = ON;

/**
 * Favicon image
 * @global string $t_config['favicon_image']
 */
$t_config['favicon_image'] = 'images/favicon.ico';

/**
 * Logo
 * @global string $t_config['logo_image']
 */
$t_config['logo_image'] = 'images/mantis_logo.gif';

/**
 * Logo URL link
 * @global string $t_config['logo_url']
 */
$t_config['logo_url'] = '%default_home_page%';

/**
 * Re-authentication required for admin areas
 * @global int $t_config['reauthentication']
 */
$t_config['reauthentication'] = ON;

/**
 *
 * @global int $t_config['reauthentication_expiry']
 */
$t_config['reauthentication_expiry'] = TOKEN_EXPIRY_AUTHENTICATED;

/**
 * Specifies whether to enable support for project documents or not.
 * This feature is deprecated and is expected to be moved to a plugin
 * in the future.
 * @global int $t_config['enable_project_documentation']
 */
$t_config['enable_project_documentation'] = OFF;

/**
 * Display another instance of the menu at the bottom.  The top menu will still
 * remain.
 * @global int $t_config['show_footer_menu']
 */
$t_config['show_footer_menu'] = OFF;

/**
 * show extra menu bar with all available projects
 * @global int $t_config['show_project_menu_bar']
 */
$t_config['show_project_menu_bar'] = OFF;

/**
 * show assigned to names
 * This is in the view all pages
 * @global int $t_config['show_assigned_names']
 */
$t_config['show_assigned_names'] = ON;

/**
 * show priority as icon
 * OFF: Shows priority as icon in view all bugs page
 * ON:  Shows priority as text in view all bugs page
 * @global int $t_config['show_priority_text']
 */
$t_config['show_priority_text'] = OFF;

/**
 * Define the priority level at which a bug becomes significant. Significant
 * bugs are displayed with emphasis. Set this value to -1 to disable the
 * feature.
 * @global int $t_config['priority_significant_threshold']
 */
$t_config['priority_significant_threshold'] = HIGH;

/**
 * Define the severity level at which a bug becomes significant.
 * Significant bugs are displayed with emphasis. Set this value to -1 to
 * disable the feature.
 * @global int $t_config['severity_significant_threshold']
 */
$t_config['severity_significant_threshold'] = MAJOR;

/**
 * The default columns to be included in the View Issues Page.
 * This can be overriden using Manage -> Manage Configuration -> Manage Columns
 * Also each user can configure their own columns using My Account -> Manage
 * Columns. Some of the columns specified here can be removed automatically if
 * they conflict with other configuration. Or if the current user doesn't have
 * the necessary access level to view them. For example, sponsorship_total will
 * be removed if sponsorships are disabled. To include custom field 'xyz',
 * include the column name as 'custom_xyz'.
 *
 * Standard Column Names (i.e. names to choose from):
 * selection, edit, id, project_id, reporter_id, handler_id, priority,
 * reproducibility, projection, eta, resolution, fixed_in_version, view_state,
 * os, os_build, build (for product build), platform, version, date_submitted,
 * attachment, category, sponsorship_total, severity, status, last_updated,
 * summary, bugnotes_count, description, steps_to_reproduce,
 * additional_information
 *
 * @global array $t_config['view_issues_page_columns']
 */
$t_config['view_issues_page_columns'] = array (
	'selection', 'edit', 'priority', 'id', 'sponsorship_total',
	'bugnotes_count', 'attachment', 'category_id', 'severity', 'status',
	'last_updated', 'summary'
);

/**
 * The default columns to be included in the Print Issues Page. This can be
 * overriden using Manage -> Manage Configuration -> Manage Columns. Also each
 * user can configure their own columns using My Account -> Manage Columns.
 * @global array $t_config['print_issues_page_columns']
 */
$t_config['print_issues_page_columns'] = array (
	'selection', 'priority', 'id', 'sponsorship_total', 'bugnotes_count',
	'attachment', 'category_id', 'severity', 'status', 'last_updated', 'summary'
);

/**
 * The default columns to be included in the CSV export. This can be overriden
 * using Manage -> Manage Configuration -> Manage Columns. Also each user can
 * configure their own columns using My Account -> Manage Columns.
 * @global array $t_config['csv_columns']
 */
$t_config['csv_columns'] = array (
	'id', 'project_id', 'reporter_id', 'handler_id', 'priority',
	'severity', 'reproducibility', 'version', 'projection', 'category_id',
	'date_submitted', 'eta', 'os', 'os_build', 'platform', 'view_state',
	'last_updated', 'summary', 'status', 'resolution', 'fixed_in_version'
);

/**
 * The default columns to be included in the Excel export. This can be
 * overriden using Manage -> Manage Configuration -> Manage Columns. Also each
 * user can configure their own columns using My Account -> Manage Columns
 * @global array $t_config['excel_columns']
 */
$t_config['excel_columns'] = array (
	'id', 'project_id', 'reporter_id', 'handler_id', 'priority', 'severity',
	'reproducibility', 'version', 'projection', 'category_id',
	'date_submitted', 'eta', 'os', 'os_build', 'platform', 'view_state',
	'last_updated', 'summary', 'status', 'resolution', 'fixed_in_version'
);

/**
 * show projects when in All Projects mode
 * @global int $t_config['show_bug_project_links']
 */
$t_config['show_bug_project_links'] = ON;

/**
 * Position of the status colour legend, can be: POSITION_*
 * see constant_inc.php. (*: TOP , BOTTOM , or BOTH)
 * @global int $t_config['status_legend_position']
 */
$t_config['status_legend_position'] = STATUS_LEGEND_POSITION_BOTTOM;

/**
 * Show a legend with percentage of bug status
 * x% of all bugs are new, y% of all bugs are assigned and so on.
 * If set to ON it will printed below the status colour legend.
 * @global int $t_config['status_percentage_legend']
 */
$t_config['status_percentage_legend'] = OFF;

/**
 * Position of the filter box, can be: POSITION_*
 * POSITION_TOP, POSITION_BOTTOM, or POSITION_NONE for none.
 * @global int $t_config['filter_position']
 */
$t_config['filter_position'] = FILTER_POSITION_TOP;

/**
 * Position of action buttons when viewing issues.
 * Can be: POSITION_TOP, POSITION_BOTTOM, or POSITION_BOTH.
 * @global int $t_config['action_button_position']
 */
$t_config['action_button_position'] = POSITION_BOTTOM;

/**
 * show product versions in create, view and update screens
 * ON forces display even if none are defined
 * OFF suppresses display
 * AUTO suppresses the display if there are no versions defined for the project
 * @global int $t_config['show_product_version']
 */
$t_config['show_product_version'] = AUTO;

/**
 * The access level threshold at which users will see the date of release
 * for product versions. Dates will be shown next to the product version,
 * target version and fixed in version fields. Set this threshold to NOBODY
 * to disable the feature.
 * @global int $t_config['show_version_dates_threshold']
 */
$t_config['show_version_dates_threshold'] = NOBODY;

/**
 * show users with their real name or not
 * @global int $t_config['show_realname']
 */
$t_config['show_realname'] = OFF;

/**
 * leave off for now
 * @global int $t_config['differentiate_duplicates']
 */
$t_config['differentiate_duplicates'] = OFF;

/**
 * sorting for names in dropdown lists. If turned on, "Jane Doe" will be sorted
 * with the "D"s
 * @global int $t_config['sort_by_last_name']
 */
$t_config['sort_by_last_name'] = OFF;

/**
 * Show user avatar. The current implementation is based on
 * http://www.gravatar.com. Users will need to register there the same address
 * used in this MantisBT installation to have their avatar shown. Please note:
 * upon registration or avatar change, it takes some time for the updated
 * gravatar images to show on sites
 * @global int $t_config['show_avatar']
 */
$t_config['show_avatar'] = OFF;

/**
 * Only users above this threshold will have their avatar shown
 * @global int $t_config['show_avatar_threshold']
 */
$t_config['show_avatar_threshold'] = DEVELOPER;

/**
 * Default avatar for users without a gravatar account
 * @global string $t_config['default_avatar']
 */
$t_config['default_avatar'] = "%path%images/no_avatar.png";

/**
 * Show release dates on changelog
 * @global int $t_config['show_changelog_dates']
 */
$t_config['show_changelog_dates'] = ON;

/**
 * Show release dates on roadmap
 * @global int $t_config['show_roadmap_dates']
 */
$t_config['show_roadmap_dates'] = ON;

/**************************
 * MantisBT Time Settings *
 **************************/

/**
 * time for 'permanent' cookie to live in seconds (1 year)
 * @global int $t_config['cookie_time_length']
 */
$t_config['cookie_time_length'] = 30000000;

/**
 * minutes to wait before document is stale (in minutes)
 * @global int $t_config['content_expire']
 */
$t_config['content_expire'] = 0;

/**
 * The time (in seconds) to allow for page execution during long processes
 *  such as upgrading your database.
 * The default value of 0 indicates that the page should be allowed to
 *  execute until it is finished.
 * @global int $t_config['long_process_timeout']
 */
$t_config['long_process_timeout'] = 0;

/**************************
 * MantisBT Date Settings *
 **************************/

/**
 * date format strings defaults to ISO 8601 formatting
 * go to http://www.php.net/manual/en/function.date.php
 * for detailed instructions on date formatting
 * @global string $t_config['short_date_format']
 */
$t_config['short_date_format'] = 'Y-m-d';

/**
 * date format strings defaults to ISO 8601 formatting
 * go to http://www.php.net/manual/en/function.date.php
 * for detailed instructions on date formatting
 * @global string $t_config['normal_date_format']
 */
$t_config['normal_date_format'] = 'Y-m-d H:i';

/**
 * date format strings defaults to ISO 8601 formatting
 * go to http://www.php.net/manual/en/function.date.php
 * for detailed instructions on date formatting
 * @global string $t_config['complete_date_format']
 */
$t_config['complete_date_format'] = 'Y-m-d H:i T';

/**
 * jscalendar date format string
 * go to http://www.php.net/manual/en/function.date.php
 * for detailed instructions on date formatting
 * @global string $t_config['calendar_js_date_format']
 */
$t_config['calendar_js_date_format'] = '\%Y-\%m-\%d \%H:\%M';

/**
 * jscalendar date format string
 * go to http://www.php.net/manual/en/function.date.php
 * for detailed instructions on date formatting
 * @global string $t_config['calendar_date_format']
 */
$t_config['calendar_date_format'] = 'Y-m-d H:i';

/**************************
 * MantisBT TimeZone Settings *
 **************************/

/**
 * Default timezone to use in MantisBT.
 * See http://us.php.net/manual/en/timezones.php
 * for a list of valid timezones.
 * Note: if this is left blank, we use the result of
 * date_default_timezone_get() i.e. in order:
 * 1. Reading the TZ environment variable (if non empty)
 * 2. Reading the value of the date.timezone php.ini option (if set)
 * 3. Querying the host operating system (if supported and allowed by the OS)
 * 4. If none of the above succeed, will return a default timezone of UTC.
 * @global string $t_config['default_timezone']
 */
$t_config['default_timezone'] = '';

/**************************
 * MantisBT News Settings *
 **************************/

/**
 * Indicates whether the news feature should be enabled or disabled.
 * This feature is deprecated and is expected to be moved to a plugin
 * in the future.
 */
$t_config['news_enabled'] = OFF;

/**
 * Limit News Items
 * limit by entry count or date
 * BY_LIMIT - entry limit
 * BY_DATE - by date
 * @global int $t_config['news_limit_method']
 */
$t_config['news_limit_method'] = BY_LIMIT;

/**
 * limit by last X entries
 * @global int $t_config['news_view_limit']
 */
$t_config['news_view_limit'] = 7;

/**
 * limit by days
 * @global int $t_config['news_view_limit_days']
 */
$t_config['news_view_limit_days'] = 30;

/**
 * threshold for viewing private news
 * @global int $t_config['private_news_threshold']
 */
$t_config['private_news_threshold'] = DEVELOPER;

/********************************
 * MantisBT Default Preferences *
 ********************************/

/**
 * signup default
 * look in constant_inc.php for values
 * @global int $t_config['default_new_account_access_level']
 */
$t_config['default_new_account_access_level'] = REPORTER;

/**
 * Default Bug View Status (VS_PUBLIC or VS_PRIVATE)
 * @global int $t_config['default_bug_view_status']
 */
$t_config['default_bug_view_status'] = VS_PUBLIC;

/**
 * Default value for steps to reproduce field.
 * @global string $t_config['default_bug_steps_to_reproduce']
 */
$t_config['default_bug_steps_to_reproduce'] = '';

/**
 * Default value for addition information field.
 * @global string $t_config['default_bug_additional_info']
 */
$t_config['default_bug_additional_info'] = '';

/**
 * Default Bugnote View Status (VS_PUBLIC or VS_PRIVATE)
 * @global int $t_config['default_bugnote_view_status']
 */
$t_config['default_bugnote_view_status'] = VS_PUBLIC;

/**
 * Default bug resolution when reporting a new bug
 * @global int $t_config['default_bug_resolution']
 */
$t_config['default_bug_resolution'] = OPEN;

/**
 * Default bug severity when reporting a new bug
 * @global int $t_config['default_bug_severity']
 */
$t_config['default_bug_severity'] = MINOR;

/**
 * Default bug priority when reporting a new bug
 * @global int $t_config['default_bug_priority']
 */
$t_config['default_bug_priority'] = NORMAL;

/**
 * Default bug reproducibility when reporting a new bug
 * @global int $t_config['default_bug_reproducibility']
 */
$t_config['default_bug_reproducibility'] = REPRODUCIBILITY_HAVENOTTRIED;

/**
 * Default bug projection when reporting a new bug
 * @global int $t_config['default_bug_projection']
 */
$t_config['default_bug_projection'] = PROJECTION_NONE;

/**
 * Default bug ETA when reporting a new bug
 * @global int $t_config['default_bug_eta']
 */
$t_config['default_bug_eta'] = ETA_NONE;

/**
 * Default global category to be used when an issue is moved from a project to another
 * that doesn't have a category with a matching name.  The default is 1 which is the "General"
 * category that is created in the default database.
 */
$t_config['default_category_for_moves'] = 1;

/**
 *
 * @global int $t_config['default_limit_view']
 */
$t_config['default_limit_view'] = 50;

/**
 *
 * @global int $t_config['default_show_changed']
 */
$t_config['default_show_changed'] = 6;

/**
 *
 * @global int $t_config['hide_status_default']
 */
$t_config['hide_status_default'] = CLOSED;

/**
 *
 * @global string $t_config['show_sticky_issues']
 */
$t_config['show_sticky_issues'] = ON;

/**
 * make sure people aren't refreshing too often
 * in minutes
 * @global int $t_config['min_refresh_delay']
 */
$t_config['min_refresh_delay'] = 10;

/**
 * in minutes
 * @global int $t_config['default_refresh_delay']
 */
$t_config['default_refresh_delay'] = 30;

/**
 * in seconds
 * @global int $t_config['default_redirect_delay']
 */
$t_config['default_redirect_delay'] = 2;

/**
 *
 * @global string $t_config['default_bugnote_order']
 */
$t_config['default_bugnote_order'] = 'ASC';

/**
 *
 * @global int $t_config['default_email_on_new']
 */
$t_config['default_email_on_new'] = ON;

/**
 *
 * @global int $t_config['default_email_on_assigned']
 */
$t_config['default_email_on_assigned'] = ON;

/**
 *
 * @global int $t_config['default_email_on_feedback']
 */
$t_config['default_email_on_feedback'] = ON;

/**
 *
 * @global int $t_config['default_email_on_resolved']
 */
$t_config['default_email_on_resolved'] = ON;

/**
 *
 * @global int $t_config['default_email_on_closed']
 */
$t_config['default_email_on_closed'] = ON;

/**
 *
 * @global int $t_config['default_email_on_reopened']
 */
$t_config['default_email_on_reopened'] = ON;

/**
 *
 * @global int $t_config['default_email_on_bugnote']
 */
$t_config['default_email_on_bugnote'] = ON;

/**
 * @todo Unused
 * @global int $t_config['default_email_on_status']
 */
$t_config['default_email_on_status'] = 0;

/**
 * @todo Unused
 * @global int $t_config['default_email_on_priority']
 */
$t_config['default_email_on_priority'] = 0;

/**
 * 'any'
 * @global int $t_config['default_email_on_new_minimum_severity']
 */
$t_config['default_email_on_new_minimum_severity'] = OFF;

/**
 * 'any'
 * @global int $t_config['default_email_on_assigned_minimum_severity']
 */
$t_config['default_email_on_assigned_minimum_severity'] = OFF;

/**
 * 'any'
 * @global int $t_config['default_email_on_feedback_minimum_severity']
 */
$t_config['default_email_on_feedback_minimum_severity'] = OFF;

/**
 * 'any'
 * @global int $t_config['default_email_on_resolved_minimum_severity']
 */
$t_config['default_email_on_resolved_minimum_severity'] = OFF;

/**
 * 'any'
 * @global int $t_config['default_email_on_closed_minimum_severity']
 */
$t_config['default_email_on_closed_minimum_severity'] = OFF;

/**
 * 'any'
 * @global int $t_config['default_email_on_reopened_minimum_severity']
 */
$t_config['default_email_on_reopened_minimum_severity'] = OFF;

/**
 * 'any'
 * @global int $t_config['default_email_on_bugnote_minimum_severity']
 */
$t_config['default_email_on_bugnote_minimum_severity'] = OFF;

/**
 * 'any'
 * @global int $t_config['default_email_on_status_minimum_severity']
 */
$t_config['default_email_on_status_minimum_severity'] = OFF;

/**
 * @todo Unused
 * @global int $t_config['default_email_on_priority_minimum_severity']
 */
$t_config['default_email_on_priority_minimum_severity'] = OFF;

/**
 *
 * @global int $t_config['default_email_bugnote_limit']
 */
$t_config['default_email_bugnote_limit'] = 0;

/*****************************
 * MantisBT Summary Settings *
 *****************************/

/**
 * how many reporters to show
 * this is useful when there are hundreds of reporters
 * @global int $t_config['reporter_summary_limit']
 */
$t_config['reporter_summary_limit'] = 10;

/**
 * summary date displays
 * date lengths to count bugs by (in days)
 * @global array $t_config['date_partitions']
 */
$t_config['date_partitions'] = array( 1, 2, 3, 7, 30, 60, 90, 180, 365);

/**
 * shows project '[project] category' when 'All Projects' is selected
 * otherwise only 'category name'
 * @global int $t_config['summary_category_include_project']
 */
$t_config['summary_category_include_project'] = OFF;

/**
 * threshold for viewing summary
 * @global int $t_config['view_summary_threshold']
 */
$t_config['view_summary_threshold'] = MANAGER;

/**
 * Define the multipliers which are used to determine the effectiveness
 * of reporters based on the severity of bugs. Higher multipliers will
 * result in an increase in reporter effectiveness.
 * @global array $t_config['severity_multipliers']
 */
$t_config['severity_multipliers'] = array(
	FEATURE => 1,
	TRIVIAL => 2,
	TEXT    => 3,
	TWEAK   => 2,
	MINOR   => 5,
	MAJOR   => 8,
	CRASH   => 8,
	BLOCK   => 10
);

/**
 * Define the resolutions which are used to determine the effectiveness
 * of reporters based on the resolution of bugs. Higher multipliers will
 * result in a decrease in reporter effectiveness. The only resolutions
 * that need to be defined here are those which match or exceed
 * $t_config['bug_resolution_not_fixed_threshold.
 * @global array $t_config['resolution_multipliers']
 */
$t_config['resolution_multipliers'] = array(
	UNABLE_TO_DUPLICATE => 2,
	NOT_FIXABLE         => 1,
	DUPLICATE           => 3,
	NOT_A_BUG           => 5,
	SUSPENDED           => 1,
	WONT_FIX            => 1
);

/*****************************
 * MantisBT Bugnote Settings *
 *****************************/

/**
 * bugnote ordering
 * change to ASC or DESC
 * @global string $t_config['bugnote_order']
 */
$t_config['bugnote_order'] = 'DESC';

/*********************************
 * MantisBT Bug History Settings *
 *********************************/

/**
 * bug history visible by default when you view a bug
 * change to ON or OFF
 * @global int $t_config['history_default_visible']
 */
$t_config['history_default_visible'] = ON;

/**
 * bug history ordering
 * change to ASC or DESC
 * @global string $t_config['history_order']
 */
$t_config['history_order'] = 'ASC';

/******************************
 * MantisBT Reminder Settings *
 ******************************/

/**
 * are reminders stored as bugnotes
 * @global int $t_config['store_reminders']
 */
$t_config['store_reminders'] = ON;

/**
 * Automatically add recipients of reminders to monitor list, if they are not
 * the handler or the reporter (since they automatically get notified, if required)
 * If recipients of the reminders are below the monitor threshold, they will not be added.
 * @global int $t_config['reminder_recipients_monitor_bug']
 */
$t_config['reminder_recipients_monitor_bug'] = ON;

/**
 * Default Reminder View Status (VS_PUBLIC or VS_PRIVATE)
 * @global int $t_config['default_reminder_view_status']
 */
$t_config['default_reminder_view_status'] = VS_PUBLIC;

/**
 * The minimum access level required to show up in the list of users who can receive a reminder.
 * The access level is that of the project to which the issue belongs.
 * @global int $t_config['reminder_receive_threshold']
 */
$t_config['reminder_receive_threshold'] = DEVELOPER;

/*********************************
 * MantisBT Sponsorship Settings *
 *********************************/

/**
 * Whether to enable/disable the whole issue sponsorship feature
 * @global int $t_config['enable_sponsorship']
 */
$t_config['enable_sponsorship'] = OFF;

/**
 * Currency used for all sponsorships.
 * @global string $t_config['sponsorship_currency']
 */
$t_config['sponsorship_currency'] = 'US$';

/**
 * Access level threshold needed to view the total sponsorship for an issue by
 * all users.
 * @global int $t_config['view_sponsorship_total_threshold']
 */
$t_config['view_sponsorship_total_threshold'] = VIEWER;

/**
 * Access level threshold needed to view the users sponsoring an issue and the
 * sponsorship amount for each.
 * @global int $t_config['view_sponsorship_details_threshold']
 */
$t_config['view_sponsorship_details_threshold'] = VIEWER;

/**
 * Access level threshold needed to allow user to sponsor issues.
 * @global int $t_config['sponsor_threshold']
 */
$t_config['sponsor_threshold'] = REPORTER;

/**
 * Access level required to be able to handle sponsored issues.
 * @global int $t_config['handle_sponsored_bugs_threshold']
 */
$t_config['handle_sponsored_bugs_threshold'] = DEVELOPER;

/**
 * Access level required to be able to assign a sponsored issue to a user with
 * access level greater or equal to 'handle_sponsored_bugs_threshold'.
 * @global int $t_config['assign_sponsored_bugs_threshold']
 */
$t_config['assign_sponsored_bugs_threshold'] = MANAGER;

/**
 * Minimum sponsorship amount. If the user enters a value less than this, an
 * error will be prompted.
 * @global int $t_config['minimum_sponsorship_amount']
 */
$t_config['minimum_sponsorship_amount'] = 5;

/*********************************
 * MantisBT File Upload Settings *
 *********************************/

/**
 * --- file upload settings --------
 * This is the master setting to disable *all* file uploading functionality
 *
 * If you want to allow file uploads, you must also make sure that they are
 *  enabled in php.  You may need to add 'file_uploads = TRUE' to your php.ini
 *
 * See also: $t_config['upload_project_file_threshold, $t_config['upload_bug_file_threshold,
 *   $t_config['allow_reporter_upload
 * @global int $t_config['allow_file_upload']
 */
$t_config['allow_file_upload'] = ON;

/**
 * Upload destination: specify actual location in project settings
 * DISK, DATABASE, or FTP.
 * @global int $t_config['file_upload_method']
 */
$t_config['file_upload_method'] = DATABASE;

/**
 * When using FTP or DISK for storing uploaded files, this setting control
 * the access permissions they will have on the web server: with the default
 * value (0400) files will be read-only, and accessible only by the user
 * running the apache process (probably "apache" in Linux and "Administrator"
 * in Windows).
 * For more details on unix style permissions:
 * http://www.perlfect.com/articles/chmod.shtml
 * @global int $t_config['attachments_file_permissions']
 */
$t_config['attachments_file_permissions'] = 0400;

/**
 * FTP settings, used if $t_config['file_upload_method = FTP
 * @global string $t_config['file_upload_ftp_server']
 */
$t_config['file_upload_ftp_server'] = 'ftp.myserver.com';

/**
 *
 * @global string $t_config['file_upload_ftp_user']
 */
$t_config['file_upload_ftp_user'] = 'readwriteuser';

/**
 *
 * @global string $t_config['file_upload_ftp_pass']
 */
$t_config['file_upload_ftp_pass'] = 'readwritepass';

/**
 * Maximum file size that can be uploaded
 * Also check your PHP settings (default is usually 2MBs)
 * @global int $t_config['max_file_size']
 */
$t_config['max_file_size'] = 5000000;

/**
 * Files that are allowed or not allowed.  Separate items by commas.
 * eg. 'php,html,java,exe,pl'
 * if $t_config['allowed_files is filled in NO other file types will be allowed.
 * $t_config['disallowed_files takes precedence over $t_config['allowed_files
 * @global string $t_config['allowed_files']
 */
$t_config['allowed_files'] = '';

/**
 *
 * @global string $t_config['disallowed_files']
 */
$t_config['disallowed_files'] = '';

/**
 * prefix to be used for the file system names of files uploaded to projects.
 * Eg: doc-001-myprojdoc.zip
 * @global string $t_config['document_files_prefix']
 */
$t_config['document_files_prefix'] = 'doc';

/**
 * absolute path to the default upload folder.  Requires trailing / or \
 * @global string $t_config['absolute_path_default_upload_folder']
 */
$t_config['absolute_path_default_upload_folder'] = '';

/**
 * Enable support for sending files to users via a more efficient X-Sendfile
 * method. HTTP server software supporting this technique includes Lighttpd,
 * Cherokee, Apache with mod_xsendfile and nginx. You may need to set the
 * proceeding file_download_xsendfile_header_name option to suit the server you
 * are using.
 * @global int $t_config['file_download_method']
 */
$t_config['file_download_xsendfile_enabled'] = OFF;

/**
 * The name of the X-Sendfile header to use. Each server tends to implement
 * this functionality in a slightly different way and thus the naming
 * conventions for the header differ between each server. Lighttpd from v1.5,
 * Apache with mod_xsendfile and Cherokee web servers use X-Sendfile. nginx
 * uses X-Accel-Redirect and Lighttpd v1.4 uses X-LIGHTTPD-send-file.
 * @global string $t_config['file_download_xsendfile_header_name']
 */
$t_config['file_download_xsendfile_header_name'] = 'X-Sendfile';

/**************************
 * MantisBT HTML Settings *
 **************************/

/**
 * html tags
 * Set this flag to automatically convert www URLs and
 * email adresses into clickable links
 * @global int $t_config['html_make_links']
 */
$t_config['html_make_links'] = ON;

/**
 * These are the valid html tags for multi-line fields (e.g. description)
 * do NOT include a or img tags here
 * do NOT include tags that require attributes
 * @global string $t_config['html_valid_tags']
 */
$t_config['html_valid_tags'] = 'p, li, ul, ol, br, pre, i, b, u, em';

/**
 * These are the valid html tags for single line fields (e.g. issue summary).
 * do NOT include a or img tags here
 * do NOT include tags that require attributes
 * @global string $t_config['html_valid_tags_single_line']
 */
$t_config['html_valid_tags_single_line'] = 'i, b, u, em';

/**
 * maximum length of the description in a dropdown menu (for search)
 * set to 0 to disable truncations
 * @global int $t_config['max_dropdown_length']
 */
$t_config['max_dropdown_length'] = 40;

/**
 * This flag conntrolls whether pre-formatted text (delimited by <pre> tags
 *  is wrapped to a maximum linelength (defaults to 100 chars in strings_api)
 *  If turned off, the display may be wide when viewing the text
 * @global int $t_config['wrap_in_preformatted_text']
 */
$t_config['wrap_in_preformatted_text'] = ON;

/************************
 * MantisBT HR Settings *
 ************************/

/**
 * Horizontal Rule Size
 * @global int $t_config['hr_size']
 */
$t_config['hr_size'] = 1;

/**
 * Horizontal Rule Width
 * @global int $t_config['hr_width']
 */
$t_config['hr_width'] = 50;

/**************************
 * MantisBT LDAP Settings *
 **************************/

/**
 *
 * @global string $t_config['ldap_server']
 */
$t_config['ldap_server'] = 'ldaps://ldap.example.com.au/';

/**
 *
 * @global string $t_config['ldap_root_dn']
 */
$t_config['ldap_root_dn'] = 'dc=example,dc=com,dc=au';

/**
 * e.g. '(organizationname=*Traffic)'
 * @global string $t_config['ldap_organization']
 */
$t_config['ldap_organization'] = '';

/**
 * Use 'sAMAccountName' for Active Directory
 * @global string $t_config['ldap_uid_field']
 */
$t_config['ldap_uid_field'] = 'uid';

/**
 * The LDAP field for real name (i.e. common name).
 * @global string $t_config['ldap_uid_field']
 */
$t_config['ldap_realname_field'] = 'cn';

/**
 * The distinguished of the user account to use for binding to the LDAP server.
 * For example, 'CN=ldap,OU=Administrators,DC=example,DC=com'.
 *
 * @global string $t_config['ldap_bind_dn']
 */
$t_config['ldap_bind_dn'] = '';

/**
 * The password for the service account to be used for connecting to the LDAP server.
 *
 * @global string $t_config['ldap_bind_passwd']
 */
$t_config['ldap_bind_passwd'] = '';

/**
 * Should we send to the LDAP email address or what MySql tells us
 * @global int $t_config['use_ldap_email']
 */
$t_config['use_ldap_email'] = OFF;

/**
 * Whether or not to pull the real name from LDAP.
 * ON from LDAP, OFF from database.
 * @global int $t_config['use_ldap_realname']
 */
$t_config['use_ldap_realname'] = OFF;

/**
 * The LDAP Protocol Version, if 0, then the protocol version is not set.  For
 * Active Directory use version 3.
 *
 * @global int $t_config['ldap_protocol_version']
 */
$t_config['ldap_protocol_version'] = 0;

/**
 * Determines whether the LDAP library automatically follows referrals returned
 * by LDAP servers or not. This maps to LDAP_OPT_REFERRALS ldap library option.
 * For Active Directory, this should be set to OFF.
 *
 * @global int $t_config['ldap_follow_referrals']
 */
$t_config['ldap_follow_referrals'] = ON;

/**
 * For development purposes, this is a configuration option that allows
 * replacing the LDAP communication with a comma separated text file.  The text
 * file has a line per user. Each line includes: user name, user real name,
 * email, password.  For production systems this option should be set to ''.
 */
$t_config['ldap_simulation_file_path'] = '';

/*******************
 * Status Settings *
 *******************/

/**
 * Status to assign to the bug when submitted.
 * @global int $t_config['bug_submit_status']
 */
$t_config['bug_submit_status'] = NEW_;

/**
 * Status to assign to the bug when assigned.
 * @global int $t_config['bug_assigned_status']
 */
$t_config['bug_assigned_status'] = ASSIGNED;

/**
 * Status to assign to the bug when reopened.
 * @global int $t_config['bug_reopen_status']
 */
$t_config['bug_reopen_status'] = FEEDBACK;

/**
 * Status to assign to the bug when feedback is required from the issue
 * reporter. Once the reporter adds a note the status moves back from feedback
 * to $t_config['bug_assigned_status or $t_config['bug_submit_status.
 * @global int $t_config['bug_feedback_status']
 */
$t_config['bug_feedback_status'] = FEEDBACK;

/**
 * When a note is added to a bug currently in $t_config['bug_feedback_status, and the note
 * author is the bug's reporter, this option will automatically set the bug status
 * to $t_config['bug_submit_status or $t_config['bug_assigned_status if the bug is assigned to a
 * developer.  Defaults to enabled.
 * @global boolean $t_config['reassign_on_feedback']
 */
$t_config['reassign_on_feedback'] = ON;

/**
 * Resolution to assign to the bug when reopened.
 * @global int $t_config['bug_reopen_resolution']
 */
$t_config['bug_reopen_resolution'] = REOPENED;

/**
 * Default resolution to assign to a bug when it is resolved as being a
 * duplicate of another issue.
 * @global int $t_config['bug_duplicate_resolution']
 */
$t_config['bug_duplicate_resolution'] = DUPLICATE;

/**
 * Bug becomes readonly if its status is >= this status.  The bug becomes
 * read/write again if re-opened and its status becomes less than this
 * threshold.
 * @global int $t_config['bug_readonly_status_threshold']
 */
$t_config['bug_readonly_status_threshold'] = RESOLVED;

/**
 * Bug is resolved, ready to be closed or reopened.  In some custom
 * installations a bug may be considered as resolved when it is moved to a
 * custom (FIXED or TESTED) status.
 * @global int $t_config['bug_resolved_status_threshold']
 */
$t_config['bug_resolved_status_threshold'] = RESOLVED;

/**
 * Threshold resolution which denotes that a bug has been resolved and
 * successfully fixed by developers. Resolutions above this threshold
 * and below $t_config['bug_resolution_not_fixed_threshold are considered to be
 * resolved successfully.
 * @global int $t_config['bug_resolution_fixed_threshold']
 */
$t_config['bug_resolution_fixed_threshold'] = FIXED;

/**
 * Threshold resolution which denotes that a bug has been resolved without
 * being successfully fixed by developers. Resolutions above this
 * threshold are considered to be resolved in an unsuccessful way.
 * @global int $t_config['bug_resolution_not_fixed_threshold']
 */
$t_config['bug_resolution_not_fixed_threshold'] = UNABLE_TO_DUPLICATE;

/**
 * Bug is closed.  In some custom installations a bug may be considered as
 * closed when it is moved to a custom (COMPLETED or IMPLEMENTED) status.
 * @global int $t_config['bug_closed_status_threshold']
 */
$t_config['bug_closed_status_threshold'] = CLOSED;

/**
 * Automatically set status to ASSIGNED whenever a bug is assigned to a person.
 * This is useful for installations where assigned status is to be used when
 * the bug is in progress, rather than just put in a person's queue.
 * @global int $t_config['auto_set_status_to_assigned']
 */
$t_config['auto_set_status_to_assigned']	= ON;

/**
 * 'status_enum_workflow' defines the workflow, and reflects a simple
 *  2-dimensional matrix. For each existing status, you define which
 *  statuses you can go to from that status, e.g. from NEW_ you might list statuses
 *  '10:new,20:feedback,30:acknowledged' but not higher ones.
 * The following example can be transferred to config_inc.php
 * $t_config['status_enum_workflow'][NEW_]='20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved';
 * $t_config['status_enum_workflow'][FEEDBACK] ='10:new,30:acknowledged,40:confirmed,50:assigned,80:resolved';
 * $t_config['status_enum_workflow'][ACKNOWLEDGED] ='20:feedback,40:confirmed,50:assigned,80:resolved';
 * $t_config['status_enum_workflow'][CONFIRMED] ='20:feedback,50:assigned,80:resolved';
 * $t_config['status_enum_workflow'][ASSIGNED] ='20:feedback,80:resolved,90:closed';
 * $t_config['status_enum_workflow'][RESOLVED] ='50:assigned,90:closed';
 * $t_config['status_enum_workflow'][CLOSED] ='50:assigned';
 * @global array $t_config['status_enum_workflow']
 */
$t_config['status_enum_workflow'] = array();

/****************************
 * Bug Attachments Settings *
 ****************************/

/**
 * Specify the filename of the magic database file. This is used by
 * PHP 5.3.0 (or earlier versions with the fileinfo PECL extension) to
 * guess what the MIME type of a file is. Usually it is safe to leave this
 * setting as the default (blank) as PHP is usually able to find this file
 * by itself.
 * @global string $t_config['fileinfo_magic_db_file']
 */
$t_config['fileinfo_magic_db_file'] = '';

/**
 * Specifies the maximum size (in bytes) below which an attachment is
 * previewed in the bug view pages.
 * To disable the previewing of attachments, set max size to 0.
 * @global int $t_config['preview_attachments_inline_max_size']
 */
$t_config['preview_attachments_inline_max_size'] = 256 * 1024;

/**
 * Extensions for text files that can be expanded inline.
 * @global array $t_config['preview_text_extensions']
 */
$t_config['preview_text_extensions'] = array(
	'', 'txt', 'diff', 'patch'
);

/**
 * Extensions for images that can be expanded inline.
 * @global array $t_config['preview_image_extensions']
 */
$t_config['preview_image_extensions'] = array(
	'bmp', 'png', 'gif', 'jpg', 'jpeg'
);

/**
 * Specifies the maximum width for the auto-preview feature. If no maximum
 * width should be imposed then it should be set to 0.
 * @global int $t_config['preview_max_width']
 */
$t_config['preview_max_width'] = 0;

/**
 * Specifies the maximum height for the auto-preview feature. If no maximum
 * height should be imposed then it should be set to 0.
 * @global int $t_config['preview_max_height']
 */
$t_config['preview_max_height'] = 250;

/**
 * Show an attachment indicator on bug list. Show a clickable attachment
 * indicator on the bug list page if the bug has one or more files attached.
 * Note: This option is disabled by default since it adds 1 database query per
 * bug listed and thus might slow down the page display.
 *
 * @global int $t_config['show_attachment_indicator']
 */
$t_config['show_attachment_indicator'] = OFF;

/**
 * access level needed to view bugs attachments.  View means to see the file
 * names, sizes, and timestamps of the attachments.
 * @global int $t_config['view_attachments_threshold']
 */
$t_config['view_attachments_threshold'] = VIEWER;

/**
 * list of filetypes to view inline. This is a string of extentions separated
 * by commas. This is used when downloading an attachment. Rather than
 * downloading, the attachment is viewed in the browser.
 * @global string $t_config['inline_file_exts']
 */
$t_config['inline_file_exts'] = 'gif,png,jpg,jpeg,bmp';

/**
 * access level needed to download bug attachments
 * @global int $t_config['download_attachments_threshold']
 */
$t_config['download_attachments_threshold'] = VIEWER;

/**
 * access level needed to delete bug attachments
 * @global int $t_config['delete_attachments_threshold']
 */
$t_config['delete_attachments_threshold'] = DEVELOPER;

/**
 * allow users to view attachments uploaded by themselves even if their access
 * level is below view_attachments_threshold.
 * @global int $t_config['allow_view_own_attachments']
 */
$t_config['allow_view_own_attachments'] = ON;

/**
 * allow users to download attachments uploaded by themselves even if their
 * access level is below download_attachments_threshold.
 * @global int $t_config['allow_download_own_attachments']
 */
$t_config['allow_download_own_attachments'] = ON;

/**
 * allow users to delete attachments uploaded by themselves even if their access
 * level is below delete_attachments_threshold.
 * @global int $t_config['allow_delete_own_attachments']
 */
$t_config['allow_delete_own_attachments'] = OFF;

/**********************
 * Field Visibility
 **********************/

/**
 * Enable or disable usage of the ETA field.
 * @global int $t_config['enable_eta']
 */
$t_config['enable_eta'] = OFF;

/**
 * Enable or disable usage of the Projection field.
 * @global int $t_config['enable_projection']
 */
$t_config['enable_projection'] = OFF;

/**
 * Enable or disable usage of the Product Build field.
 * @global int $t_config['enable_product_build']
 */
$t_config['enable_product_build'] = OFF;

/**
 * An array of optional fields to show on the bug report page.
 *
 * The following optional fields are allowed:
 *   - additional_info
 *   - attachments
 *   - category_id
 *   - due_date
 *   - handler
 *   - os
 *   - os_version
 *   - platform
 *   - priority
 *   - product_build
 *   - product_version
 *   - reproducibility
 *   - severity
 *   - steps_to_reproduce
 *   - target_version
 *   - view_state
 *
 * The summary and description fields are always shown and do not need to be
 * listed in this option. Fields not listed above cannot be shown on the bug
 * report page. Visibility of custom fields is handled via the Manage =>
 * Manage Custom Fields administrator page.
 *
 * This setting can be set on a per-project basis by using the
 * Manage => Manage Configuration administrator page.
 *
 * @global array $t_config['bug_report_page_fields']
 */
$t_config['bug_report_page_fields'] = array(
	'additional_info',
	'attachments',
	'category_id',
	'due_date',
	'handler',
	'os',
	'os_version',
	'platform',
	'priority',
	'product_build',
	'product_version',
	'reproducibility',
	'severity',
	'steps_to_reproduce',
	'target_version',
	'view_state',
);

/**
 * An array of optional fields to show on the bug view page.
 *
 * The following optional fields are allowed:
 *   - additional_info
 *   - attachments
 *   - category_id
 *   - date_submitted
 *   - description
 *   - due_date
 *   - eta
 *   - fixed_in_version
 *   - handler
 *   - id
 *   - last_updated
 *   - os
 *   - os_version
 *   - platform
 *   - priority
 *   - product_build
 *   - product_version
 *   - project
 *   - projection
 *   - reporter
 *   - reproducibility
 *   - resolution
 *   - severity
 *   - status
 *   - steps_to_reproduce
 *   - summary
 *   - tags
 *   - target_version
 *   - view_state
 *
 * Fields not listed above cannot be shown on the bug view page. Visibility of
 * custom fields is handled via the Manage => Manage Custom Fields
 * administrator page.
 *
 * This setting can be set on a per-project basis by using the
 * Manage => Manage Configuration administrator page.
 *
 * @global array $t_config['bug_view_page_fields']
 */
$t_config['bug_view_page_fields'] = array (
	'additional_info',
	'attachments',
	'category_id',
	'date_submitted',
	'description',
	'due_date',
	'eta',
	'fixed_in_version',
	'handler',
	'id',
	'last_updated',
	'os',
	'os_version',
	'platform',
	'priority',
	'product_build',
	'product_version',
	'project',
	'projection',
	'reporter',
	'reproducibility',
	'resolution',
	'severity',
	'status',
	'steps_to_reproduce',
	'summary',
	'tags',
	'target_version',
	'view_state',
);

/**
 * An array of optional fields to show on the bug print page.
 *
 * The following optional fields are allowed:
 *   - additional_info
 *   - attachments
 *   - category_id
 *   - date_submitted
 *   - description
 *   - due_date
 *   - eta
 *   - fixed_in_version
 *   - handler
 *   - id
 *   - last_updated
 *   - os
 *   - os_version
 *   - platform
 *   - priority
 *   - product_build
 *   - product_version
 *   - project
 *   - projection
 *   - reporter
 *   - reproducibility
 *   - resolution
 *   - severity
 *   - status
 *   - steps_to_reproduce
 *   - summary
 *   - tags
 *   - target_version
 *   - view_state
 *
 * Fields not listed above cannot be shown on the bug print page. All custom
 * field values are shown on the bug print page.
 *
 * This setting can be set on a per-project basis by using the
 * Manage => Manage Configuration administrator page.
 *
 * @global array $t_config['bug_print_page_fields']
 */
$t_config['bug_print_page_fields'] = array (
	'additional_info',
	'attachments',
	'category_id',
	'date_submitted',
	'description',
	'due_date',
	'eta',
	'fixed_in_version',
	'handler',
	'id',
	'last_updated',
	'os',
	'os_version',
	'platform',
	'priority',
	'product_build',
	'product_version',
	'project',
	'projection',
	'reporter',
	'reproducibility',
	'resolution',
	'severity',
	'status',
	'steps_to_reproduce',
	'summary',
	'tags',
	'target_version',
	'view_state',
);

/**
 * An array of optional fields to show on the bug update page.
 *
 * The following optional fields are allowed:
 *   - additional_info
 *   - category_id
 *   - date_submitted
 *   - description
 *   - due_date
 *   - eta
 *   - fixed_in_version
 *   - handler
 *   - id
 *   - last_updated
 *   - os
 *   - os_version
 *   - platform
 *   - priority
 *   - product_build
 *   - product_version
 *   - project
 *   - projection
 *   - reporter
 *   - reproducibility
 *   - resolution
 *   - severity
 *   - status
 *   - steps_to_reproduce
 *   - summary
 *   - target_version
 *   - view_state
 *
 * Fields not listed above cannot be shown on the bug update page. Visibility
 * of custom fields is handled via the Manage => Manage Custom Fields
 * administrator page.
 *
 * This setting can be set on a per-project basis by using the
 * Manage => Manage Configuration administrator page.
 *
 * @global array $t_config['bug_update_page_fields']
 */
$t_config['bug_update_page_fields'] = array (
	'additional_info',
	'category_id',
	'date_submitted',
	'description',
	'due_date',
	'eta',
	'fixed_in_version',
	'handler',
	'id',
	'last_updated',
	'os',
	'os_version',
	'platform',
	'priority',
	'product_build',
	'product_version',
	'project',
	'projection',
	'reporter',
	'reproducibility',
	'resolution',
	'severity',
	'status',
	'steps_to_reproduce',
	'summary',
	'target_version',
	'view_state',
);

/**
 * An array of optional fields to show on the bug change status page. This
 * only changes the visibibility of fields shown below the form used for
 * updating the status of an issue.
 *
 * The following optional fields are allowed:
 *   - additional_info
 *   - attachments
 *   - category_id
 *   - date_submitted
 *   - description
 *   - due_date
 *   - eta
 *   - fixed_in_version
 *   - handler
 *   - id
 *   - last_updated
 *   - os
 *   - os_version
 *   - platform
 *   - priority
 *   - product_build
 *   - product_version
 *   - project
 *   - projection
 *   - reporter
 *   - reproducibility
 *   - resolution
 *   - severity
 *   - status
 *   - steps_to_reproduce
 *   - summary
 *   - tags
 *   - target_version
 *   - view_state
 *
 * Fields not listed above cannot be shown on the bug change status page.
 * Visibility of custom fields is handled via the Manage =>
 * Manage Custom Fields administrator page (use the same settings as the
 * bug view page).
 *
 * This setting can be set on a per-project basis by using the
 * Manage => Manage Configuration administrator page.
 *
 * @global array $t_config['bug_change_status_page_fields']
 */
$t_config['bug_change_status_page_fields'] = array (
	'additional_info',
	'attachments',
	'category_id',
	'date_submitted',
	'description',
	'due_date',
	'eta',
	'fixed_in_version',
	'handler',
	'id',
	'last_updated',
	'os',
	'os_version',
	'platform',
	'priority',
	'product_build',
	'product_version',
	'project',
	'projection',
	'reporter',
	'reproducibility',
	'resolution',
	'severity',
	'status',
	'steps_to_reproduce',
	'summary',
	'tags',
	'target_version',
	'view_state',
);

/**************************
 * MantisBT Misc Settings *
 **************************/

/**
 * access level needed to report a bug
 * @global int $t_config['report_bug_threshold']
 */
$t_config['report_bug_threshold'] = REPORTER;

/**
 * access level needed to update bugs (i.e., the update_bug_page)
 * This controls whether the user sees the "Update Bug" button in bug_view*_page
 * and the pencil icon in view_all_bug_page
 * @global int $t_config['update_bug_threshold']
 */
$t_config['update_bug_threshold'] = UPDATER;

/**
 * Access level needed to monitor bugs.
 * Look in the constant_inc.php file if you want to set a different value.
 * @global int $t_config['monitor_bug_threshold']
 */
$t_config['monitor_bug_threshold'] = REPORTER;

/**
 * Access level needed to add other users to the list of users monitoring
 * a bug.
 * Look in the constant_inc.php file if you want to set a different value.
 * @global int $t_config['monitor_add_others_bug_threshold']
 */
$t_config['monitor_add_others_bug_threshold'] = DEVELOPER;

/**
 * Access level needed to delete other users from the list of users
 * monitoring a bug.
 * Look in the constant_inc.php file if you want to set a different value.
 * @global int $t_config['monitor_add_others_bug_threshold']
 */
$t_config['monitor_delete_others_bug_threshold'] = DEVELOPER;

/**
 * access level needed to view private bugs
 * Look in the constant_inc.php file if you want to set a different value
 * @global int $t_config['private_bug_threshold']
 */
$t_config['private_bug_threshold'] = DEVELOPER;

/**
 * access level needed to be able to be listed in the assign to field.
 * @global int $t_config['handle_bug_threshold']
 */
$t_config['handle_bug_threshold'] = DEVELOPER;

/**
 * access level needed to show the Assign To: button bug_view*_page or
 *  the Assigned list in bug_update*_page.
 *  This allows control over who can route bugs
 * This defaults to $t_config['handle_bug_threshold
 * @global int $t_config['update_bug_assign_threshold']
 */
$t_config['update_bug_assign_threshold'] = '%handle_bug_threshold%';

/**
 * access level needed to view private bugnotes
 * Look in the constant_inc.php file if you want to set a different value
 * @global int $t_config['private_bugnote_threshold']
 */
$t_config['private_bugnote_threshold'] = DEVELOPER;

/**
 * access level needed to view handler in bug reports and notification email
 * @todo yarick123: now it is implemented for notification email only
 * @global int $t_config['view_handler_threshold']
 */
$t_config['view_handler_threshold'] = VIEWER;

/**
 * access level needed to view history in bug reports and notification email
 * @todo yarick123: now it is implemented for notification email only
 * @global int $t_config['view_history_threshold']
 */
$t_config['view_history_threshold'] = VIEWER;

/**
 * access level needed to send a reminder from the bug view pages
 * set to NOBODY to disable the feature
 * @global int $t_config['bug_reminder_threshold']
 */
$t_config['bug_reminder_threshold'] = DEVELOPER;

/**
 * Access lever required to drop bug history revisions
 * @global int $t_config['bug_revision_drop_threshold']
 */
$t_config['bug_revision_drop_threshold'] = MANAGER;

/**
 * access level needed to upload files to the project documentation section
 * You can set this to NOBODY to prevent uploads to projects
 * See also: $t_config['upload_bug_file_threshold, $t_config['allow_file_upload
 * @global int $t_config['upload_project_file_threshold']
 */
$t_config['upload_project_file_threshold'] = MANAGER;

/**
 * access level needed to upload files to attach to a bug
 * You can set this to NOBODY to prevent uploads to bugs but note that
 *  the reporter of the bug will still be able to upload unless you set
 *  $t_config['allow_reporter_upload or $t_config['allow_file_upload to OFF
 * See also: $t_config['upload_project_file_threshold, $t_config['allow_file_upload,
 *			$t_config['allow_reporter_upload
 * @global int $t_config['upload_bug_file_threshold']
 */
$t_config['upload_bug_file_threshold'] = REPORTER;

/**
 * Add bugnote threshold
 * @global int $t_config['add_bugnote_threshold']
 */
$t_config['add_bugnote_threshold'] = REPORTER;

/**
 * Threshold at which a user can edit the bugnotes of other users
 * @global int $t_config['update_bugnote_threshold']
 */
$t_config['update_bugnote_threshold'] = DEVELOPER;

/**
 * Threshold needed to view project documentation
 * @global int $t_config['view_proj_doc_threshold']
 */
$t_config['view_proj_doc_threshold'] = ANYBODY;

/**
 * Site manager
 * @global int $t_config['manage_site_threshold']
 */
$t_config['manage_site_threshold'] = MANAGER;

/**
 * Threshold at which a user is considered to be a site administrator.
 * These users have "superuser" access to all aspects of MantisBT including
 * the admin/ directory. WARNING: DO NOT CHANGE THIS VALUE UNLESS YOU
 * ABSOLUTELY KNOW WHAT YOU'RE DOING! Users at this access level have the
 * ability to damage your MantisBT installation and data within the database.
 * It is strongly advised you leave this option alone.
 * @global int $t_config['admin_site_threshold']
 */
$t_config['admin_site_threshold'] = ADMINISTRATOR;

/**
 * Threshold needed to manage a project: edit project
 * details (not to add/delete projects) ...etc.
 * @global int $t_config['manage_project_threshold']
 */
$t_config['manage_project_threshold'] = MANAGER;

/**
 * Threshold needed to add/delete/modify news
 * @global int $t_config['manage_news_threshold']
 */
$t_config['manage_news_threshold'] = MANAGER;

/**
 * Threshold required to delete a project
 * @global int $t_config['delete_project_threshold']
 */
$t_config['delete_project_threshold'] = ADMINISTRATOR;

/**
 * Threshold needed to create a new project
 * @global int $t_config['create_project_threshold']
 */
$t_config['create_project_threshold'] = ADMINISTRATOR;

/**
 * Threshold needed to be automatically included in private projects
 * @global int $t_config['private_project_threshold']
 */
$t_config['private_project_threshold'] = ADMINISTRATOR;

/**
 * Threshold needed to manage user access to a project
 * @global int $t_config['project_user_threshold']
 */
$t_config['project_user_threshold'] = MANAGER;

/**
 * Threshold needed to manage user accounts
 * @global int $t_config['manage_user_threshold']
 */
$t_config['manage_user_threshold'] = ADMINISTRATOR;

/**
 * Delete bug threshold
 * @global int $t_config['delete_bug_threshold']
 */
$t_config['delete_bug_threshold'] = DEVELOPER;

/**
 * Threshold at which a user can delete the bugnotes of other users.
 * The default value is equal to the configuration setting
 * $t_config['delete_bug_threshold.
 * @global string $t_config['delete_bugnote_threshold']
 */
$t_config['delete_bugnote_threshold'] = '%delete_bug_threshold%';

/**
 * Move bug threshold
 * @global int $t_config['move_bug_threshold']
 */
$t_config['move_bug_threshold'] = DEVELOPER;

/**
 * Threshold needed to set the view status while reporting a bug or a bug note.
 * @global int $t_config['set_view_status_threshold']
 */
$t_config['set_view_status_threshold'] = REPORTER;

/**
 * Threshold needed to update the view status while updating a bug or a bug note.
 * This threshold should be greater or equal to $t_config['set_view_status_threshold.
 * @global int $t_config['change_view_status_threshold']
 */
$t_config['change_view_status_threshold'] = UPDATER;

/**
 * Threshold needed to show the list of users montoring a bug on the bug view pages.
 * @global int $t_config['show_monitor_list_threshold']
 */
$t_config['show_monitor_list_threshold'] = DEVELOPER;

/**
 * Threshold needed to be able to use stored queries
 * @global int $t_config['stored_query_use_threshold']
 */
$t_config['stored_query_use_threshold'] = REPORTER;

/**
 * Threshold needed to be able to create stored queries
 * @global int $t_config['stored_query_create_threshold']
 */
$t_config['stored_query_create_threshold'] = DEVELOPER;

/**
 * Threshold needed to be able to create shared stored queries
 * @global int $t_config['stored_query_create_shared_threshold']
 */
$t_config['stored_query_create_shared_threshold'] = MANAGER;

/**
 * Threshold needed to update readonly bugs.  Readonly bugs are identified via
 * $t_config['bug_readonly_status_threshold.
 * @global int $t_config['update_readonly_bug_threshold']
 */
$t_config['update_readonly_bug_threshold'] = MANAGER;

/**
 * threshold for viewing changelog
 * @global int $t_config['view_changelog_threshold']
 */
$t_config['view_changelog_threshold'] = VIEWER;

/**
 * threshold for viewing roadmap
 * @global int $t_config['roadmap_view_threshold']
 */
$t_config['roadmap_view_threshold'] = VIEWER;

/**
 * threshold for updating roadmap, target_version, etc
 * @global int $t_config['roadmap_update_threshold']
 */
$t_config['roadmap_update_threshold'] = DEVELOPER;

/**
 * status change thresholds
 * @global int $t_config['update_bug_status_threshold']
 */
$t_config['update_bug_status_threshold'] = DEVELOPER;

/**
 * access level needed to re-open bugs
 * @global int $t_config['reopen_bug_threshold']
 */
$t_config['reopen_bug_threshold'] = DEVELOPER;

/**
 * access level needed to assign bugs to unreleased product versions
 * @global int $t_config['report_issues_for_unreleased_versions_threshold']
 */
$t_config['report_issues_for_unreleased_versions_threshold'] = DEVELOPER;

/**
 * access level needed to set a bug sticky
 * @global int $t_config['set_bug_sticky_threshold']
 */
$t_config['set_bug_sticky_threshold'] = MANAGER;

/**
 * The minimum access level for someone to be a member of the development team
 * and appear on the project information page.
 * @global int $t_config['development_team_threshold']
 */
$t_config['development_team_threshold'] = DEVELOPER;

/**
 * this array sets the access thresholds needed to enter each status listed.
 * if a status is not listed, it falls back to $t_config['update_bug_status_threshold
 * example:
 * $t_config['set_status_threshold = array(
 *     ACKNOWLEDGED => MANAGER,
 *     CONFIRMED => DEVELOPER,
 *     CLOSED => MANAGER
 * );
 * @global array $t_config['set_status_threshold']
 */
$t_config['set_status_threshold'] = array();

/**
 * Threshold at which a user can edit his/her own bugnotes.
 * The default value is equal to the configuration setting
 * $t_config['update_bugnote_threshold.
 * @global int $t_config['bugnote_user_edit_threshold']
 */
$t_config['bugnote_user_edit_threshold'] = '%update_bugnote_threshold%';

/**
 * Threshold at which a user can delete his/her own bugnotes.
 * The default value is equal to the configuration setting
 * $t_config['delete_bugnote_threshold.
 * @global int $t_config['bugnote_user_delete_threshold']
 */
$t_config['bugnote_user_delete_threshold'] = '%delete_bugnote_threshold%';

/**
 * Threshold at which a user can change the view state of his/her own bugnotes.
 * The default value is equal to the configuration setting
 * $t_config['change_view_status_threshold.
 * @global int $t_config['bugnote_user_change_view_state_threshold']
 */
$t_config['bugnote_user_change_view_state_threshold'] = '%change_view_status_threshold%';

/**
 * Allow a bug to have no category
 * @global int $t_config['allow_no_category']
 */
$t_config['allow_no_category'] = OFF;

/**
 * login method
 * CRYPT or PLAIN or MD5 or LDAP or BASIC_AUTH. You can simply change this at
 * will. MantisBT will try to figure out how the passwords were encrypted.
 * @global int $t_config['login_method']
 */
$t_config['login_method'] = MD5;

/**
 * limit reporters. Set to ON if you wish to limit reporters to only viewing
 * bugs that they report.
 * @global int $t_config['limit_reporters']
 */
$t_config['limit_reporters'] = OFF;

/**
 * reporter can close. Allow reporters to close the bugs they reported, after
 * they are marked resolved.
 * @global int $t_config['allow_reporter_close']
 */
$t_config['allow_reporter_close']	 = OFF;

/**
 * reporter can reopen. Allow reporters to reopen the bugs they reported, after
 * they are marked resolved.
 * @global int $t_config['allow_reporter_reopen']
 */
$t_config['allow_reporter_reopen'] = ON;

/**
 * reporter can upload
 * Allow reporters to upload attachments to bugs they reported.
 * @global int $t_config['allow_reporter_upload']
 */
$t_config['allow_reporter_upload'] = ON;

/**
 * account delete
 * Allow users to delete their own accounts
 * @global int $t_config['allow_account_delete']
 */
$t_config['allow_account_delete'] = OFF;

/**
 * Enable anonymous access to MantisBT. You must also specify
 * $t_config['anonymous_account as the account which anonymous users will browse
 * MantisBT with. The default setting is OFF.
 * @global int $t_config['allow_anonymous_login']
 */
$t_config['allow_anonymous_login'] = OFF;

/**
 * Define the account which anonymous users will assume when using MantisBT.
 * You only need to define this setting when $t_config['allow_anonymous_login is set to
 * ON. This account will always be treated as a protected account and thus
 * anonymous users will not be able to update the preferences or settings of
 * this account. It is suggested that the access level of this account have
 * read only access to your MantisBT installation (VIEWER). Please read the
 * documentation on this topic before setting up anonymous access to your
 * MantisBT installation.
 * @global string $t_config['anonymous_account']
 */
$t_config['anonymous_account'] = '';

/**
 * Bug Linking
 * if a number follows this tag it will create a link to a bug.
 * eg. for # a link would be #45
 * eg. for bug: a link would be bug:98
 * @global string $t_config['bug_link_tag']
 */
$t_config['bug_link_tag'] = '#';

/**
 * Bugnote Linking
 * if a number follows this tag it will create a link to a bugnote.
 * eg. for ~ a link would be ~45
 * eg. for bugnote: a link would be bugnote:98
 * @global string $t_config['bugnote_link_tag']
 */
$t_config['bugnote_link_tag'] = '~';

/**
 * Bug Count Linking
 * this is the prefix to use when creating links to bug views from bug counts
 * (eg. on the main page and the summary page).
 * Default is a temporary filter
 * only change the filter this time - 'view_all_set.php?type=1&amp;temporary=y'
 * permanently change the filter - 'view_all_set.php?type=1';
 * @global string $t_config['bug_count_hyperlink_prefix']
 */
$t_config['bug_count_hyperlink_prefix'] = 'view_all_set.php?type=1&amp;temporary=y';

/**
 * The regular expression to use when validating new user login names
 * The default regular expression allows a-z, A-Z, 0-9, +, -, dot, space and
 * underscore.  If you change this, you may want to update the
 * ERROR_USER_NAME_INVALID string in the language files to explain
 * the rules you are using on your site
 * See http://en.wikipedia.org/wiki/Regular_Expression for more details about
 * regular expressions. For testing regular expressions, use
 * http://rubular.com/.
 * @global string $t_config['user_login_valid_regex']
 */
$t_config['user_login_valid_regex'] = '/^([a-z\d\-.+_ ]+(@[a-z\d\-.]+\.[a-z]{2,4})?)$/i';

/**
 * Default user name prefix used to filter the list of users in
 * manage_user_page.php.  Change this to 'A' (or any other
 * letter) if you have a lot of users in the system and loading
 * the manage users page takes a long time.
 * @global string $t_config['default_manage_user_prefix']
 */
$t_config['default_manage_user_prefix'] = 'ALL';

/**
 * Default tag prefix used to filter the list of tags in
 * manage_tags_page.php.  Change this to 'A' (or any other
 * letter) if you have a lot of tags in the system and loading
 * the manage tags page takes a long time.
 * @global string $t_config['default_manage_tag_prefix']
 */
$t_config['default_manage_tag_prefix'] = 'ALL';

/**
 * CSV Export
 * Set the csv separator
 * @global string $t_config['csv_separator']
 */
$t_config['csv_separator'] = ',';

/**
 * The threshold required for users to be able to manage configuration of a project.
 * This includes workflow, email notifications, columns to view, and others.
 */
$t_config['manage_configuration_threshold'] = MANAGER;

/**
 * threshold for users to view the system configurations
 * @global int $t_config['view_configuration_threshold']
 */
$t_config['view_configuration_threshold'] = ADMINISTRATOR;

/**
 * threshold for users to set the system configurations generically via
 * MantisBT web interface.
 * WARNING: Users who have access to set configuration via the interface MUST
 * be trusted.  This is due to the fact that such users can set configurations
 * to PHP code and hence there can be a security risk if such users are not
 * trusted.
 * @global int $t_config['set_configuration_threshold']
 */
$t_config['set_configuration_threshold'] = ADMINISTRATOR;

/************************************
 * MantisBT Look and Feel Variables *
 ************************************/

/**
 * status color codes, using the Tango color palette
 * @global array $t_config['status_colors']
 */
$t_config['status_colors'] = array(
	'new'          => '#fcbdbd', // red    (scarlet red #ef2929)
	'feedback'     => '#e3b7eb', // purple (plum        #75507b)
	'acknowledged' => '#ffcd85', // orange (orango      #f57900)
	'confirmed'    => '#fff494', // yellow (butter      #fce94f)
	'assigned'     => '#c2dfff', // blue   (sky blue    #729fcf)
	'resolved'     => '#d2f5b0', // green  (chameleon   #8ae234)
	'closed'       => '#c9ccc4'  // grey   (aluminum    #babdb6)
);

/**
 * The padding level when displaying project ids
 *  The bug id will be padded with 0's up to the size given
 * @global int $t_config['display_project_padding']
 */
$t_config['display_project_padding'] = 3;

/**
 * The padding level when displaying bug ids
 *  The bug id will be padded with 0's up to the size given
 * @global int $t_config['display_bug_padding']
 */
$t_config['display_bug_padding'] = 7;

/**
 * The padding level when displaying bugnote ids
 *  The bugnote id will be padded with 0's up to the size given
 * @global int $t_config['display_bugnote_padding']
 */
$t_config['display_bugnote_padding'] = 7;

/**
 * colours for configuration display
 * @global string $t_config['colour_project']
 */
$t_config['colour_project'] = 'LightGreen';

/**
 * colours for configuration display
 * @global string $t_config['colour_global']
 */
$t_config['colour_global'] = 'LightBlue';

/*****************************
 * MantisBT Cookie Variables *
 *****************************/

/**
 * --- cookie path ---------------
 * set this to something more restrictive if needed
 * http://www.php.net/manual/en/function.setcookie.php
 * @global string $t_config['cookie_path']
 */
$t_config['cookie_path'] = '/';

/**
 *
 * @global string $t_config['cookie_domain']
 */
$t_config['cookie_domain'] = '';

/**
 * cookie version for view_all_page
 * @global string $t_config['cookie_version']
 */
$t_config['cookie_version'] = 'v8';

/**
 * --- cookie prefix ---------------
 * set this to a unique identifier.  No spaces or periods.
 * @global string $t_config['cookie_prefix']
 */
$t_config['cookie_prefix'] = 'MANTIS';

/**
 *
 * @global string $t_config['string_cookie']
 */
$t_config['string_cookie'] = '%cookie_prefix%_STRING_COOKIE';

/**
 *
 * @global string $t_config['project_cookie']
 */
$t_config['project_cookie'] = '%cookie_prefix%_PROJECT_COOKIE';

/**
 *
 * @global string $t_config['view_all_cookie']
 */
$t_config['view_all_cookie'] = '%cookie_prefix%_VIEW_ALL_COOKIE';

/**
 *
 * @global string $t_config['manage_cookie']
 */
$t_config['manage_cookie'] = '%cookie_prefix%_MANAGE_COOKIE';

/**
 *
 * @global string $t_config['logout_cookie']
 */
$t_config['logout_cookie'] = '%cookie_prefix%_LOGOUT_COOKIE';

/**
 *
 * @global string $t_config['bug_list_cookie']
 */
$t_config['bug_list_cookie'] = '%cookie_prefix%_BUG_LIST_COOKIE';

/*****************************
 * MantisBT Filter Variables *
 *****************************/

/**
 *
 * @global int $t_config['filter_by_custom_fields']
 */
$t_config['filter_by_custom_fields'] = ON;

/**
 *
 * @global int $t_config['filter_custom_fields_per_row']
 */
$t_config['filter_custom_fields_per_row'] = 8;

/**
 *
 * @global int $t_config['view_filters']
 */
$t_config['view_filters'] = SIMPLE_DEFAULT;

/**
 * This switch enables the use of AJAX to dynamically load and create filter
 * form controls upon request. This method will reduce the amount of data that
 * needs to be transferred upon each page load dealing with filters and thus
 * will result in speed improvements and bandwidth reduction.
 * @global int $t_config['use_dynamic_filters']
 */
$t_config['use_dynamic_filters'] = ON;

/**
 * The threshold required for users to be able to create permalinks.  To turn
 * off this feature use NOBODY.
 * @global int $t_config['create_permalink_threshold']
 */
$t_config['create_permalink_threshold'] = DEVELOPER;

/**
 * The service to use to create a short URL.  The %s will be replaced by the
 * long URL. To disable the feature set to ''.
 * @global string $t_config['create_short_url']
 */
$t_config['create_short_url'] = 'http://tinyurl.com/create.php?url=%s';

/*************************************
 * MantisBT Database Table Variables *
 *************************************/

/**
 * table prefix
 * @global string $t_config['db_table_prefix']
 */
$t_config['db_table_prefix'] = 'mantis';

/**
 * table suffix
 * @global string $t_config['db_table_suffix']
 */
$t_config['db_table_suffix'] = '_table';

/*************************
 * MantisBT Enum Strings *
 *************************/

/**
 * status from ( $t_config[status_index] -1 to 79 ) are used for the onboard customization
 * (if enabled) directly use MantisBT to edit them.
 * @global string $t_config['access_levels_enum_string']
 */
$t_config['access_levels_enum_string'] = '10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator';

/**
 *
 * @global string $t_config['project_status_enum_string']
 */
$t_config['project_status_enum_string'] = '10:development,30:release,50:stable,70:obsolete';

/**
 *
 * @global string $t_config['project_view_state_enum_string']
 */
$t_config['project_view_state_enum_string'] = '10:public,50:private';

/**
 *
 * @global string $t_config['view_state_enum_string']
 */
$t_config['view_state_enum_string'] = '10:public,50:private';

/**
 *
 * @global string $t_config['priority_enum_string']
 */
$t_config['priority_enum_string'] = '10:none,20:low,30:normal,40:high,50:urgent,60:immediate';
/**
 *
 * @global string $t_config['severity_enum_string']
 */
$t_config['severity_enum_string'] = '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block';

/**
 *
 * @global string $t_config['reproducibility_enum_string']
 */
$t_config['reproducibility_enum_string'] = '10:always,30:sometimes,50:random,70:have not tried,90:unable to duplicate,100:N/A';

/**
 *
 * @global string $t_config['status_enum_string']
 */
$t_config['status_enum_string'] = '10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,80:resolved,90:closed';

/**
 * @@@ for documentation, the values in this list are also used to define
 * variables in the language files (e.g., $s_new_bug_title referenced in
 * bug_change_status_page.php ). Embedded spaces are converted to underscores
 * (e.g., "working on" references $s_working_on_bug_title). They are also
 * expected to be English names for the states
 * @global string $t_config['resolution_enum_string']
 */
$t_config['resolution_enum_string'] = '10:open,20:fixed,30:reopened,40:unable to duplicate,50:not fixable,60:duplicate,70:not a bug,80:suspended,90:wont fix';

/**
 *
 * @global string $t_config['projection_enum_string']
 */
$t_config['projection_enum_string'] = '10:none,30:tweak,50:minor fix,70:major rework,90:redesign';

/**
 *
 * @global string $t_config['eta_enum_string']
 */
$t_config['eta_enum_string'] = '10:none,20:< 1 day,30:2-3 days,40:< 1 week,50:< 1 month,60:> 1 month';

/**
 *
 * @global string $t_config['sponsorship_enum_string']
 */
$t_config['sponsorship_enum_string'] = '0:Unpaid,1:Requested,2:Paid';

/**
 *
 * @global string $t_config['custom_field_type_enum_string']
 */
$t_config['custom_field_type_enum_string'] = '0:string,1:numeric,2:float,3:enum,4:email,5:checkbox,6:list,7:multiselection list,8:date,9:radio,10:textarea';

/*********************************
 * MantisBT Javascript Variables *
 *********************************/

/**
 * allow the use of Javascript?
 * @global int $t_config['use_javascript']
 */
$t_config['use_javascript'] = ON;

/*******************************
 * MantisBT Speed Optimisation *
 *******************************/

/**
 * Use compression of generated html if browser supports it. If you already
 * have compression enabled in your php.ini file (either with
 * zlib.output_compression or output_handler=ob_gzhandler) this option will be
 * ignored.
 *
 * If you do not have zlib enabled in your PHP installation this option will
 * also be ignored.  PHP 4.3.0 and later have zlib included by default. Windows
 * users should uncomment the appropriate line in their php.ini files to load
 * the zlib DLL. You can check what extensions are loaded by running "php -m"
 * at the command line (look for 'zlib')
 * @global int $t_config['compress_html']
 */
$t_config['compress_html'] = ON;

/*****************
 * Include files *
 *****************/

/**
 * Specify your top/bottom include file (logos, banners, etc)
 * @global string $t_config['bottom_include_page']
 */
$t_config['bottom_include_page'] = '%absolute_path%';

/**
 * Specify your top/bottom include file (logos, banners, etc). If a top file is
 * supplied, the default MantisBT logo at the top will be hidden.
 * @global string $t_config['top_include_page']
 */
$t_config['top_include_page'] = '%absolute_path%';

/**
 * CSS file
 * @global string $t_config['css_include_file']
 */
$t_config['css_include_file'] = 'default.css';

/**
 * RTL CSS file
 * @global string $t_config['css_rtl_include_file']
 */
$t_config['css_rtl_include_file'] = 'rtl.css';


/**
 * meta tags
 * @global string $t_config['meta_include_file']
 */
$t_config['meta_include_file'] = '%absolute_path%meta_inc.php';

/****************
 * Redirections *
 ****************/

/**
 * Default page after Login or Set Project
 * @global string $t_config['default_home_page']
 */
$t_config['default_home_page'] = 'my_view_page.php';

/**
 * Specify where the user should be sent after logging out.
 * @global string $t_config['logout_redirect_page']
 */
$t_config['logout_redirect_page'] = 'login_page.php';

/***********
 * Headers *
 ***********/

/**
 * An array of headers to be sent with each page.
 * For example, to allow your MantisBT installation to be viewed in a frame in
 * IE6 when the frameset is not at the same hostname as the MantisBT install,
 * you need to add a P3P header. You could try something like
 * 'P3P: CP="CUR ADM"' in your config file, but make sure to check that the
 * your policy actually matches with what you are promising. See
 * http://msdn.microsoft.com/en-us/library/ms537343.aspx for more information.
 * @global array $t_config['custom_headers']
 */
$t_config['custom_headers'] = array();

/**
 * Browser Caching Control
 * By default, we try to prevent the browser from caching anything. These two
 * settings will defeat this for some cases.
 *
 * Browser Page caching - This will allow the browser to cache all pages. The
 * upside will be better performance, but there may be cases where obsolete
 * information is displayed. Note that this will be bypassed (and caching is
 * allowed) for the bug report pages.
 *
 * @global int $t_config['allow_browser_cache']
 */
// $t_config['allow_browser_cache'] = ON;
/**
 * File caching - This will allow the browser to cache downloaded files.
 * Without this set, there may be issues with IE receiving files, and launching
 * support programs.
 * @global int $t_config['allow_file_cache']
 */
 // $t_config['allow_file_cache'] = ON;

/*****************
 * Custom Fields *
 *****************/

/**
 * Threshold needed to manage custom fields
 * @global int $t_config['manage_custom_fields_threshold']
 */
$t_config['manage_custom_fields_threshold'] = ADMINISTRATOR;

/**
 * Threshold needed to link/unlink custom field to/from a project
 * @global int $t_config['custom_field_link_threshold']
 */
$t_config['custom_field_link_threshold'] = MANAGER;

/**
 * Whether to start editng a custom field immediately after creating it
 * @global int $t_config['custom_field_edit_after_create']
 */
$t_config['custom_field_edit_after_create'] = ON;

/****************
 * Custom Menus *
 ****************/

/**
 * Add custom options to the main menu.  For example:
 * $t_config['main_menu_custom_options'] = array(
 *     array( "My Link",  MANAGER,       'my_link.php' ),
 *     array( "My Link2", ADMINISTRATOR, 'my_link2.php' )
 * );
 *
 * Note that if the caption is found in custom_strings_inc.php, then it will be
 * replaced by the translated string.  Options will only be added to the menu
 * if the current logged in user has the appropriate access level.
 * @global array $t_config['main_menu_custom_options']
 */
$t_config['main_menu_custom_options'] = array();

/*********
 * Icons *
 *********/

/**
 * Maps a file extension to a file type icon.  These icons are printed
 * next to project documents and bug attachments.
 * Note:
 * - Extensions must be in lower case
 * - All icons will be displayed as 16x16 pixels.
 * @global array $t_config['file_type_icons']
 */
$t_config['file_type_icons'] = array(
	''	=> 'text.gif',
	'7z'	=> 'zip.gif',
	'ace'	=> 'zip.gif',
	'arj'	=> 'zip.gif',
	'bz2'	=> 'zip.gif',
	'c'	=> 'cpp.gif',
	'chm'	=> 'chm.gif',
	'cpp'	=> 'cpp.gif',
	'css'	=> 'css.gif',
	'csv'	=> 'csv.gif',
	'cxx'	=> 'cpp.gif',
	'diff'	=> 'text.gif',
	'doc'	=> 'doc.gif',
	'docx'	=> 'doc.gif',
	'dot'	=> 'doc.gif',
	'eml'	=> 'eml.gif',
	'htm'	=> 'html.gif',
	'html'	=> 'html.gif',
	'gif'	=> 'gif.gif',
	'gz'	=> 'zip.gif',
	'jpe'	=> 'jpg.gif',
	'jpg'	=> 'jpg.gif',
	'jpeg'	=> 'jpg.gif',
	'log'	=> 'text.gif',
	'lzh'	=> 'zip.gif',
	'mhtml'	=> 'html.gif',
	'mid'	=> 'mid.gif',
	'midi'	=> 'mid.gif',
	'mov'	=> 'mov.gif',
	'msg'	=> 'eml.gif',
	'one'	=> 'one.gif',
	'patch'	=> 'text.gif',
	'pcx'	=> 'pcx.gif',
	'pdf'	=> 'pdf.gif',
	'png'	=> 'png.gif',
	'pot'	=> 'pot.gif',
	'pps'	=> 'pps.gif',
	'ppt'	=> 'ppt.gif',
	'pptx'	=> 'ppt.gif',
	'pub'	=> 'pub.gif',
	'rar'	=> 'zip.gif',
	'reg'	=> 'reg.gif',
	'rtf'	=> 'doc.gif',
	'tar'	=> 'zip.gif',
	'tgz'	=> 'zip.gif',
	'txt'	=> 'text.gif',
	'uc2'	=> 'zip.gif',
	'vsd'	=> 'vsd.gif',
	'vsl'	=> 'vsl.gif',
	'vss'	=> 'vsd.gif',
	'vst'	=> 'vst.gif',
	'vsu'	=> 'vsd.gif',
	'vsw'	=> 'vsd.gif',
	'vsx'	=> 'vsd.gif',
	'vtx'	=> 'vst.gif',
	'wav'	=> 'wav.gif',
	'wbk'	=> 'wbk.gif',
	'wma'	=> 'wav.gif',
	'wmv'	=> 'mov.gif',
	'wri'	=> 'wri.gif',
	'xlk'	=> 'xls.gif',
	'xls'	=> 'xls.gif',
	'xlsx'	=> 'xls.gif',
	'xlt'	=> 'xlt.gif',
	'xml'	=> 'xml.gif',
	'zip'	=> 'zip.gif',
	'?'	=> 'generic.gif' );

/**
 * Icon associative arrays
 * Status to icon mapping
 * @global array $t_config['status_icon_arr']
 */
$t_config['status_icon_arr'] = array (
	NONE      => '',
	LOW       => 'priority_low_1.gif',
	NORMAL    => 'priority_normal.gif',
	HIGH      => 'priority_1.gif',
	URGENT    => 'priority_2.gif',
	IMMEDIATE => 'priority_3.gif'
);

/**
 * Sort direction to icon mapping
 * @global array $t_config['sort_icon_arr']
 */
$t_config['sort_icon_arr'] = array (
	ASCENDING  => 'up.gif',
	DESCENDING => 'down.gif'
);

/**
 * Read status to icon mapping
 * @global array $t_config['unread_icon_arr']
 */
$t_config['unread_icon_arr'] = array (
	READ   => 'mantis_space.gif',
	UNREAD => 'unread.gif'
);

/********************
 * My View Settings *
 ********************/

/**
 * Number of bugs shown in each box
 * @global int $t_config['my_view_bug_count']
 */
$t_config['my_view_bug_count'] = 10;

/**
 * Boxes to be shown and their order
 * A box that is not to be shown can have its value set to 0
 * @global array $t_config['my_view_boxes']
 */
$t_config['my_view_boxes'] = array (
	'assigned'      => '1',
	'unassigned'    => '2',
	'reported'      => '3',
	'resolved'      => '4',
	'recent_mod'    => '5',
	'monitored'     => '6',
	'feedback'      => '0',
	'verify'        => '0',
	'my_comments'   => '0'
);

/**
 * Toggle whether 'My View' boxes are shown in a fixed position (i.e. adjacent
 * boxes start at the same vertical position)
 * @global int $t_config['my_view_boxes_fixed_position']
 */
$t_config['my_view_boxes_fixed_position'] = ON;


/*************
 * RSS Feeds *
 *************/

/**
 * This flag enables or disables RSS syndication.  In the case where RSS
 * syndication is not used, it is recommended to set it to OFF.
 * @global int $t_config['rss_enabled']
 */
$t_config['rss_enabled'] = ON;


/*********************
 * Bug Relationships *
 *********************/

/**
 * Enable relationship graphs support.
 * Show issue relationships using graphs.
 *
 * In order to use this feature, you must first install GraphViz.
 *
 * Graphviz homepage:    http://www.research.att.com/sw/tools/graphviz/
 *
 * Refer to the notes near the top of core/graphviz_api.php and
 * core/relationship_graph_api.php for more information.
 * @global int $t_config['relationship_graph_enable']
 */
$t_config['relationship_graph_enable'] = OFF;

/**
 * Complete path to dot and neato tools. Your webserver must have execute
 * permission to these programs in order to generate relationship graphs.
 * NOTE: On windows, the IIS user may require permissions to cmd.exe to be able to use PHP's proc_open
 * @global string $t_config['dot_tool']
 */
$t_config['dot_tool'] = '/usr/bin/dot';
/**
 * Complete path to dot and neato tools. Your webserver must have execute
 * permission to these programs in order to generate relationship graphs.
 * NOTE: On windows, the IIS user may require permissions to cmd.exe to be able to use PHP's proc_open
 * @global string $t_config['neato_tool']
 */
$t_config['neato_tool'] = '/usr/bin/neato';

/**
 * Font name and size, as required by Graphviz. If Graphviz fails to run
 * for you, you are probably using a font name that gd can't find. On
 * Linux, try the name of the font file without the extension.
 * @global string $t_config['relationship_graph_fontname']
 */
$t_config['relationship_graph_fontname'] = 'Arial';

/**
 *
 * @global int $t_config['relationship_graph_fontsize']
 */
$t_config['relationship_graph_fontsize'] = 8;

/**
 * Default dependency orientation. If you have issues with lots of childs
 * or parents, leave as 'horizontal', otherwise, if you have lots of
 * "chained" issue dependencies, change to 'vertical'.
 * @global string $t_config['relationship_graph_orientation']
 */
$t_config['relationship_graph_orientation'] = 'horizontal';

/**
 * Max depth for relation graphs. This only affects relation graphs,
 * dependency graphs are drawn to the full depth. A value of 3 is already
 * enough to show issues really unrelated to the one you are currently
 * viewing.
 * @global int $t_config['relationship_graph_max_depth']
 */
$t_config['relationship_graph_max_depth'] = 2;

/**
 * If set to ON, clicking on an issue on the relationship graph will open
 * the bug view page for that issue, otherwise, will navigate to the
 * relationship graph for that issue.
 *
 * @global int $t_config['relationship_graph_view_on_click']
 */
$t_config['relationship_graph_view_on_click'] = OFF;

/**
 * Number of years in the past that custom date fields will display in
 * drop down boxes.
 * @global int $t_config['backward_year_count']
 */
$t_config['backward_year_count'] = 4;

/**
 * Number of years in the future that custom date fields will display in
 * drop down boxes.
 * @global int $t_config['forward_year_count']
 */
$t_config['forward_year_count'] = 4;

/**
 * Custom Group Actions
 *
 * This extensibility model allows developing new group custom actions.  This
 * can be implemented with a totally custom form and action pages or with a
 * pre-implemented form and action page and call-outs to some functions.  These
 * functions are to be implemented in a predefined file whose name is based on
 * the action name. For example, for an action to add a note, the action would
 * be EXT_ADD_NOTE and the file implementing it would be
 * bug_actiongroup_add_note_inc.php. See implementation of this file for
 * details.
 *
 * Sample:
 *
 * array(
 *	array(
 *		'action' => 'my_custom_action',
 *		'label' => 'my_label',   // string to be passed to lang_get_defaulted()
 *		'form_page' => 'my_custom_action_page.php',
 *		'action_page' => 'my_custom_action.php'
 *	)
 *	array(
 *		'action' => 'my_custom_action2',
 *		'form_page' => 'my_custom_action2_page.php',
 *		'action_page' => 'my_custom_action2.php'
 *	)
 *	array(
 *		'action' => 'EXT_ADD_NOTE',  // you need to implement bug_actiongroup_<action_without_'EXT_')_inc.php
 *		'label' => 'actiongroup_menu_add_note' // see strings_english.txt for this label
 *	)
 * );
 *
 * @global array $t_config['custom_group_actions']
 */
$t_config['custom_group_actions'] = array();

/********************
 * Wiki Integration *
 ********************/

/**
 * Wiki Integration Enabled?
 * @global int $t_config['wiki_enable']
 */
$t_config['wiki_enable'] = OFF;

/**
 * Wiki Engine.
 * Supported engines: 'dokuwiki', 'mediawiki', 'twiki', 'wikka', 'xwiki'
 * @global string $t_config['wiki_engine']
 */
$t_config['wiki_engine'] = '';

/**
 * Wiki namespace to be used as root for all pages relating to this MantisBT
 * installation.
 * @global string $t_config['wiki_root_namespace']
 */
$t_config['wiki_root_namespace'] = 'mantis';

/**
 * URL under which the wiki engine is hosted.  Must be on the same server.
 * @global string $t_config['wiki_engine_url']
 */
$t_config['wiki_engine_url'] = $t_protocol . '://' . $t_host . '/%wiki_engine%/';

/********************
 * Recently Visited *
 ********************/

/**
 * Whether to show the most recently visited issues or not.  At the moment we always track them even if this flag is off.
 * @global int $t_config['recently_visited']
 */
$t_config['recently_visited'] = ON;

/**
 * The maximum number of issues to keep in the recently visited list.
 * @global int $t_config['recently_visited_count']
 */
$t_config['recently_visited_count'] = 5;

/***************
 * Bug Tagging *
 ***************/

/**
 * String that will separate tags as entered for input
 * @global int $t_config['tag_separator']
 */
$t_config['tag_separator'] = ',';

/**
 * Access level required to view tags attached to a bug
 * @global int $t_config['tag_view_threshold']
 */
$t_config['tag_view_threshold'] = VIEWER;

/**
 * Access level required to attach tags to a bug
 * @global int $t_config['tag_attach_threshold']
 */
$t_config['tag_attach_threshold'] = REPORTER;

/**
 * Access level required to detach tags from a bug
 * @global int $t_config['tag_detach_threshold']
 */
$t_config['tag_detach_threshold'] = DEVELOPER;

/**
 * Access level required to detach tags attached by the same user
 * @global int $t_config['tag_detach_own_threshold']
 */
$t_config['tag_detach_own_threshold'] = REPORTER;

/**
 * Access level required to create new tags
 * @global int $t_config['tag_create_threshold']
 */
$t_config['tag_create_threshold'] = REPORTER;

/**
 * Access level required to edit tag names and descriptions
 * @global int $t_config['tag_edit_threshold']
 */
$t_config['tag_edit_threshold'] = DEVELOPER;

/**
 * Access level required to edit descriptions by the creating user
 * @global int $t_config['tag_edit_own_threshold']
 */
$t_config['tag_edit_own_threshold'] = REPORTER;

/*****************
 * Time tracking *
 *****************/

/**
 * Turn on Time Tracking accounting
 * @global int $t_config['time_tracking_enabled']
 */
$t_config['time_tracking_enabled'] = OFF;

/**
 * A billing sums
 * @global int $t_config['time_tracking_with_billing']
 */
$t_config['time_tracking_with_billing'] = OFF;

/**
 * Stop watch to build time tracking field
 * @global int $t_config['time_tracking_stopwatch']
 */
$t_config['time_tracking_stopwatch'] = OFF;

/**
 * access level required to view time tracking information
 * @global int $t_config['time_tracking_view_threshold']
 */
$t_config['time_tracking_view_threshold'] = DEVELOPER;

/**
 * access level required to add/edit time tracking information
 * @global int $t_config['time_tracking_edit_threshold']
 */
$t_config['time_tracking_edit_threshold'] = DEVELOPER;

/**
 * access level required to run reports
 * @global int $t_config['time_tracking_reporting_threshold']
 */
$t_config['time_tracking_reporting_threshold'] = MANAGER;

/**
 * allow time tracking to be recorded without a bugnote
 * @global int $t_config['time_tracking_without_note']
 */
$t_config['time_tracking_without_note'] = ON;

/****************************
 * Profile Related Settings *
 ****************************/

/**
 * Enable Profiles
 * @global int $t_config['enable_profiles']
 */
$t_config['enable_profiles'] = ON;

/**
 * Add profile threshold
 * @global int $t_config['add_profile_threshold']
 */
$t_config['add_profile_threshold'] = REPORTER;

/**
 * Threshold needed to be able to create and modify global profiles
 * @global int $t_config['manage_global_profile_threshold
 */
$t_config['manage_global_profile_threshold'] = MANAGER;

/**
 * Allows the users to enter free text when reporting/updating issues
 * for the profile related fields (i.e. platform, os, os build)
 * @global int $t_config['allow_freetext_in_profile_fields']
 */
$t_config['allow_freetext_in_profile_fields'] = ON;

/********************
 * Twitter Settings *
 ********************/

/**
 * The integration with twitter allows for a MantisBT installation to post
 * updates to a twitter account.  This feature will be disabled if username
 * is empty or if the curl extension is not enabled.
 *
 * The twitter account user name.
 * @global string $t_config['twitter_username']
 */
$t_config['twitter_username'] = '';

/**
 * The twitter account password.
 * @global string $t_config['twitter_password']
 */
$t_config['twitter_password'] = '';

/*****************
 * Plugin System *
 *****************/

/**
 * enable/disable plugins
 * @global int $t_config['plugins_enabled']
 */
$t_config['plugins_enabled'] = ON;

/**
 * absolute path to plugin files.
 * @global string $t_config['plugin_path']
 */
$t_config['plugin_path'] = $t_config['absolute_path'] . 'plugins' . DIRECTORY_SEPARATOR;

/**
 * management threshold.
 * @global int $t_config['manage_plugin_threshold
 */
$t_config['manage_plugin_threshold'] = ADMINISTRATOR;

/**
 * Force installation and protection of certain plugins.
 * Note that this is not the preferred method of installing plugins,
 * which should generally be done directly through the plugin management
 * interface.  However, this method will prevent users with admin access
 * from uninstalling plugins through the plugin management interface.
 *
 * Entries in the array must be in the form of a key/value pair
 * consisting of the plugin basename and priority, as such:
 *
 * = array(
 *     'PluginA' => 5,
 *     'PluginB' => 5,
 *     ...
 *
 * @global $t_config['plugins_force_installed']
 */
$t_config['plugins_force_installed'] = array();

/************
 * Due Date *
 ************/

/**
 * threshold to update due date submitted
 * @global int $t_config['due_date_update_threshold']
 */
$t_config['due_date_update_threshold'] = NOBODY;

/**
 * threshold to see due date
 * @global int $t_config['due_date_view_threshold']
 */
$t_config['due_date_view_threshold'] = NOBODY;

/*****************
 * Sub-projects
 *****************

/**
 * Sub-projects should inherit categories from parent projects.
 */
$t_config['subprojects_inherit_categories'] = ON;

/**
 * Sub-projects should inherit versions from parent projects.
 */
$t_config['subprojects_inherit_versions'] = ON;

/**********************************
 * Debugging / Developer Settings *
 **********************************/

/**
 * Time page loads. The page execution timer shows at the bottom of each page.
 * @global int $t_config['show_timer']
 */
$t_config['show_timer'] = OFF;

/**
 * Show memory usage for each page load in the footer.
 * @global int $t_config['show_memory_usage']
 */
$t_config['show_memory_usage'] = OFF;

/**
 * Used for debugging e-mail feature, when set to OFF the emails work as normal.
 * when set to e-mail address, all e-mails are sent to this address with the
 * original To, Cc, Bcc included in the message body.
 * @global int $t_config['debug_email']
 */
$t_config['debug_email'] = OFF;

/**
 * Shows the total number/unique number of queries executed to serve the page.
 * @global int $t_config['show_queries_count']
 */
$t_config['show_queries_count'] = OFF;

/**
 * --- detailed error messages -----
 * Shows a list of variables and their values when an error is triggered
 * Only applies to error types configured to 'halt' in $t_config['display_errors, below
 * WARNING: Potential security hazard.  Only turn this on when you really
 * need it for debugging
 * @global int $t_config['show_detailed_errors']
 */
$t_config['show_detailed_errors'] = OFF;

/**
 * --- error display ---
 * what errors are displayed and how?
 * The options for display are:
 *  'halt' - stop and display traceback
 *  'inline' - display 1 line error and continue
 *  'none' - no error displayed
 * A developer might set this in config_inc.php as:
 *	$t_config['display_errors'] = array(
 *		E_WARNING => 'halt',
 *		E_NOTICE => 'halt',
 *		E_USER_ERROR => 'halt',
 *		E_USER_WARNING => 'none',
 *		E_USER_NOTICE => 'none'
 *	);
 * @global array $t_config['display_errors']
 */
$t_config['display_errors'] = array(
	E_WARNING => 'inline',
	E_NOTICE => 'none',
	E_USER_ERROR => 'halt',
	E_USER_WARNING => 'inline',
	E_USER_NOTICE => 'none'
);

/**
 * --- debug messages ---
 * If this option is turned OFF (default) page redirects will continue to
 *  function even if a non-fatal error occurs.  For debugging purposes, you
 *  can set this to ON so that any non-fatal error will prevent page redirection,
 *  allowing you to see the errors.
 * Only turn this option on for debugging
 * @global int $t_config['stop_on_errors']
 */
$t_config['stop_on_errors'] = OFF;

/**
 * --- system logging ---
 * This controls the logging of information to a separate file for debug or audit
 * $t_config['log_level'] controls what information is logged
 *  see constant_inc.php for details on the log channels available
 *  e.g., $t_config['log_level'] = LOG_EMAIL | LOG_EMAIL_RECIPIENT | LOG_FILTERING | LOG_AJAX;
 *
 * $t_config['log_destination'] specifies the file where the data goes
 *   right now, only "file:<file path>" is supported
 *   e.g. (Linux), $t_config['log_destination'] = 'file:/tmp/mantisbt.log';
 *   e.g. (Windows), $t_config['log_destination'] = 'file:c:/temp/mantisbt.log';
 *   see http://www.php.net/error_log for details
 * @global int $t_config['log_level']
 */
$t_config['log_level'] = LOG_NONE;

/**
 * 4 Options currently exist for log destination:
 * a) '': The default value (empty string) means default PHP error log settings
 * b) 'file': Log to a specific file - specified as 'file:/var/log/mantis.log'
 * c) 'firebug': make use of firefox's firebug addon from http://getfirebug.com/ - Note: if user is 
 *    not running firefox, this options falls through to the default php error log settings.
 * d) 'page': Display log output at bottom of the page.
 * @global string $t_config['log_destination']
 */
$t_config['log_destination'] = '';

/**
 * Indicates the access level required for a user to see the log output (if log_destination is page)
 * Note that this threshold is compared against the user's default global access level rather than 
 * the threshold based on the current active project.
 *
 * @global int $t_config['show_log_threshold']
 */
$t_config['show_log_threshold'] = ADMINISTRATOR;

/**************************
 * Configuration Settings *
 **************************/

/**
 * The following list of variables should never be in the database.
 * These patterns will be concatenated and used as a regular expression
 * to bypass the database lookup and look here for appropriate global settings.
 * @global array $t_config['global_settings']
 */
$t_config['global_settings'] = array(
	'global_settings',
	'admin_checks',
	'allow_signup',
	'anonymous',
	'compress_html',
	'content_expire',
	'cookie',
	'crypto_master_salt',
	'custom_headers',
	'database_name',
	'^db_',
	'display_errors',
	'form_security_',
	'hostname',
	'html_valid_tags',
	'language',
	'login_method',
	'plugins_enabled',
	'plugins_installed',
	'session_',
	'show_detailed_errors',
	'show_queries_',
	'stop_on_errors',
	'use_javascript',
	'version_suffix',
	'[^_]file[(_(?!threshold))$]',
	'[^_]path[_$]',
	'_page$',
	'_table$',
	'_url$',
);
