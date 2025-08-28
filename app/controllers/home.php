<?php

class Home extends Controller {

    public function index() {
      if (isset($_SESSION['auth'])) {
        header('Location: /lifenav');
        exit;
      }

      $user = $this->model('User');
      $data = $user->test();
			
	    $this->view('home/index');
	    die;
    }

}