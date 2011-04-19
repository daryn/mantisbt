<?php
namespace MantisBT\Login;
use MantisBT\Auth\Adapter;

class Controller {
    protected $auth= null;
    protected $request = null;

    public function __construct( AdapterInterface $p_auth, Request $p_request ) {
        # @todo needs an interface check.  no interface requirement exists yet
        $this->auth = $p_auth;
        $this->request = $p_request;
    }

    public function preDispatch() {
        if( $t_auth->hasIdentity() ) {
            if( 'logout' != $this->request->actionName ) {
                return $this->helper->redirector('index', 'index');
            }
        } else {
            if( 'logout' == $this->request->actionName ) {
                return $this->helper->redirector( 'index' );
            }
        }
    }

    public function indexAction() {
        $this->view->form = $this->getForm();
    }

    public function processAction() {
        if( !$this->request->isPost() ) {
            # redirect
            return $this->helper->redirector('index');
        }

        # validate form

        # check credentials
        $t_result  = $this->auth->authenticate($adapter);
        if (!$t_result->isValid()) {
            // Invalid credentials
            #$form->setDescription('Invalid credentials provided');
            #$this->view->form = $form;
            #return $this->render('index'); // re-render the login form
            $this->userDb->incrementFailedLoginCount( $t_user_id );
        } else {
            # ok, we're good to login now
            # increment login count
            $this->userDb->incrementLoginCount( $t_user_id );
            $this->userDb->resetFailedLoginCount( $t_user_id );
            $this->userDb->resetLostPasswordInProgressCount( $t_user_id );
        }

        // We're authenticated! Redirect to the home page
        $this->_helper->redirector('index', 'index');
    }

    public function logoutIndex() {
        $t_result = $this->auth->authenticate( $t_adapter );
        switch ($t_result->getCode()) {
 
            case Auth\Result::FAILURE_IDENTITY_NOT_FOUND:
                /** do stuff for nonexistent identity **/
             break;
 
            case Auth\Result::FAILURE_CREDENTIAL_INVALID:
                /** do stuff for invalid credential **/
             break;
 
            case Auth\Result::SUCCESS:
                /** do stuff for successful authentication **/
             break;
 
            default:
                /** do stuff for other failure **/
             break;
        }
    }

    public function logoutAction() {
        $t_auth->clearIdentity();
        return $this->helper->redirector('index');
    }
}
