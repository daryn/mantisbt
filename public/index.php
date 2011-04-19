<?php
# MantisBT - A PHP based bugtracking system

# @todo needs new license text

/**
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Set the initial include_path. for performance
 * reasons, it's best to move this to your web server configuration or php.ini
 * for production.
 */ set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library'),
    get_include_path(),
)));

// Define absolute path to web directory.
defined('ABSOLUTE_PATH')
    || define('ABSOLUTE_PATH', realpath( dirname(__FILE__) ) );

// Define path to application directory
defined('LIBRARY_PATH')
    || define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library') );

# Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application') );

// Define path to configs directory
defined('CONFIG_PATH')
    || define('CONFIG_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' );

// Define path to languages directory
defined('LANGUAGES_PATH')
    || define('LANGUAGES_PATH', realpath(dirname(__FILE__) . '/../languages') );

# Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

require_once( APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Autoloader.php' );
spl_autoload_register( array( 'Autoloader', 'autoload' ) );

// Create application, bootstrap, and run
MantisBT::main();
