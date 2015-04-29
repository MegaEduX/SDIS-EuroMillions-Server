<?php

namespace Users;

require_once(BASE_PATH . 'inc/database.inc.php');

define('SALT', 'Salt + Pepper');

function hash($username, $password) {
	return sha1($username . SALT . $password);
}

function getUserIdentifier($username) {
	global $db_conn;
	
	$result = $db_conn->query("SELECT `id` FROM `users` WHERE `username` = %s", $username)->fetchAll();
	
	return $result[0]['id'];
}

function getUsername($userId) {
	global $db_conn;
	
	$result = $db_conn->query("SELECT `username` FROM `users` WHERE `id` = %s", $userId)->fetchAll();
	
	return $result[0]['username'];
}

function getLoggedInUserIdentifier($key) {
	global $db_conn;
	
	$result = $db_conn->query("SELECT `user` FROM `sessions` WHERE `key` = %s", $key);
	
	return $result[0]['user'];
}

function validateLoginDetails($username, $password) {
	global $db_conn;
	
	return ($db_conn->query("SELECT * FROM `users` WHERE `username` = %s AND `password` = %s", 
				$username, 
				hash($username, $password))->rowCount() 
				== 1);
}

function login($username, $password, &$key) {
	global $db_conn;
	
	if (!validateLoginDetails($username, $password))
		return false;
	
	$key = base64_encode(openssl_random_pseudo_bytes(96));
	
	while ($db_conn->query("SELECT `key` FROM `sessions` WHERE `key` = %s", $key)->rowCount())
		$key = base64_encode(openssl_random_pseudo_bytes(96));
	
	$db_conn->query("INSERT INTO `sessions` (`user`, `key`) VALUES (%s, %s)", getUserIdentifier($username), $key);
	
	return true;
}

function validateSession($key) {
	global $db_conn;
	
	return ($db_conn->query("SELECT `key` FROM `sessions` WHERE `key` = %s", $key)->rowCount() == 1);
}

function createAccount($username, $password, $email, &$error) {
	global $db_conn;
	
	if (strlen($username) < 6) {
		$error = 'Username too short!';
		
		return false;
	}
	
	if (strlen($password) < 8) {
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
