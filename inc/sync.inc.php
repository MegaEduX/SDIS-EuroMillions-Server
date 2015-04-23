<?php

namespace Sync;

define('__ROOT__', dirname(dirname(__FILE__)));

require_once(__ROOT__ . '/inc/database.inc.php');

function store($userId, $blob) {
	$db_conn->query("UPDATE `cloud` SET `data` = %s WHERE `user` = %s", $blob, $userId);
}

function load($userId) {
	//	Not sure if this is working, though.
	
	$result = $db_conn->query("SELECT `data` FROM `cloud` WHERE `user` = %s", $userId)->fetchAll(PDO::FETCH_ASSOC);
	
	return $result[0][0];
}

function delete($userId) {
	$db_conn->query("DELETE `cloud` WHERE `user` = %s", $userId);
}

?>