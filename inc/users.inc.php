<?php

namespace Users;

require_once(BASE_PATH . 'inc/database.inc.php');

define('SALT', 'Salt + Pepper');

function hash($username, $password) {
	return sha1($username . SALT . $password);
}

function validateLoginDetails($username, $password) {
	return ($db_conn->query("SELECT * FROM `users` WHERE `username` = %s AND `password` = %s", 
				$username, 
				hash($username, $password))->rowCount() 
				== 1);
}

function createAccount($username, $password, $email, &$error) {
	if (sizeof($username) < 6) {
		$error = 'Username too short!';
		
		return false;
	}
	
	if (sizeof($password) < 8) {
		$error = 'Password too short!';
		
		return false;
	}
	
	if (/* Validate E-Mail */ false) {
		$error = 'Invalid e-mail address!';
		
		return false;
	}
	
	if ($db_conn->query("SELECT * FROM `users` WHERE `username` = %s", $username)->rowCount()) {
		$error = 'Username already in use!';
		
		return false;
	}
	
	if ($db_conn->query("SELECT * FROM `users` WHERE `email` = %s", $email)->rowCount()) {
		$error = 'E-mail address already in use!';
		
		return false;
	}
	
	$db_conn->query("INSERT INTO `users` (`username`, `password`, `email`) VALUES (%s, %s, %s)", $username, hash($username, $password), $email);
	
	return true;
}

?>