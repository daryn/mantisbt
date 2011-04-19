<?php
# Database Variables
$t_config['hostname'] = 'localhost';
$t_config['db_type'] = 'mysqli';
$t_config['database_name'] = 'mbt_bugtracker';
$t_config['db_username'] = 'mantis';
$t_config['db_password'] = 'super';

$t_config['manage_user_threshold'] = MANAGER;
$t_config['send_reset_password']  = OFF;

# Email Variables
$t_config['administrator_email']  = 'mantis@iivip.com';
$t_config['webmaster_email']      = 'mantis@iivip.com';
$t_config['from_email']           = 'mantis@iivip.com';
$t_config['return_path_email']    = 'mantis@iivip.com';
$t_config['phpMailer_method']		= 1;

# File Upload Settings 
$t_config['allow_file_upload']	= ON;
$t_config['show_footer_menu']    = OFF;

# allow users to signup for their own accounts.
# Mail settings must be correctly configured in order for this to work
$t_config['allow_signup']         = OFF;

# Wiki Integration
$t_config['wiki_enable'] = ON;
$t_config['wiki_engine'] = 'mediawiki';
$t_config['wiki_root_namespace'] = 'mantis';
$t_config['wiki_engine_url'] = $t_protocol . '://wiki/';

$t_config['allow_browser_cache'] = ON;
$t_config['news_enabled'] = OFF;
$t_config['manage_news_threshold'] = DEVELOPER;
$t_config['default_advanced_report'] = ON;
$t_config['month_day_year_date_format'] = 'm/d/Y';
$t_config['short_date_format']    = 'm/d/Y';
$t_config['normal_date_format']   = 'm/d/Y H:i';
$t_config['complete_date_format'] = 'm/d/Y H:i T';
$t_config['default_bug_view_status'] = VS_PRIVATE;
$t_config['default_bugnote_order']        = 'DESC';

$t_config['path']	= 'http://ii288lt-lucid/';
$t_config['short_path'] = '/'; # overwrite short path because mantis bug is adding an extra slash on cvs1 server only
$t_config['show_assigned_names'] = OFF;
$t_config['show_queries_count'] = ON;
$t_config['show_queries_list'] = ON;

$t_config['allow_no_category'] = ON;

$t_config['debug_email'] = 'daryn@iivip.com';
$t_config['show_realname'] = ON;
$t_config['show_user_realname_threshold'] = DEVELOPER;
$t_config['crypto_master_salt'] = 'c30iKZwvD/h61MFvo2l3DtGvMkQ14b8+H9hTx9yhta9iZFySWzqs3A2p6qVYC/hVw775NLUvSnI7XvH+EaNY0g==';

$t_config['show_product_version'] = ON;

$t_config['due_date_update_threshold'] = DEVELOPER;
$t_config['due_date_view_threshold'] = DEVELOPER;
$t_config['bug_readonly_status_threshold'] = CLOSED;
$t_config['update_readonly_bug_threshold'] = DEVELOPER;
$t_config['bug_link_tag'] = 'issue:';
$t_config['default_timezone'] = 'America/Chicago';
$t_config['max_file_size'] = 2097152;
