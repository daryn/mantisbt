<?php
namespace MantisBT\User;
use MantisBT;

# MantisBT - a php based bugtracking system

# @todo add new license text

/**
 *	User\Cache class for caching user models.
 * @package MantisBT
 * @subpackage classes
 */
class Cache implements MantisBT\CacheInterface {
    private $full = false;
    private $cache = null;

    public function search( $p_field_name, $p_value ) {
        if( property_exists( User\Model, $p_field_name ) && isset( $this->cache ) ) {
            if( $p_field_name == 'id' ) {
                return $this->getById( $p_value );
            }

            foreach( $this->cache as $t_user ) {
                if( $t_user instanceof User\Model ) {
                    if( $t_user->$p_field == $p_value ) {
                        return $t_user;
                    }
                }
            }
        }
        return false;
    }

    public function getById( $p_id ) {
        if( $this->cache[$p_id] instanceof User\Model ) {
            return $this->cache[$p_id];
        } else {
            return false;
        }
    }

    public function add( $p_user ) {
        if( $p_user instanceof Model ) {
            $this->cache[ $t_user->id ] = $t_user;
        }
    }

    public function clear( $p_id = null ) {
        if( null === $p_id ) {
            $this->cache = array();
        } else {
            unset( $this->cache[$p_id] );
        }

        return true;
    }
}

