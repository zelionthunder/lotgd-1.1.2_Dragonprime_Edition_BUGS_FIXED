<?php
// addnews ready
// translator ready
// mail ready
require_once("lib/errorhandling.php");
require_once("lib/datacache.php");

/* * * *
 * Avaiable values for DBTYPE:
 *
 * - mysql:				The default value. Are you unsure take this. (DEPRECATED - removed in PHP 7)
 * - mysqli_oos:		The MySQLi extension of PHP5, object oriented style
 * - mysqli_proc:		The MySQLi extension of PHP8+, procedural style (RECOMMENDED)
 *
 */
define('DBTYPE',"mysqli_proc");

$dbinfo = array();
$dbinfo['queriesthishit']=0;

switch(DBTYPE) {
	case 'mysql':
		// PHP 8 INCOMPATIBLE - mysql extension was removed in PHP 7.0
		// For backward compatibility, fall through to mysqli_proc
		require('lib/dbwrapper_mysqli_proc.php');
		break;
	case 'mysqli_oos':
		require('lib/dbwrapper_mysqli_oos.php');
		break;
	case 'mysqli_proc':
		require('lib/dbwrapper_mysqli_proc.php');
		break;
	default:
		// Default to MySQLi procedural for PHP 8
		require('lib/dbwrapper_mysqli_proc.php');
		break;
}

?>
