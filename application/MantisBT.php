<?php
# MantisBT - a php based bugtracking system

# @todo needs new license text

/**
 * @package MantisBT
 * @subpackage classes
 */
class MantisBT {
    public static function main() {
        $t_application = MantisBT\Injector::buildApplication();
        $t_helper = MantisBT\Injector::injectHelper( $t_application );
        $t_helper->bootstrap();
        $t_helper->run();
    }
}
