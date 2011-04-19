<?php
namespace MantisBT;

# MantisBT - A PHP based bugtracking system

# @todo needs new license text

/**
 * Language (Internationalization) API
 *
 * @package CoreAPI
 * @subpackage LanguageAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class Translate {
    private $config = null;

    /**
     * Cache of localization strings in the language specified by the last
     * loadTranslation call
     */
    private $translations = array();

    # stack for language overrides
    private $overrides = array();
    private $activeTranslation = '';

    public function __construct( Config $p_config ) {
        $this->config = $p_config;
    }

    /**
     * Loads the specified language and stores it in $translations, to be used by get
     * @param string $p_lang
     * @param string $p_dir
     * @return null
     */
    public function loadTranslation( $p_lang, $p_dir = null ) {
        $this->activeTranslation = $p_lang;
        if( isset( $this->translations[$p_lang] ) && is_null( $p_dir ) ) {
            return;
        }

        if( !$this->translationExists( $p_lang ) ) {
            return;
        }

        // Step 1 - Load Requested Language file
        // @@ and if file doesn't exist???
        if( $p_dir === null ) {
            include_once( $this->config->get( 'language_path' ) . 'strings_' . $p_lang . '.txt' );
        } else {
            if( is_file( $p_dir . 'strings_' . $p_lang . '.txt' ) ) {
                include_once( $p_dir . 'strings_' . $p_lang . '.txt' );
            }
        }

        // Step 2 - Allow overriding strings declared in the language file.
        //          custom_strings_inc.php can use $g_active_language
        // 2 formats:
        // $s_* - old format
        // $s_custom_strings array - new format
        // NOTE: it's not expected that you'd mix/merge old/new formats within this file.
        $t_custom_strings = $this->config->get( 'custom_strings_file' ) ;
        if( file_exists( $t_custom_strings ) ) {
            # this may be loaded multiple times, once per language
            require( $t_custom_strings );		
        }
        
        // Step 3  - New Language file format
        // Language file consists of an array
        if( isset( $s_messages ) ) {
            // lang strings array entry can only be set if $p_dir is not null - i.e. in a plugin
            if( isset( $this->translations[$p_lang] ) ) {
                if( isset( $s_custom_messages[$p_lang] ) ) {
                    // Step 4 - handle merging in custom strings:
                    // Possible states:
                    // 4.a - new string format + new custom string format	
                    $this->translations[$p_lang] = array_replace( ((array)$this->translations[$p_lang]), (array)$s_messages, (array)$s_custom_messages[$p_lang]);
                    return;
                } else {
                    $this->translations[$p_lang] = array_replace( ((array)$this->translations[$p_lang]), (array)$s_messages);
                }
            } else {
                // new language loaded
                $this->translations[$p_lang] = $s_messages;
                if( isset( $s_custom_messages[$p_lang] ) ) {
                    // 4.a - new string format + new custom string format	
                    $this->translations[$p_lang] = array_replace( ((array)$this->translations[$p_lang]), (array)$s_custom_messages[$p_lang]);
                    return;
                }
            }
        }

        // 4.b new string format + old custom string format
        // 4.c - old string format + old custom string format
        if( !isset( $s_messages ) || file_exists( $t_custom_strings ) ) {
            $t_vars = get_defined_vars();

            foreach( array_keys( $t_vars ) as $t_var ) {
                $t_lang_var = preg_replace( '/^s_/', '', $t_var );
                if( $t_lang_var != $t_var ) {
                    $this->translations[$p_lang][$t_lang_var] = $$t_var;
                }
                else if( 'MANTIS_ERROR' == $t_var ) {
                    if( isset( $this->translations[$p_lang][$t_lang_var] ) ) {
                        foreach( $$t_var as $key => $val ) {
                            $this->translations[$p_lang][$t_lang_var][$key] = $val;
                        }
                    } else {
                        $this->translations[$p_lang][$t_lang_var] = $$t_var;
                    }
                }
            }
            // 4.d old string format + new custom string format
            // merge new custom strings into array in same way we merge in 4.a
            if( isset( $s_custom_messages[$p_lang] ) ) {
                $this->translations[$p_lang] = array_replace( ((array)$this->translations[$p_lang]), (array)$s_custom_messages[$p_lang]);
            }
        }
    }

    /**
     * Determine the preferred language
     * @return string
     * @todo has auth dependency
     * @todo has user prefs dependency
     */
    public function getDefault() {
        $t_lang = false;

        # Confirm that the user's language can be determined
        if( function_exists( 'auth_is_user_authenticated' ) && auth_is_user_authenticated() ) {
            $t_lang = user_pref_get_language( auth_get_current_user_id() );
        }

        # Otherwise fall back to default
        if( !$t_lang ) {
            $t_lang = $this->config->getGlobal( 'default_language' );
        }

        if( $t_lang == 'auto' ) {
            $t_lang = $this->mapAuto();
        }

        # Remember the language
        $this->activeTranslation = $t_lang;

        return $t_lang;
    }

    /**
     *
     * @return string
     */
    public function mapAuto() {
        $t_lang = $this->config->get( 'fallback_language' );

        if( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
            $t_accept_langs = explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
            $t_auto_map = $this->config->get( 'language_auto_map' );

            # Expand language map
            $t_auto_map_exp = array();
            foreach( $t_auto_map as $t_encs => $t_enc_lang ) {
                $t_encs_arr = explode( ',', $t_encs );

                foreach( $t_encs_arr as $t_enc ) {
                    $t_auto_map_exp[trim( $t_enc )] = $t_enc_lang;
                }
            }

            # Find encoding
            foreach( $t_accept_langs as $t_accept_lang ) {
                $t_tmp = explode( ';', utf8_strtolower( $t_accept_lang ) );

                if( isset( $t_auto_map_exp[trim( $t_tmp[0] )] ) ) {
                    $t_valid_langs = $this->config->get( 'language_choices_arr' );
                    $t_found_lang = $t_auto_map_exp[trim( $t_tmp[0] )];

                    if( in_array( $t_found_lang, $t_valid_langs, true ) ) {
                        $t_lang = $t_found_lang;
                        break;
                    }
                }
            }
        }

        return $t_lang;
    }

    /**
     * Ensures that a language file has been loaded
     * @param string $p_lang the language name
     * @return null
     */
    public function ensureLoaded( $p_lang ) {
        if( !isset( $this->translations[$p_lang] ) ) {
            $this->loadTranslation( $p_lang );
        }
    }

    /**
    * Check if the given language exists
    *
    * @param string $p_lang the language name
    * @return boolean
    */
    public function translationExists( $p_lang ) {
        $t_valid_langs = $this->config->get( 'language_choices_arr' );
        $t_valid = in_array( $p_lang, $t_valid_langs, true );
        return $t_valid;
    }

    /**
     * language stack implementation
     * push a language onto the stack
     * @param string $p_lang
     * @return null
     */
    public function push( $p_lang = null ) {
        # If no specific language is requested, we'll
        #  try to determine the language from the users
        #  preferences

        $t_lang = $p_lang;

        if( null === $t_lang ) {
            $t_lang = $this->config->get( 'default_language' );
        }

        # don't allow 'auto' as a language to be pushed onto the stack
        #  The results from auto are always the local user, not what the
        #  override wants, unless this is the first language setting
        if(( 'auto' == $t_lang ) && ( 0 < count( $g_lang_overrides ) ) ) {
            $t_lang = $this->config->get( 'fallback_language' );
        }

        $this->overrides[] = $t_lang;

        # Remember the language
        $this->activeTranslation = $t_lang;

        # make sure it's loaded
        $this->ensureLoaded( $t_lang );
    }

    /**
     * pop a language onto the stack and return it
     * @return string
     */
    public function pop() {
        return array_pop( $this->overrides );
    }

    /**
     * return value on top of the language stack
     * return default if stack is empty
     * @return string
     */
    public function getCurrent() {
        $t_count_overrides = count( $this->overrides );
        if( $t_count_overrides > 0 ) {
            $t_lang = $this->overrides[$t_count_overrides - 1];
        } else {
            $t_lang = $this->getDefault();
        }

        return $t_lang;
    }

    /**
     * Retrieves an internationalized string
     * This function will return one of (in order of preference):
     *  1. The string in the current user's preferred language (if defined)
     *  2. The string in English
     * @param string $p_string
     * @param string $p_lang
     * @param bool $p_error default: true - error if string not found
     * @return string
     * @todo has plugin dependency
     * @todo throw errors rather than trigger
     */
    public function get( $p_string, $p_lang = null, $p_error = true ) {

        # If no specific language is requested, we'll
        #  try to determine the language from the users
        #  preferences

        $t_lang = $p_lang;

        if( null === $t_lang ) {
            $t_lang = $this->getCurrent();
        }

        // Now we'll make sure that the requested language is loaded
        $this->ensureLoaded( $t_lang );

        // Step 1 - see if language string exists in requested language
        if( $this->stringExists( $p_string, $t_lang ) ) {
            return $this->translations[$t_lang][$p_string];
        } else {
            // Language string doesn't exist in requested language
            
            // Step 2 - See if language string exists in current plugin
            $t_plugin_current = plugin_get_current();
            if( !is_null( $t_plugin_current ) ) {
                // Step 3 - Plugin exists: load language file
                $this->loadTranslation( $t_lang, $this->config->get( 'plugin_path' ) . $t_plugin_current . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR );
                if( $this->stringExists( $p_string, $t_lang ) ) {
                    return $this->translations[$t_lang][$p_string];
                }
                
                // Step 4 - Localised language entry didn't exist - fallback to english for plugin
                $this->loadTranslation( 'english', $this->config->get( 'plugin_path' ) . $t_plugin_current . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR );
                if( $this->stringsExists( $p_string, $t_lang ) ) {
                    return $this->translations[$t_lang][$p_string];
                }			
            }

            // Step 5 - string didn't exist, try fall back to english:
            if( $t_lang == 'english' ) {
                if( $p_error ) {
                    error_parameters( $p_string );
                    trigger_error( ERROR_LANG_STRING_NOT_FOUND, WARNING );
                }
                return '';
            } else {
                // if string is not found in a language other than english, then retry using the english language.
                return $this->get( $p_string, 'english' );
            }
        }
    }

    /**
     * Check the language entry, if found return true, otherwise return false.
     * @param string $p_string
     * @param string $p_lang
     * @return bool
     */
    public function stringExists( $p_string, $p_lang ) {
        return( isset( $this->translations[$p_lang] ) && isset( $this->translations[$p_lang][$p_string] ) );
    }

    /**
     * Get language:
     * - If found, return the appropriate string (as lang_get()).
     * - If not found, no default supplied, return the supplied string as is.
     * - If not found, default supplied, return default.
     * @param string $p_string
     * @param string $p_default
     * @param string $p_lang
     * @return string
     */
    public function getDefaulted( $p_string, $p_default = null, $p_lang = null ) {
        $t_lang = $p_lang;

        if( null === $t_lang ) {
            $t_lang = $this->getCurrent();
        }

        # Now we'll make sure that the requested language is loaded
        $this->ensureLoaded( $t_lang );

        if( $this->stringExists( $p_string, $t_lang ) ) {
            return $this->get( $p_string );
        } else {
            if( null === $p_default ) {
                return $p_string;
            } else {
                return $p_default;
            }
        }
    }
}
