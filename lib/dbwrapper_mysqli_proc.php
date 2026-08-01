<?php
// PHP 8 Compatible MySQLi Procedural Database Wrapper
// This replaces the deprecated mysql_* functions with MySQLi equivalents

/**
 * Global connection variable for MySQLi
 */
global $mysqli_connection;

/**
 * Connect to MySQL database using MySQLi
 * @param string $host Database host
 * @param string $user Database user
 * @param string $pass Database password
 * @return mysqli|false Connection object or false on failure
 */
function mysqli_proc_connect($host, $user, $pass) {
	global $mysqli_connection;
	
	$mysqli_connection = new mysqli($host, $user, $pass);
	
	if ($mysqli_connection->connect_error) {
		return false;
	}
	
	// Set charset to UTF-8
	$mysqli_connection->set_charset("utf8mb4");
	
	return $mysqli_connection;
}

/**
 * Persistent connection (same as regular for MySQLi)
 */
function mysqli_proc_pconnect($host, $user, $pass) {
	return mysqli_proc_connect($host, $user, $pass);
}

/**
 * Select database
 * @param string $dbname Database name
 * @param mysqli|null $link Connection object
 * @return bool
 */
function mysqli_proc_select_db($dbname, $link = null) {
	global $mysqli_connection;
	
	if ($link === null) {
		$link = $mysqli_connection;
	}
	
	if (!$link instanceof mysqli) {
		return false;
	}
	
	return $link->select_db($dbname);
}

/**
 * Execute a query
 * @param string $sql SQL query
 * @param mysqli|null $link Connection object
 * @return mysqli_result|false
 */
function mysqli_proc_query($sql, $link = null) {
	global $mysqli_connection;
	
	if ($link === null) {
		$link = $mysqli_connection;
	}
	
	if (!$link instanceof mysqli) {
		return false;
	}
	
	return $link->query($sql);
}

/**
 * Get error message
 * @param mysqli|null $link Connection object
 * @return string Error message
 */
function mysqli_proc_error($link = null) {
	global $mysqli_connection;
	
	if ($link === null) {
		$link = $mysqli_connection;
	}
	
	if (!$link instanceof mysqli) {
		return "No connection";
	}
	
	return $link->error;
}

/**
 * Fetch associative array from result
 * @param mysqli_result $result Query result
 * @return array|null Associative array or null
 */
function mysqli_proc_fetch_assoc($result) {
	if (!$result instanceof mysqli_result) {
		return false;
	}
	
	return $result->fetch_assoc();
}

/**
 * Get number of rows in result
 * @param mysqli_result $result Query result
 * @return int Number of rows
 */
function mysqli_proc_num_rows($result) {
	if (!$result instanceof mysqli_result) {
		return 0;
	}
	
	return $result->num_rows;
}

/**
 * Get last inserted ID
 * @param mysqli|null $link Connection object
 * @return int Last insert ID
 */
function mysqli_proc_insert_id($link = null) {
	global $mysqli_connection;
	
	if ($link === null) {
		$link = $mysqli_connection;
	}
	
	if (!$link instanceof mysqli) {
		return 0;
	}
	
	return $link->insert_id;
}

/**
 * Get number of affected rows
 * @param mysqli|null $link Connection object
 * @return int Affected rows count
 */
function mysqli_proc_affected_rows($link = null) {
	global $mysqli_connection;
	
	if ($link === null) {
		$link = $mysqli_connection;
	}
	
	if (!$link instanceof mysqli) {
		return 0;
	}
	
	return $link->affected_rows;
}

/**
 * Free result set
 * @param mysqli_result $result Query result
 * @return bool
 */
function mysqli_proc_free_result($result) {
	if (!$result instanceof mysqli_result) {
		return false;
	}
	
	$result->free();
	return true;
}

/**
 * Get server info
 * @param mysqli|null $link Connection object
 * @return string Server version
 */
function mysqli_proc_get_server_info($link = null) {
	global $mysqli_connection;
	
	if ($link === null) {
		$link = $mysqli_connection;
	}
	
	if (!$link instanceof mysqli) {
		return "";
	}
	
	return $link->server_info;
}

?>