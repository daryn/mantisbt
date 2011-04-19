<?php
namespace MantisBT;

class Controller {
    protected $_request = null;

    public function __construct( Request $p_request ) {
        # @todo needs an interface check.  no interface requirement exists yet
        $this->_request = $p_request;
    }

    public function indexAction() {
        # model

        # view
        if (isset($_GET['module'])) {
            $t_module = $_GET['module'];
            if (isset($_GET['action'])) {
                $t_action = $_GET['action'];
            } else {
                $t_action = 'indexAction';
            }

#            $classFile = FR_BASE_PATH.'/modules/'.$module.'/'.$class.'.php';
            try {
                $result = $instance->$event();
            } catch (Exception $error) {
                die( $error->getMessage() );
            }
        } catch (Exception $error) {
            die($error->getMessage());   
        }
    }
}
