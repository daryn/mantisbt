<?php
namespace MantisBT\Config;

# MantisBT - a php based bugtracking system

# @todo add new license text

/**
 *	User\Cache class for caching user models.
 * @package MantisBT
 * @subpackage classes
 */
class Cache implements CacheInterface {
    private $full = false;
    /**
     *  $cache = array(
        ALL_USERS=>array(
            ALL_PROJECTS=>array(
                'config_1'=>value,
                'config_2'=>value,
                ),
            PROJECT_N=array(
                'config_1'=>value,
                'config_2'=>value,
                ),
            ),
        USER_N=>array(
            ALL_PROJECTS=>array(
                'config_1'=>value,
                'config_2'=>value,
                ),
            PROJECT_N=array(
                'config_1'=>value,
                'config_2'=>value,
                ),
            ),
     */
    private $cache = null;

    /**
     */
    public function search( $p_config_id, $p_user_id, $p_project_id ) {
        if( isset( $this->cache ) ) {
            if( array_key_exists( $p_user_id, $this->cache ) &&
                array_key_exists( $p_project_id, $this->cache[$p_user_id] ) &&
                array_key_exists( $p_config_id, $this->cache[$p_user_id][$p_project_id] ) ) {
                # these are user specific for either a specific project or all projects
                return $this->cache[$p_user_id][$p_project_id][$p_config_id];
            } else if( array_key_exists( $p_project_id, $this->cache[ALL_USERS] ) &&
                array_key_exists( $p_config_id, $this->cache[ALL_USERS][$p_project_id] ) ) {
                # these are for all users for a specific project
                return $this->cache[ALL_USERS][$p_project_id][$p_config_id];
            } else if( array_key_exists( $p_config_id, $this->cache[ALL_USERS][ALL_PROJECTS] ) ) {
                # these are all the defaults
                return $this->cache[ALL_USERS][ALL_PROJECTS][$p_config_id];
            } else {
                return false;
            }
        }
        return false;
    }

    public function add( Model $p_config ) {
        if( $p_config instanceof Model ) {
            $this->cache[$p_config->user_id][$p_config->project_id][ $p_config->config_id ] = $p_config;
        }
    }

    public function clear( $p_config_id=null, $p_user_id=null, $p_project_id=null ) {
        $t_user_ids = array_keys( $this->cache );
        if( !is_null( $p_user_id ) ) {
            if( !is_null( $p_project_id ) ) {
                # user not null, project is not null
                if( !is_null( $p_config_id ) ) {
                    # unset the specified config_id where the user and project matches
                    if( array_key_exists( $p_user_id, $this->cache ) &&
                        array_key_exists( $p_project_id, $this->cache[$p_user_id] )&&
                        array_key_exists( $p_config_id, $this->cache[$p_user_id][$p_project_id] ) ) {
                        unset( $this->cache[$p_user_id][$p_project_id][$p_config_id] );
                    }
                } else {
                    # unset cache record where the user and project matches
                    if( array_key_exists( $p_user_id, $this->cache ) &&
                        array_key_exists( $p_project_id, $this->cache[$p_user_id] ) ) {
                        unset( $this->cache[$p_user_id][$p_project_id] );
                    }
                }
            } else {
                # user not null, project is null
                if( is_null( $p_config_id ) ) {
                    # only have the user id, unset all cache for the specified user
                    if( array_key_exists( $p_user_id, $this->cache ) ) {
                        unset( $this->cache[$p_user_id] );
                    }
                } else {
                    # have the user id and a config id unset the config in each project for the specified user
                    foreach( $this->cache[$p_user_id] AS $t_project_id => $t_configs ) {
                        if( in_array( $t_configs, $p_config_id ) ) {
                            unset( $this->cache[$p_user_id][$t_project_id][$p_config_id] );
                        }
                    }
                }
            }
        } else {
            # user is null, project is not null
            if( !is_null( $p_project_id ) ) {
                # user is null, project is not null, config is not null
                if( !is_null( $p_config_id ) ) {
                    foreach( $t_user_ids AS $t_id ) {
                        # delete the config for each user in each project
                        if( array_key_exists( $p_project_id, $this->cache[$t_id] ) ) {
                            unset( $this->cache[$t_id][$p_project_id][$p_config_id] );
                        }
                    }
                } else {
                    # user is null and config is null
                    # delete the project for each user
                    foreach( $t_user_ids AS $t_id ) {
                        # we already know the t_id exists in the array. only check the project
                        if( array_key_exists( $p_project_id, $this->cache[$t_id] ) ) {
                            unset( $this->cache[$t_id][$p_project_id] );
                        }
                    }
                }
            } else if( !is_null( $p_config_id ) ) {
                # user is null, project is null, config is not null
                foreach( $t_user_ids AS $t_id ) {
                    foreach( $this->cache[$t_id] AS $t_project_id=>$t_configs ) {
                        if( array_key_exists( $p_config_id, $this->cache[$t_id][$t_project_id] ) ) {
                            unset( $this->cache[$t_id][$p_project_id][$p_config_id] );
                        }
                    }
                }
            } else {
                # user is null, project is null, config is null
                $this->cache = array();
                $this->full = false;
            }
        }
    }
    
    public function isFull() {
        return $this->full;
    }

    public function setFull( $p_full ) {
        $this->full = $p_full;
    }
}

