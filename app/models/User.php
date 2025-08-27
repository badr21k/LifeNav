<?php

class User {

    public $username;
    public $password;
    public $auth = false;

    public function __construct() {
        
    }

    public function test () {
      $db = db_connect();
      $statement = $db->prepare("select * from users;");
      $statement->execute();
      $rows = $statement->fetch(PDO::FETCH_ASSOC);
      return $rows;
    }

    public function authenticate($email, $password) {
        /*
         * if email and password good then
         * $this->auth = true;
         */
		$email = strtolower($email);
		$db = db_connect();
        $statement = $db->prepare("select * from users WHERE email = :email;");
        $statement->bindValue(':email', $email);
        $statement->execute();
        $rows = $statement->fetch(PDO::FETCH_ASSOC);

		if ($rows && password_verify($password, $rows['password_hash'])) {
			$_SESSION['auth'] = [
				'id' => $rows['id'],
				'tenant_id' => $rows['tenant_id'],
				'name' => $rows['name'],
				'email' => $rows['email'],
				'role' => $rows['role']
			];
			unset($_SESSION['failedAuth']);
			header('Location: /home');
			die;
		} else {
			if(isset($_SESSION['failedAuth'])) {
				$_SESSION['failedAuth'] ++; //increment
			} else {
				$_SESSION['failedAuth'] = 1;
			}
			header('Location: /login');
			die;
		}
    }

}