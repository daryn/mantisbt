<?php
namespace MantisBT;

# MantisBT - A PHP based bugtracking system

# @todo add new license text

/**
 * Cache API
 *
 * @package CoreAPI
 * @subpackage classes 
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

interface CacheInterface {
    public function search( $p_field, $p_value );
    public function add( $p_id );
    public function clear( $p_id = null );
}
