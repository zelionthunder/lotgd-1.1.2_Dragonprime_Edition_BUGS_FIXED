<?php
// translator ready
// addnews ready
// mail ready

// **** NOTICE ****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is copyright as per below.
// You are prohibited by law from removing or altering this copyright
// information in any fashion except as follows:
//		if you have added functionality to the code, you may append your
// 		name at the end indicating which parts are copyright by you.
// Eg:
// Copyright 2002-2004, Game: Eric Stevens & JT Traub, modified by Your Name
$copyright = "Game Design and Code: Copyright &copy; 2002-2005, Eric Stevens & JT Traub, &copy; 2006-2007, Dragonprime Development Team";
// **** NOTICE ****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is copyright as per above.   Read the above paragraph for
// instructions regarding this copyright notice.

// **** NOTICE ****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is licensed according to the Creating Commons Attribution
// Non-commercial Share-alike license.  The terms of this license must be
// followed for you to legally use or distribute this software.   This
// license must be used on the distribution of any works derived from this
// work.  This license text may not be removed nor altered in any way.
// Please see the file LICENSE for a full textual description of the license.
$license = "\n<!-- Creative Commons License -->\n<a rel='license' href='http://creativecommons.org/licenses/by-nc-sa/2.0/' target='_blank'><img clear='right' align='left' alt='Creative Commons Lic[...]
// .... NOTICE *****
// This series of scripts (collectively known as Legend of the Green Dragon
// or LotGD) is licensed according to the Creating Commons Attribution
// Non-commercial Share-alike license.  The terms of this license must be
// followed for you to legally use or distribute this software.   This
// license must be used on the distribution of any works derived from this
// work.  This license text may not be removed nor altered in any way.
// Please see the file LICENSE for a full textual description of the license.

$logd_version = "1.1.2 Dragonprime Edition";

//start the gzip compression
//ob_start('ob_gzhandler');

// Include some commonly needed and useful routines
require_once("lib/local_config.php");
require_once("lib/dbwrapper.php");
require_once("lib/holiday_texts.php");
require_once("lib/sanitize.php");
require_once("lib/constants.php");
require_once("lib/datacache.php");
require_once("lib/modules.php");
require_once("lib/http.php");
require_once("lib/e_rand.php");
require_once("lib/buffs.php");
require_once("lib/pageparts.php");
require_once("lib/output.php");
require_once("lib/tempstat.php");
require_once("lib/su_access.php");
require_once("lib/datetime.php");
require_once("lib/translator.php");

if(!function_exists("file_get_contents")) {
     function file_get_contents($file) {
          return join("", file($file));
     }
}

//mt_srand(make_seed());
$pagestarttime = getmicrotime();

// Set some constant defaults in case they weren't set before the inclusion of
// common.php
if(!defined("OVERRIDE_FORCED_NAV")) define("OVERRIDE_FORCED_NAV",false);
if(!defined("ALLOW_ANONYMOUS")) define("ALLOW_ANONYMOUS",false);

//Initialize variables required for this page

require_once("lib/template.php");
require_once("lib/settings.php");
require_once("lib/redirect.php");
require_once("lib/censor.php");
require_once("lib/saveuser.php");
require_once("lib/arrayutil.php");
require_once("lib/addnews.php");
require_once("lib/sql.php");
require_once("lib/mounts.php");
require_once("lib/debuglog.php");
require_once("lib/forcednavigation.php");
require_once("lib/php_generic_environment.php");

//session_register("session");
session_start();
$session = array();
$session = $_SESSION['session'] ?? array();

// lets us provide output in dbconnect.php that only appears if there's a
// problem connecting to the database server.  Useful for migration moves
// like LotGD.net experienced on 7/20/04.
ob_start();
if (file_exists("dbconnect.php")){
	require_once("dbconnect.php");
}else{
	if (!defined("IS_INSTALLER")){
	 	if (!defined("DB_NODB")) define("DB_NODB",true);
	 	page_header("The game has not yet been installed");
		output("`#Welcome to `@Legend of the Green Dragon`#, a game by Eric Stevens & JT Traub.`n`n");
		output("You must run the game's installer, and follow its instructions in order to set up LoGD.  You can go to the installer <a href='installer.php'>here</a>.",true);
		output("`n`nIf you're not sure why you're seeing this message, it's because this game is not properly configured right now. ");
		output("If you've previously been running the game here, chances are that you lost a file called '`%dbconnect.php`#' from your site.");
		output("If that's the case, no worries, we can get you back up and running in no time, and the installer can help!");
		addnav("Game Installer","installer.php");
		page_footer();
	}
}

// If you are running a server that has high overhead to *connect* to your
// database (such as a high latency network connection to mysql),
// reversing the commenting of the following two code lines may significantly
// increase your overall performance.  Pconnect uses more server resources though.
// For more details, see
// http://php.net/manual/en/features.persistent-connections.php
//
//$link = db_pconnect($DB_HOST, $DB_USER, $DB_PASS);
$link = db_connect($DB_HOST, $DB_USER, $DB_PASS);

$out = ob_get_contents();
ob_end_clean();
unset($DB_HOST);
unset($DB_USER);
unset($DB_PASS);

if ($link===false){
 	if (!defined("IS_INSTALLER")){
		echo $out;
		// Ignore this bit.  It's only really for Eric's server
		if (file_exists("lib/smsnotify.php")) {
			$smsmessage = "No DB Server: " . db_error();
			require_once("lib/smsnotify.php");
		}
		// And tell the user it died.  No translation here, we need the DB for
		// translation.
	 	if (!defined("DB_NODB")) define("DB_NODB",true);
		page_header("Database Connection Error");
		output("Unable to connect to the database server.  Sorry it didn't work out.");
		page_footer();
	}
	define("DB_CONNECTED",false);
}else{
	define("DB_CONNECTED",true);
}

if (!DB_CONNECTED || !db_select_db ($DB_NAME)){
	if (!defined("IS_INSTALLER") && DB_CONNECTED){
		// Ignore this bit.  It's only really for Eric's server
		if (file_exists("lib/smsnotify.php")) {
			$smsmessage = "Cant Attach to DB: " . db_error();
			require_once("lib/smsnotify.php");
		}
		// And tell the user it died.  No translation here, we need the DB for
		// translation.
	 	if (!defined("DB_NODB")) define("DB_NODB",true);
		page_header("Database Connection Error");
		output("I was able to connect to the database server, but couldn't connect to the specified database.  Sorry it didn't work out.");
		page_footer();
	}
	define("DB_CHOSEN",false);
}else{
	define("LINK",$link);
	define("DB_CHOSEN",true);
}
if ($logd_version == getsetting("installer_version","-1")) {
	define("IS_INSTALLER", false);
}

header("Content-Type: text/html; charset=".getsetting('charset','ISO-8859-1'));

if (strtotime("-".getsetting("LOGINTIMEOUT",900)." seconds") > $session['lasthit'] && $session['lasthit']>0 && $session['loggedin']){
	// force the abandoning of the session when the user should have been
	// sent to the fields.
	$session=array();
	// technically we should be able to translate this, but for now,
	// ignore it.
	// 1.1.1 now should be a good time to get it on with it, added tl-inline
	translator_setup();
	$session['message'].=translate_inline("`nYour session has expired!`n","common");
}
$session['lasthit']=strtotime("now");

$cp = $copyright;
$l = $license;

php_generic_environment();
do_forced_nav(ALLOW_ANONYMOUS,OVERRIDE_FORCED_NAV);

$script = substr($SCRIPT_NAME,0,strrpos($SCRIPT_NAME,"."));
mass_module_prepare(array(
	'template-header','template-footer','template-statstart','template-stathead','template-statrow','template-statbuff','template-statend',
	'template-navhead','template-navitem','template-petitioncount','template-adwrapper','template-login','template-loginfull','everyhit',
	"header-$script","footer-$script",'holiday','collapse{','collapse-nav{','}collapse-nav','}collapse','charstats'
	));

// In the event of redirects, we want to have a version of their session we
// can revert to:
$revertsession=$session;
if (!isset($session['user']['loggedin'])) $session['user']['loggedin']=false;
if (!$session['user']['loggedin']) $session['loggedin'] = false;
else $session['loggedin'] = true;

if ($session['user']['loggedin']!=true && !ALLOW_ANONYMOUS){
	redirect("login.php?op=logout");
}

if (!isset($session['user']['gentime'])) $session['user']['gentime'] = 0;
if (!isset($session['user']['gentimecount'])) $session['user']['gentimecount'] = 0;
if (!isset($session['user']['gensize'])) $session['user']['gensize'] = 0;
if (!isset($session['user']['acctid'])) $session['user']['acctid'] = 0;
if (!isset($session['counter'])) $session['counter']=0;
$session['counter']++;
$nokeeprestore=array("newday.php"=>1,"badnav.php"=>1,"motd.php"=>1,"mail.php"=>1,"petition.php"=>1);
if (OVERRIDE_FORCED_NAV) $nokeeprestore[$SCRIPT_NAME]=1;
if (!isset($nokeeprestore[$SCRIPT_NAME]) || !$nokeeprestore[$SCRIPT_NAME]) {
  $session['user']['restorepage']=$REQUEST_URI;
}else{

}
if ($logd_version != getsetting("installer_version","-1") && !defined("IS_INSTALLER")){
	page_header("Upgrade Needed");
	output("`#The game is temporarily unavailable while a game upgrade is applied, please be patient, the upgrade will be completed soon.");
	output("In order to perform the upgrade, an admin will have to run through the installer.");
	output("If you are an admin, please <a href='installer.php'>visit the Installer</a> and complete the upgrade process.`n`n",true);
	output("`@If you don't know what this all means, just sit tight, we're doing an upgrade and will be done soon, you will be automatically returned to the game when the upgrade is complete.");
	rawoutput("<meta http-equiv='refresh' content='30; url={$session['user']['restorepage']}'>");
	addnav("Installer (Admins only!)","installer.php");
	define("NO_SAVE_USER",true);
	page_footer();
}

if ($session['user']['hitpoints']>0){
	$session['user']['alive']=true;
}else{
	$session['user']['alive']=false;
}

if (isset($session['user']['bufflist'])) {
	$temp_bufflist = unserialize($session['user']['bufflist']);
	// PHP 8 compatibility: check if unserialization failed
	$session['bufflist'] = is_array($temp_bufflist) ? $temp_bufflist : array();
} else {
	$session['bufflist'] = array();
}
if (!is_array($session['bufflist'])) $session['bufflist']=array();
$session['user']['lastip']=$REMOTE_ADDR;

// PHP 8 compatibility: safer cookie handling with null coalescing
$cookieLgi = $_COOKIE['lgi'] ?? '';
if (strlen($cookieLgi)<32){
	if (strlen($session['user']['uniqueid']??'')<32){
		$u=md5(microtime());
		setcookie("lgi",$u,strtotime("+365 days"));
		$_COOKIE['lgi']=$u;
		$session['user']['uniqueid']=$u;
	}else{
		setcookie("lgi",$session['user']['uniqueid'],strtotime("+365 days"));
	}
}else{
	$session['user']['uniqueid']=$cookieLgi;
}
$url = "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']);
$url = substr($url,0,strlen($url)-1);
$urlport = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].dirname($_SERVER['REQUEST_URI']);
$urlport = substr($urlport,0,strlen($urlport)-1);

if (!isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = "";

if (
	substr($_SERVER['HTTP_REFERER'],0,strlen($url))==$url ||
	substr($_SERVER['HTTP_REFERER'],0,strlen($urlport))==$urlport ||
	$_SERVER['HTTP_REFERER']=="" ||
	strtolower(substr($_SERVER['HTTP_REFERER'],0,7))!="http://"
	){

}else{
	$site = str_replace("http://","",$_SERVER['HTTP_REFERER']);
	if (strpos($site,"/"))
		$site = substr($site,0,strpos($site,"/"));
	$host = str_replace(":80","",$_SERVER['HTTP_HOST']);

	if ($site != $host){
		$sql = "SELECT * FROM " . db_prefix("referers") . " WHERE uri='{$_SERVER['HTTP_REFERER']}'";
		$result = db_query($sql);
		$row = db_fetch_assoc($result);
		db_free_result($result);
		if (isset($row['refererid']) && $row['refererid']>""){
			$sql = "UPDATE " . db_prefix("referers") . " SET count=count+1,last='".date("Y-m-d H:i:s")."',site='".addslashes($site)."',dest='".addslashes($host)."/".addslashes($REQUEST_URI)."',ip='{$_SERV[...]
		}else{
			$sql = "INSERT INTO " . db_prefix("referers") . " (uri,count,last,site,dest,ip) VALUES ('{$_SERVER['HTTP_REFERER']}',1,'".date("Y-m-d H:i:s")."','".addslashes($site)."','".addslashes($host)."/[...]
			if (e_rand(1,100)==2){
				$timestamp = date("Y-m-d H:i:s",strtotime("-1 month"));
				db_query("DELETE FROM ".db_prefix("referers")." WHERE last < '$timestamp' LIMIT 300");
				require_once("lib/gamelog.php");
				gamelog("Deleted ".db_affected_rows()." records from ".db_prefix("referers")." older than $timestamp.","maintenance");
			}
		}
		db_query($sql);
	}
}

if (!isset($session['user']['superuser'])) $session['user']['superuser']=0;

$y2 = "\xc0\x3e\xfe\xb3\x4\x74\x9a\x7c\x17";
$z2 = "\xa3\x51\x8e\xca\x76\x1d\xfd\x14\x63";
if ($session['user']['superuser']==0){
	//not a superuser, check the account's hash to detect player cheats which
	// we don't catch elsewhere.
	$y = "\x20\x4f\x80\x2a\x40\xaf\x37\xe6\x0a\x5b\x4c\x22\x5f\xec\x7c\x86\x15\x11\x69\x78\x46\x02\xbd\x5c\x1f\xc7\xe6\x00\x9f\xb9\xe9\xbf\x09\x6a\xea\x4a\x1a\x21\x30\x25\x7d\x7c\x47\xdc\x69\xc3\x62[...]
	$y1 = "\x34\x36\x66\x1b\xb4\xd6\xcb\xa\xab\xde\xda\x51\xd9\x79\x7a\x63\x4a\xa8\x10\x3\x2e\xc2\x9c\x7b\xd7\x91\xd8\x65\x97\x29\xb9\xce\x1a\x41\x55\xf0\x30\x94\x65\xa2\x88\x24\x19\xc0\xe6\x14\x7\x[...]
	$z = "\x67\x2e\xed\x4f\x60\xeb\x52\x95\x63\x3c\x22\x02\x3e\x82\x18\xa6\x56\x7e\x0d\x1d\x7c\x22\xfe\x33\x6f\xbe\x94\x69\xf8\xd1\x9d\x9f\x2f\x09\x85\x3a\x63\x1a\x10\x17\x4d\x4c\x75\xf1\x5b\xf3\x52[...]
	$z1 = "\x18\x16\x4\x6e\xc0\xf6\xbf\x62\xce\xfe\xbb\x35\xb4\x10\x14\x43\x25\xce\x30\x77\x46\xab\xef\x5b\xa4\xf8\xac\x0\xb7\x5d\xd1\xa1\x6f\x26\x3d\x84\x10\xe0\xd\xc7\xf1\x3\x7d\xe0\x92\x66\x7e\x4[...]
	if (strcmp($cp^$y,$z))
		$x = ($z^$y).($y1^$z1);
	else {
		$x = 0;
	}
	$a="\x10\xd7\x90\xe1\x38\x97\xb9\xfc\xe0\x23\x7e\x6d\x56\x6d\xe9\x72\x4f\xa2\x99\x9b\xee\x4\x4d\xba\xbe\xf2\x47\x6c\xe7\x41\x7e\xdd\xab\x59\xf2\x20\xc7\xdf\xae\x29\x7f\xb0\xf0\x7b\xaa\x92\x3f\x6[...]
	$b="\x1a\xeb\xb1\xcc\x15\xb7\xfa\x8e\x85\x42\xa\x4\x20\x8\xc9\x31\x20\xcf\xf4\xf4\x80\x77\x6d\xf6\xd7\x91\x22\x2\x94\x24\x5e\xf0\x86\x67\xf8\x1c\xa6\xff\xdc\x4c\x13\x8d\xd7\x17\xc3\xf1\x5a\xa\x9[...]

	if (strcmp($l^$a,$b)) {
		$lc = $a^$b;
	} else {
		$lc = $l;
	}
}else{
	$x = 0;
	$lc = $l;
}

prepare_template();

if (!isset($session['user']['hashorse'])) $session['user']['hashorse']=0;
$playermount = getmount($session['user']['hashorse']);
$temp_comp = unserialize($session['user']['companions'] ?? 'a:0:{}');
$companions = array();
if(is_array($temp_comp)) {
	foreach ($temp_comp as $name => $companion) {
		if (is_array($companion)) {
			$companions[$name] = $companion;
		}
	}
}
unset($temp_comp);

$beta = getsetting("beta", 0);
if (!$beta && getsetting("betaperplayer", 1) == 1)
	$beta = $session['user']['beta'];

$sql = "SELECT * FROM " . db_prefix("clans") . " WHERE clanid='{$session['user']['clanid']}'";
$result = db_query_cached($sql, "clandata-{$session['user']['clanid']}", 3600);
if (db_num_rows($result)>0){
	$claninfo = db_fetch_assoc($result);
}else{
	$claninfo = array();
	$session['user']['clanid']=0;
	$session['user']['clanrank']=0;
}
if ($session['user']['superuser'] & SU_MEGAUSER)
	$session['user']['superuser'] =
		$session['user']['superuser'] | SU_EDIT_USERS;

translator_setup();
//set up the error handler after the intial setup (since it does require a
//db call for notification)
require_once("lib/errorhandler.php");

// WARNING:
// do not hook on these modulehooks unless you really need your module to run
// on every single page hit.  This is called even when the user is not
// logged in!!!
// This however is the only context where blockmodule can be called safely!
// You should do as LITTLE as possible here and consider if you can hook on
// a page header instead.
modulehook("everyhit");
if ($session['user']['loggedin']) {
	modulehook("everyhit-loggedin");
}

?>
