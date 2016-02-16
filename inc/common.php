<?php

if (ereg ("common.php", $_SERVER['PHP_SELF']))
{
   header ("Location: /");
   exit;
}

  if(!defined('MANAGE')) {
      session_start();
  }
  define('MANAGE', 1); # checked in included files

  function incorrect_setup() {
        die ("config.inc.php does not exists PLEASE read docs/INSTALL.TXT to install Postfix.Manage.");
	exit();
  }

  $incpath = dirname(__FILE__);

  if(ini_get('register_globals')) {
      die("Please turn off register_globals; edit your php.ini");
  }

  if(!is_file("$incpath/config.inc.php")) {
        incorrect_setup();
  }

  require_once("$incpath/config.inc.php");
  require_once("$incpath/class.db.php");
  require_once("$incpath/functions.php");

?>
