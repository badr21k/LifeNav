<?php

class Login extends Controller {

    public function index() {		
	    $this->view('login/index');
    }
    
    public function verify(){
			$email = $_REQUEST['email'];
			$password = $_REQUEST['password'];
		
			$user = $this->model('User');
			$user->authenticate($email, $password); 
    }

}
