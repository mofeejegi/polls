<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 22/03/2018
 * Time: 5:34 PM
 */

// A class to help work with sessions

// It is generally inadvisable to store DB-related objects in sessions

class Session {

    private $logged_in=false;
    public $user_id;
    public $message;
    public $errors;

    function __construct() {
        session_name("POLLING_APP_t1");
        session_start();
        $this->check_message();
        $this->check_errors();
        $this->check_login();
    }

    private function check_login(){
        if (isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
            $this->logged_in = true;
        } else {
            unset($this->user_id);
            $this->logged_in = false;
        }
    }

    private function check_message(){
        // is there a message stored in the session?
        if (isset($_SESSION['message'])) {
            //set the attr value and clear the session
            $this->message = $_SESSION['message'];
            unset($_SESSION['message']);
        } else {
            $this->message = "";
        }
    }

    private function check_errors(){
        // is there any error stored in the session?
        if (isset($_SESSION['errors'])) {
            //set the attr value and clear the session
            $this->errors = $_SESSION['errors'];
            unset($_SESSION['errors']);
        } else {
            $this->errors = array();
        }
    }

    public function is_logged_in() {
        return $this->logged_in;
    }

    public function login(User $user) {
        // database should find user based on username/password
        if ($user) {
            $this->user_id = $_SESSION['user_id'] = $user->id;
            $this->logged_in = true;
        }
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($this->user_id);
        $this->logged_in = false;
    }

    /**
     * Sets or Gets message
     * @param string $msg
     */
    public function message($msg=""){
        if (!empty($msg)) {
            //set message
            $_SESSION['message'] = $msg;
        } else {
            //get message
            return $this->message;
        }
    }

    public function errors($err=array()){
        if (!empty($err)) {
            //set errors
            $_SESSION['errors'] = $err;
        } else {
            //get message
            return $this->errors;
        }
    }

}

$session = new Session();
$message = $session->message();
$errors = $session->errors();