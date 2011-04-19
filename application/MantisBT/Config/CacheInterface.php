<?php
namespace MantisBT\Config;
# MantisBT - A PHP based bugtracking system

# @todo add new license text

/**
 * Config\Cache API
 *
 * @package CoreAPI
 * @subpackage classes 
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

interface CacheInterface {
    public function search( $p_config_id, $p_user_id, $p_project_id);
    public function add( Model $p_config ); 
    public function clear( $p_config_id=null, $p_user_id=null, $p_project_id=null );
}
