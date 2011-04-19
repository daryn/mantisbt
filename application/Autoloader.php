<?php
class Autoloader {
    public static function autoload( $p_class_name ) {
        # check for legacy classes in the core directory
	    $t_require_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $p_class_name . '.class.php';
	    if ( file_exists( $t_require_path ) ) {
	    	require_once( $t_require_path );
	    	return;
	    }

        # handle any namespaces
        $t_class_name = str_replace( '\\', '/', $p_class_name );

	    $t_require_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . $t_class_name . '.php';
	    if ( file_exists( $t_require_path ) ) {
	    	require_once( $t_require_path );
	    	return;
	    }

    	$t_require_path = 'rssbuilder' . DIRECTORY_SEPARATOR . 'class.' . $t_class_name . '.inc.php';

    	if ( file_exists( $t_require_path ) ) {
    		require_once( $t_require_path );
     		return;
    	}
    }
}
