<?php
namespace MantisBT\Config;

# MantisBT - A PHP based bugtracking system

# @todo needs new license text

/**
 * MantisBT\Config\Model
 *
 * @package MantisBT
 * @subpackage classes
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class Model {
    protected $config_id;
    protected $project_id;
    protected $user_id;
    protected $access_reqd;
    protected $type;
    protected $value;

    protected $private;
    protected $global;

    public function __construct( $p_row ) {
        foreach( $p_row AS $p_key=>$p_value ) {
            if( property_exists( $this, $p_key ) ) {
                $this->$p_key = $p_value;
            }
        }
    }

	public function __get( $p_field_name ) {
        return $this->$p_field_name;
	}

	/**
	 * This may expose webserver details and shouldn't be
	 * exposed to users or webservices
	 *	@return bool
	 */
	public function isPrivate() {
        return $this->private;
    }

	public function isGlobal() {
        return $this->global;
    }
}
