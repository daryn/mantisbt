<?php
namespace MantisBT\User;

# MantisBT - a php based bugtracking system

# @todo add new license text

/**
 *	User\Db class to handle loading and modifying user data.
 * @package MantisBT
 * @subpackage classes
 */
class Db {
    private $db = null;
    private $cache = null;
    private $table = null;
    private $anonymousAccount = null;

    public function __construct( $p_db, $p_cache, $p_anonymous_account ) {
        # @todo check for interface compliance
        $this->db = $p_db;
        $this->table = $p_db->getTable( 'user' );
        if( $p_cache instanceof CacheInterface ) {
            $this->cache = $p_cache;
        }
        $this->anonymousAccount = $p_anonymous_account;
    }

    /**
     * Increment the number of times the user has logegd in
     * This function is only called from the login.php script
     * @param int $p_user_id
     */
    public function incrementLoginCount( $p_user_id ) {
        $t_query = "UPDATE {$this->table}
            SET login_count=login_count+1
            WHERE id=" . $this->db->param();

        $this->db->queryBound( $t_query, array( $p_user_id ) );
        $this->cache->clear( $p_user_id );
        # db_query errors on failure so:
        return true;
    }

    /**
     * Reset to zero the failed login attempts
     * @param int $p_user_id
     */
    public function resetFailedLoginCount( $p_user_id ) {
        $t_query = "UPDATE {$this->table}
            SET failed_login_count=0
            WHERE id=" . $this->db->param();
        $this->db->queryBound( $t_query, array( $p_user_id ) );
        $this->cache->clear( $p_user_id );

        return true;
    }

    /**
     * Reset to zero the 'lost password' in progress attempts
     * @param int $p_user_id
     */
    public function resetLostPasswordInProgressCount( $p_user_id ) {
        $t_query = "UPDATE {$this->table}
            SET lost_password_request_count=0
            WHERE id=" . $this->db->param();
        $this->db->queryBound( $t_query, array( $p_user_id ) );
        $this->cache->clear( $p_user_id );

        return true;
    }

    /**
     * Increment the failed login count by 1 and remove the user from the cache
     * @param int $p_user_id
     */
    public function incrementFailedLoginCount( $p_user_id ) {
        $t_query = "UPDATE {$this->table}
        SET failed_login_count=failed_login_count+1
        WHERE id=" . $this->db->param();
        $this->db->queryBound( $t_query, array( $p_user_id ) );
        $this->cache->clear( $p_user_id );

        return true;
    }


    /**
     * Return true if the cookie login identifier is unique, false otherwise
     * @param string $p_cookie_string 
     * @return bool indicating whether cookie string is unique
     * @access public
     */
    public function isCookieStringUnique( $p_cookie_string ) {
        $t_query = "SELECT COUNT(*)
                      FROM {$this->table}
                      WHERE cookie_string=" . $this->db->param();
        $t_result = $this->db->queryBound( $t_query, array( $p_cookie_string ) );
        $t_count = $this->db->result( $t_result );

        if( $t_count > 0 ) {
            return false;
        } else {
            return true;
        }   
    }

	/**
	 *	Return a user from the database.
	 *	@param int $p_id
	 *	@return user object
	 *	@access public
	 */
	public function getById( $p_id ) {
        $t_user = $this->cache->getById( $p_id );
		if ( !$t_user ) {
			$t_query = "SELECT * FROM {$this->table} WHERE id=" . $this->db->param();
			$t_result = $this->db->queryBound( $t_query, array( $p_id ) );

			if( 0 == $this->db->numRows( $t_result ) ) {
				throw new Exception( ERROR_USER_BY_ID_NOT_FOUND );
			}

			$t_row = $this->db->fetchArray( $t_result );
			$t_user = new Model( $t_row, $this->anonymousAccount );
            $this->cache->add( $t_user );
		}
		return $t_user;
	}

    public function getByUsername( $p_user_name ) {
        $t_user = $this->cache->search( 'username', $p_user_name );
        if( !$t_user ) {
            $t_query = "SELECT * FROM {$this->table} WHERE username = " . $this->db->param();
            $t_result = $this->db->queryBound( $t_query, array( $p_user_name ) );

            if( 1 == $this->dbNumRows( $t_result ) ) {
                $t_row = $this->db->fetchArray( $t_result );
		        $t_user = new Model( $t_row, $this->anonymousAccount );
                $this->cache->add( $t_user );
            } else {
                return false;
            }
        }
		return $t_user;
    }

    public function getByCookie( $p_cookie_string ) {
        $t_user = $this->cache->search( 'cookie_string', $p_cookie_string );
        if( !$t_user ) {
            $t_query = "SELECT * FROM {$this->table} WHERE cookie_string = " . $this->db->param();
            $t_result = $this->db->queryBound( $t_query, array( $p_cookie_string ) );

            if( 1 == $this->dbNumRows( $t_result ) ) {
                $t_row = $this->db->fetchArray( $t_result );
		        $t_user = new Model( $t_row, $this->anonymousAccount );
                $this->cache->add( $t_user );
            } else {
		        return false;
            }
        }
		return $t_user;
    }

	/**
	 *	@access public
	 */
	public function getByIds( $p_user_id_array ) {
		$t_query = "SELECT *
				  FROM {$this->table}
				  WHERE id IN (" . implode( ',', $p_user_id_array ) . ')';
		$t_result = $this->db->queryBound( $t_query );

		$t_users = array();
		while( $t_row = $this->db->fetchArray( $t_result ) ) {
			$t_user = new Model( $t_row, $this->anonymousAccount );
			$t_users[$t_user->id] = $t_user;
            $this->cache->add( $t_user );
		}
		return $t_users;
	}

	public function getAll( $p_user_filter ) {
		$t_query = "SELECT * FROM {$this->table} ORDER BY " . $p_user_filter->sortColumn . ' ' . $p_user_filter->sortDirection;

		$t_result = $this->db->queryBound( $t_query, null, $p_user_filter->perPage, $p_user_filter->getFilterOffset() );

		while( $t_row = $this->db->fetchArray( $t_result ) ) {
			$t_user = new Model( $t_row, $this->anonymousAccount );
			$t_users[$t_row['id']] = $t_user;
            $this->cache->add( $t_user );
		}

		return $t_users;
	}

	/**
	 *	Filtered
	 */
	public function getByFilter( $p_filter ) {

		$t_params = array();
		$t_days_old = 7 * SECONDS_PER_DAY;
		if( $p_filter->_hideInactive ) {
			$t_hide = $this->db->helperCompareDays( "" . $this->db->now() . "", "last_visit", "< " . $this->db->param() );
		} else {
			$t_hide = '';
		}

		switch( $p_filter->_filter ) {
			case 'NEW':
				$t_where = $this->db->helperCompareDays( "" . $this->db->now() . "", "date_created", "<= " . $this->db->param() . "" );

				$t_params[] = $t_days_old;
				if( $p_filter->_hideInactive ) {
					$t_hide = ' AND ' . $t_hide;
					$t_params[] = $t_days_old;
				}
				$t_query = "SELECT * FROM {$this->table} WHERE $t_where $t_hide ORDER BY " . $p_filter->_sortColumn . ' ' . $p_filter->_sortDirection;
			break;
			case 'UNUSED':
				$t_where = '(login_count = 0) AND ( date_created = last_visit )';

				if( $p_filter->_hideInactive ) {
					$t_hide = ' AND ' . $t_hide;
					$t_params[] = $t_days_old;
				}
				$t_query = "SELECT * FROM {$this->table} WHERE $t_where $t_hide ORDER BY " . $p_filter->_sortColumn . ' ' . $p_filter->_sortDirection;
			break;
			case 'ALL':
				if( $p_filter->_hideInactive ) {
					$t_hide = 'WHERE ' . $t_hide;
					$t_params[] = $t_days_old;
				}
				$t_query = "SELECT * FROM {$this->table} $t_hide ORDER BY " . $p_filter->_sortColumn . ' ' . $p_filter->_sortDirection;
			break;
			default:
				$t_params[] = $p_filter->_filter . '%';
				if( $p_filter->_hideInactive ) {
					$t_hide = ' AND ' . $t_hide;
					$t_params[] = $t_days_old;
				}
				$t_query = "SELECT * FROM {$this->table} WHERE username LIKE " . $this->db->param() . $t_hide;
			break;
		}

		$t_result = $this->db->queryBound( $t_query, $t_params, $p_filter->_perPage, $p_filter->getFilterOffset() );
		while( $t_row = $this->db->fetchArray( $t_result ) ) {
			$t_user = new Model( $t_row, $this->anonymousAccount );
			$t_users[$t_row['id']] = $t_user;
		}
		return $t_users;
	}

	public function getFilteredCount( $p_filter ) {
		$t_user_table = $this->db->getTable( 'user' );
		$t_params = array();
		$t_days_old = 7 * SECONDS_PER_DAY;
		if( self::$hide_inactive ) {
			$t_hide = $this->db->helperCompareDays( "" . $this->db->now() . "", "last_visit", "< " . $this->db->param() );
		} else {
			$t_hide = '';
		}

		switch( $p_filter ) {
			case 'NEW':
				$t_where = $this->db->helperCompareDays( "" . $this->db->now() . "", "date_created", "<= " . $this->db->param() . "" );


				$t_params[] = $t_days_old;
				if( self::$hide_inactive ) {
					$t_params[] = $t_days_old;
					$t_hide = ' AND ' . $t_hide;
				}
				$t_query = "SELECT COUNT(id) FROM $t_user_table WHERE $t_where $t_hide ORDER BY " . self::$sort_column . ' ' . self::$sort_direction;
			break;
			case 'UNUSED':
				$t_where = '(login_count = 0) AND ( date_created = last_visit )';

				if( self::$hide_inactive ) {
					$t_hide = ' AND ' . $t_hide;
					$t_params[] = $t_days_old;
				}
				$t_query = "SELECT COUNT(id) FROM $t_user_table WHERE $t_where $t_hide ORDER BY " . self::$sort_column . ' ' . self::$sort_direction;
			break;
			case 'ALL':
				if( self::$hide_inactive ) {
					$t_hide = 'WHERE ' . $t_hide;
					$t_params[] = $t_days_old;
				}
				$t_query = "SELECT COUNT(id) FROM $t_user_table $t_hide ORDER BY " . self::$sort_column . ' ' . self::$sort_direction;
			break;
			default:
				$t_params[] = self::$filter . '%';
				if( self::$hide_inactive ) {
					$t_hide = ' AND ' . $t_hide;
					$t_params[] = $t_days_old;
				}
				$t_query = "SELECT COUNT(id) FROM $t_user_table WHERE username LIKE " . $this->db->param() . $t_hide;
			break;
		}

		$t_result = $this->db->queryBound( $t_query, $t_params );
		$t_count = $this->db->result( $t_result );
		if( $p_filter == self::$filter ) {
			self::$user_count = $t_count;
		}
		return $t_count;
	}

    /**
     * Create a user.
     * returns false if error, the generated cookie string if ok
     * @todo depends on config
     */
    public function create( Auth $p_auth, Config $p_config, Model $p_user ) {
#$p_username, $p_password, $p_email = '',
#        $p_access_level = null, $p_protected = false, $p_enabled = true,
#        $p_realname = '', $p_admin_name = '' ) {

        if( null === $p_user->access_level ) {
            $p_user->access_level = $p_config->default_new_account_access_level;
        }

        $t_password = $p_auth->processPlainPassword( $p_user->password );

        $c_access_level = db_prepare_int( $p_user->access_level );
        $c_protected = db_prepare_bool( $p_user->protected );
        $c_enabled = db_prepare_bool( $p_user->enabled );

        if( !$p_user->isUsernameValid( $p_config->user_login_valid_regex ) ) {
            throw new Exception( ERROR_USER_NAME_INVALID );
        }
        if( !$this->isUsernameUnique( $p_user->username ) ) {
            throw new Exception( ERROR_USER_NAME_NOT_UNIQUE );
        }
        $t_result = $this->getRealnameDuplicates( $p_user->username, $p_user->realname );
        if( $t_result ) {
            if( $p_config->differentiate_duplicates ) {
                $this->differentiateDuplicateRealnames( $t_result );
            } else {
                # differentiate duplicates was never completed.
                throw new Exception( ERROR_USER_REAL_MATCH_USER );
            }
        }
        
        email_ensure_valid( $p_user->email );

        $t_seed = $p_user->email . $p_user->username;
        $t_cookie_string = $p_auth->generateUniqueCookieString( $t_seed );

        $t_query = "INSERT INTO {$this->table}
            ( username, email, password, date_created, last_visit,
                enabled, access_level, login_count, cookie_string, realname )
                VALUES
                    ( " . $this->db->param() . ', ' . $this->db->param() . ', ' . $this->db->param() . ', ' . $this->db->param() . ', ' . $this->db->param()  . ",
                    " . $this->db->param() . ',' . $this->db->param() . ',' . $this->db->param() . ',' . $this->db->param() . ', ' . $this->db->param() . ')';
        $t_now = $this->db->now();
        $this->db->queryBound( $t_query, array( $p_user->username, $p_user->email, $t_password, $t_now, $t_now, $c_enabled, $c_access_level, 0, $t_cookie_string, $p_realname ) );

        # Create preferences for the user
        $t_user_id = $this->db->insertId( $this->table );

        # Users are added with protected set to FALSE in order to be able to update
        # preferences.  Now set the real value of protected.
        if( $c_protected ) {
            $this->setField( $t_user_id, 'protected', 1 );
        }

        # Send notification email
        if( trim( $p_user->email ) != "" ) {
            $t_confirm_hash = $p_auth->generateConfirmHash( $t_user_id );
            email_signup( $t_user_id, $p_password, $t_confirm_hash, $p_admin_name );
        }

        return $t_cookie_string;
    }

    /**
     * return true if the username is unique, false if there is already a user
     * with that username
     * @param string $p_username
     * @return bool
     */
    public function isUsernameUnique( $p_username ) {
        $t_query = "SELECT username
            FROM {$this->table}
            WHERE username=" . $this->db->param();
        $t_result = $this->db->queryBound( $t_query, array( $p_username ), 1 );

        if( $this->db->numRows( $t_result ) > 0 ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check if the realname is a valid username (does not account for uniqueness)
     * @return mixed 0 if it is invalid, an array of matches
     */
    public function getRealnameDuplicates( $p_username, $p_realname,  ) {
        $p_username = trim( $p_username );
        $p_realname = trim( $p_realname );
        if( $p_realname == "" ) {
            # don't bother checking if realname is blank
            return 1;
        }

        # allow realname to match username
        $t_count = 0;
        if( $p_realname <> $p_username ) {
            # check realname does not match an existing username
            #  but allow it to match the current user
            $t_target_user = $this->getByUsername( $p_username );
            $t_other_user = $this->getByUsername( $p_realname );
            if( ( $t_other_user ) && ( $t_target_user->id != $t_other_user->id ) ) {
                return 0;
            }

            # check to see if the realname is unique
            $t_query = "SELECT id
                FROM {$this->table}
                WHERE realname=" . $this->db->param();
            $t_result = $this->db->queryBound( $t_query, array( $p_realname ) );
            $t_count = $this->db->numRows( $t_result );

            if( $t_count > 0 ) {
                return $t_result;
            }
        }
    }

    public function differentiateDuplicateRealnames( $p_result ) {
        $t_count = $this->db->numRows( $p_result );
        # set flags for non-unique realnames
        for( $i = 0;$i < $t_count;$i++ ) {
            $t_user_id = $this->db->result( $p_result, $i );
            $this->setField( $t_user_id, 'duplicate_realname', ON );
        }
    }

    # Set a user field
    public function setField( $p_user_id, $p_field_name, $p_field_value ) {
        $p_user = $this->getById( $p_user_id );

        if( $p_field_name != 'protected' && $p_user->isProtected() ) {
            throw new Exception( ERROR_PROTECTED_ACCOUNT );
        }

        $t_query = "UPDATE {$this->table} SET " . $this->db->param() . "=" . $this->db->param() .  ' WHERE id=' . $this->db->param();
        $this->db->queryBound( $t_query, array( $p_field_name, $p_field_value, $p_user_id ) );
        $this->cache->clear( $p_user->id );

        # db_query errors on failure so:
        return true;
    }
}
