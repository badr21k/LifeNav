<?php

class Overview extends Controller {

    public function index() {
        // If you need auth checks, they likely happen in base Controller or middleware
        $this->view('overview/index');
        die;
    }
}
