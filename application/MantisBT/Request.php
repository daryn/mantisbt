<?php
namespace MantisBT;

class Request {
    protected $cookiePath;
    protected $cookieDomain;

    /**
     * 
     */
    protected $cookieExpiry;

    public function __construct( $p_cookie_path, $p_cookie_domain, $p_cookie_expiry ){
        $this->cookiePath = $p_cookie_path;
        $this->cookieDomain = $p_cookie_domain;
        $this->cookieExpiry = $p_cookie_expiry;
    }

    public function isGet() {
        if ('GET' == $this->getRequestMethod() ) {
            return true;
        }
        return false;
    }

    public function getQuery($p_key = null, $p_default = null ) {
        if ( null === $p_key ) {
            return $_GET;
        }
        return (isset($_GET[$p_key])) ? $_GET[$p_key] : $p_default;
    }

    public function isPost() {
        if ('POST' == $this->getRequestMethod() ) {
            return true;
        }
        return false;
    }

    public function getPost( $p_key = null, $p_default = null ) {
        if (null === $p_key) {
            return $_POST;
        }
        return ( isset( $_POST[ $p_key] ) ) ? $_POST[ $p_key ] : $p_default;
    }

    public function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * GET, POST, and Cookie API
     * ---------------
     * Retrieve a GPC variable.
     * If the variable is not set, the default is returned.
     *
     *  You may pass in any variable as a default (including null) but if
     *  you pass in *no* default then an error will be triggered if the field
     *  cannot be found
     *
     * @param string
     * @return null
     */
    public function get( $p_var_name, $p_default = null ) {
    	if( $this->getRequestMethod() == 'POST' ) {
    		$t_result = $this->getPOST[ $p_var_name, $p_default );
    	} else if ( $this->getRequestMethod() == 'GET' ) {
    		$t_result = $this->getQuery[ $p_var_name, $p_default );
        }

    	if( is_null( $t_result ) ) {
            if( !is_null( $p_default ) ) {
    		    # check for a default passed in (allowing null)
    		    $t_result = $p_default;
    	    } else {
    	    	error_parameters( $p_var_name );
    	    	trigger_error( ERROR_GPC_VAR_NOT_FOUND, ERROR );
    	    	$t_result = null;
    	    }
        }
    	return $t_result;
    }

    /**
     * @param string $p_var_name
     * @return bool
     */
    public function isSet( $p_var_name ) {
    	if( isset( $_POST[$p_var_name] ) ) {
    		return true;
    	} else if( isset( $_GET[$p_var_name] ) ) {
    		return true;
    	}

    	return false;
    }

    /**
     * Retrieve a string GPC variable. Uses gpc_get().
     * If you pass in *no* default, an error will be triggered if
     * the variable does not exist
     * @param string $p_var_name
     * @param string $p_default (optional)
     * @return string|null
     */
    public function getString( $p_var_name, $p_default = null ) {
    	$t_result = $this->get( $p_var_name, $p_default );

    	if( is_array( $t_result ) ) {
    		error_parameters( $p_var_name );
    		trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
    	}

    	return $t_result;
    }

    /**
     * Retrieve an integer GPC variable. Uses gpc_get().
     * If you pass in *no* default, an error will be triggered if
     * the variable does not exist
     * @param string $p_var_name
     * @param int $p_default (optional)
     * @return int|null
     */
    public function getInt( $p_var_name, $p_default = null ) {
    	$t_result = $this->get( $p_var_name, $p_default );

    	if( is_array( $t_result ) ) {
    		error_parameters( $p_var_name );
    		trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
    	}
    	$t_val = str_replace( ' ', '', trim( $t_result ) );
    	if( !preg_match( "/^-?([0-9])*$/", $t_val ) ) {
    		error_parameters( $p_var_name );
    		trigger_error( ERROR_GPC_NOT_NUMBER, ERROR );
    	}

    	return (int) $t_val;
    }

    /**
     * Retrieve a boolean GPC variable. Uses gpc_get().
     *  If you pass in *no* default, false will be used
     * @param string $p_var_name
     * @param bool $p_default (optional)
     * @return bool|null
     */
    public function getBool( $p_var_name, $p_default = false ) {
    	$t_result = $this->get( $p_var_name, $p_default );

    	if( $t_result === $p_default ) {
    		return $p_default;
    	} else {
    		if( is_array( $t_result ) ) {
    			error_parameters( $p_var_name );
    			trigger_error( ERROR_GPC_ARRAY_UNEXPECTED, ERROR );
    		}

    		return $this->stringToBool( $t_result );
    	}
    }

    /**
     * see if a custom field variable is set.  Uses $this->isSet().
     * @param string $p_var_name
     * @param int $p_custom_field_type
     * @return bool
     */
    public function issetCustomField( $p_var_name, $p_custom_field_type ) {
    	$t_field_name = 'custom_field_' . $p_var_name;

    	switch ($p_custom_field_type ) {
    		case CUSTOM_FIELD_TYPE_DATE:
    			// date field is three dropdowns that default to 0
    			// Dropdowns are always present, so check if they are set
    			return $this->isSet( $t_field_name . '_day' ) &&
    				$this->getInt( $t_field_name . '_day', 0 ) != 0 &&
    				$this->isSet( $t_field_name . '_month' ) &&
    				$this->getInt( $t_field_name . '_month', 0 ) != 0 &&
    				$this->isSet( $t_field_name . '_year' ) &&
    				$this->getInt( $t_field_name . '_year', 0 ) != 0 ;
    		case CUSTOM_FIELD_TYPE_STRING:
    		case CUSTOM_FIELD_TYPE_NUMERIC:
    		case CUSTOM_FIELD_TYPE_FLOAT:
    		case CUSTOM_FIELD_TYPE_ENUM:
    		case CUSTOM_FIELD_TYPE_EMAIL:
    			return $this->isSet( $t_field_name ) && !is_blank( $this->getString( $t_field_name ) );
    		default:
    			return $this->isSet( $t_field_name );
    	}
    }

    /**
     * Retrieve a custom field variable.  Uses gpc_get().
     * If you pass in *no* default, an error will be triggered if
     * the variable does not exist
     * @param string $p_var_name
     * @param int $p_custom_field_Type
     * @param mixed $p_default
     * @return string
     */
    public function getCustomField( $p_var_name, $p_custom_field_type, $p_default = null ) {
    	switch( $p_custom_field_type ) {
    		case CUSTOM_FIELD_TYPE_MULTILIST:
    		case CUSTOM_FIELD_TYPE_CHECKBOX:
    		    // ensure that the default is an array, if set
    		    if ( ($p_default !== null) && !is_array($p_default) ) {
    		        $p_default = array( $p_default );
    		    }
    			$t_values = $this->getStringArray( $p_var_name, $p_default );
    			if( is_array( $t_values ) ) {
    				return implode( '|', $t_values );
    			} else {
    				return '';
    			}
    			break;
    		case CUSTOM_FIELD_TYPE_DATE:
    			$t_day = $this->getInt( $p_var_name . '_day', 0 );
    			$t_month = $this->getInt( $p_var_name . '_month', 0 );
    			$t_year = $this->getInt( $p_var_name . '_year', 0 );
    			if(( $t_year == 0 ) || ( $t_month == 0 ) || ( $t_day == 0 ) ) {
    				if( $p_default == null ) {
    					return '';
    				} else {
    					return $p_default;
    				}
    			} else {
    				return strtotime( $t_year . '-' . $t_month . '-' . $t_day );
    			}
    			break;
    		default:
    			return $this->getString( $p_var_name, $p_default );
    	}
    }

    /**
     * Retrieve a string array GPC variable.  Uses gpc_get().
     * If you pass in *no* default, an error will be triggered if
     * the variable does not exist
     * @param string $p_var_name
     * @param array $p_default
     * @return array
     */
    public function getStringArray( $p_var_name, $p_default = null ) {
    	$t_result = $this->get( $p_var_name, $p_default );

    	# If we the result isn't the default we were given or an array, error
    	if( !((( 1 < func_num_args() ) && ( $t_result === $p_default ) ) || is_array( $t_result ) ) ) {
    		error_parameters( $p_var_name );
    		trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR );
    	}

    	return $t_result;
    }

    /**
     * Retrieve an integer array GPC variable.  Uses gpc_get().
     * If you pass in *no* default, an error will be triggered if
     * the variable does not exist
     * @param string $p_var_name
     * @param array $p_default
     * @return array
     */
    public function getIntArray( $p_var_name, $p_default = null ) {
    	$t_result = $this->get( $p_var_name, $p_default );

    	# If we the result isn't the default we were given or an array, error
    	if( !((( 1 < func_num_args() ) && ( $t_result === $p_default ) ) || is_array( $t_result ) ) ) {
    		error_parameters( $p_var_name );
    		trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR );
    	}

    	$t_count = count( $t_result );
    	for( $i = 0;$i < $t_count;$i++ ) {
    		$t_result[$i] = (int) $t_result[$i];
    	}

    	return $t_result;
    }

    /**
     * Retrieve a boolean array GPC variable.  Uses gpc_get().
     * If you pass in *no* default, an error will be triggered if the variable does not exist.
     * @param string $p_var_name
     * @param string $p_default
     * @return array
     */
    public function getBoolArray( $p_var_name, $p_default = null ) {
    	$t_result = $this->get( $p_var_name, $p_default );

    	# If we the result isn't the default we were given or an array, error
    	if( !((( 1 < func_num_args() ) && ( $t_result === $p_default ) ) || is_array( $t_result ) ) ) {
    		error_parameters( $p_var_name );
    		trigger_error( ERROR_GPC_ARRAY_EXPECTED, ERROR );
    	}

    	$t_count = count( $t_result );
    	for( $i = 0; $i < $t_count; $i++ ) {
    		$t_result[$i] = $this->stringToBool( $t_result[$i] );
    	}

    	return $t_result;
    }

    /**
     * Retrieve a cookie variable
     * You may pass in any variable as a default (including null) but if
     * you pass in *no* default then an error will be triggered if the cookie cannot be found
     * @param string $p_var_name
     * @param string $p_default
     * @return string
     */
    public function getCookie( $p_var_name, $p_default = null ) {
    	if( isset( $_COOKIE[$p_var_name] ) ) {
    		$t_result = $_COOKIE[$p_var_name];
    	}
    	else if( func_num_args() > 1 ) {
    		# check for a default passed in (allowing null)
    		$t_result = $p_default;
    	} else {
    		error_parameters( $p_var_name );
    		trigger_error( ERROR_GPC_VAR_NOT_FOUND, ERROR );
    	}

    	return $t_result;
    }

    /**
     * Set a cookie variable
     * If $p_expire is false instead of a number, the cookie will expire when
     * the browser is closed; if it is true, the default time from the config
     * file will be used.
     * If $p_path or $p_domain are omitted, defaults are used.
     * Set $p_httponly to false if client-side Javascript needs to read/write
     * the cookie. Otherwise it is safe to leave this value unspecified, as
     * the default value is true.
     * @todo this function is to be modified by Victor to add CRC... for now it just passes the parameters through to setcookie()
     * @param string $p_name
     * @param string $p_value
     * @param bool $p_expire default false
     * @param string $p_path default null
     * @param string $p_domain default null
     * @param bool $p_httponly default true
     * @return bool - true on success, false on failure
     */
    public function setCookie( $p_name, $p_value, $p_expire = false, $p_path = null, $p_domain = null, $p_httponly = true ) {
    	if( false === $p_expire ) {
    		$p_expire = 0;
    	}
    	else if( true === $p_expire ) {
    		$t_cookie_length = $this->cookieExpiry;
    		$p_expire = time() + $t_cookie_length;
    	}
    	if( null === $p_path ) {
    		$p_path = $this->cookiePath;
    	}
    	if( null === $p_domain ) {
    		$p_domain = $this->cookieDomain;
    	}

    	return setcookie( $p_name, $p_value, $p_expire, $p_path, $p_domain, $this->isSecureRequest(), true );
    }

    /**
     * Clear a cookie variable
     * @param string $p_name
     * @param string $p_path
     * @param string $p_domain
     * @return bool
     */
    public function clearCookie( $p_name, $p_path = null, $p_domain = null ) {
    	if( null === $p_path ) {
    		$p_path = $this->cookiePath;
    	}
    	if( null === $p_domain ) {
    		$p_domain = $this->cookieDomain;
    	}

    	if( isset( $_COOKIE[$p_name] ) ) {
    		unset( $_COOKIE[$p_name] );
    	}

    	# dont try to send cookie if headers are send (guideweb)
    	if( !headers_sent() ) {
    		return setcookie( $p_name, '', -1, $p_path, $p_domain );
    	} else {
    		return false;
    	}
    }

    /**
     * Retrieve a file variable
     * You may pass in any variable as a default (including null) but if
     * you pass in *no* default then an error will be triggered if the file
     * cannot be found
     * @param string $p_var_name
     * @param mixed $p_file
     * @return mixed
     */
    public function getFile( $p_var_name, $p_default = null ) {
    	if( isset( $_FILES[$p_var_name] ) ) {
    		# FILES are not escaped even if magic_quotes is ON, this applies to Windows paths.
    		$t_result = $_FILES[$p_var_name];
    	} else if( func_num_args() > 1 ) {
    		# check for a default passed in (allowing null)
    		$t_result = $p_default;
    	} else {
    		error_parameters( $p_var_name );
    		trigger_error( ERROR_GPC_VAR_NOT_FOUND, ERROR );
    	}

    	return $t_result;
    }

    /**
     * Convert a POST/GET parameter to an array if it is not already one.
     * @param string $p_var_name - The name of the parameter
     * @return null no return value.  The $_POST/$_GET are updated as appropriate.
     */
    public function makeArray( $p_var_name ) {
        if( $this->isPost() ) {
    	    if( isset( $_POST[$p_var_name] ) && !is_array( $_POST[$p_var_name] ) ) {
    	    	$_POST[$p_var_name] = array(
    	    		$_POST[$p_var_name],
    	    	);
    	    }
        }

        if( $this->isGet() ) {
    	    if( isset( $_GET[$p_var_name] ) && !is_array( $_GET[$p_var_name] ) ) {
    	    	$_GET[$p_var_name] = array(
    	    		$_GET[$p_var_name],
    	    	);
    	    }
        }
    }

    /**
     * Convert a string to a bool
     * @param string $p_string
     * @return bool
     */
    public function stringToBool( $p_string ) {
    	if( 0 == strcasecmp( 'off', $p_string ) || 0 == strcasecmp( 'no', $p_string ) || 0 == strcasecmp( 'false', $p_string ) || 0 == strcasecmp( '', $p_string ) || 0 == strcasecmp( '0', $p_string ) ) {
    		return false;
    	} else {
    		return true;
    	}
    }

    protected function isSecureRequest() {
        return ( isset( $_SERVER['HTTPS'] ) && ( utf8_strtolower( $_SERVER['HTTPS'] ) != 'off' ) );
    }
}
