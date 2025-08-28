
<?php

class Home extends Controller {

    public function index() {
      // if logged in, go to lifenav
      if (isset($_SESSION['auth'])) {
        header('Location: /lifenav');
        exit;
      }

      // if not logged in, go to login page
      header('Location: /login');
      exit;
    }
}
