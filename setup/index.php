<?php

require_once('../inc/common.php');
require_once('../header.php');

if(isset($_SESSION['role']))
{
  header('Location: /');
  exit;
}

$incpath = dirname(__FILE__);
$dir = explode('setup',$incpath);
$dir = $dir[0];

if(!is_writable($dir)) {
  print "<center><b>Error: Your directory is not writeable please adjust the permissions, with following command e.g</b></center>";
  print "<center>chown -R user:user $dir</center>";
  exit;
}

$tables = $db->query("show tables");
if(!$tables->fetch(PDO::FETCH_NUM))
{
  print "<center><b>Error: Empty Database: Did you restore your tables into database?</b></center>";
  exit;
}

$result = $db->select($TBL_mailbox, "", "", 'LIMIT 1', "username");

if(!empty($result))
{
  if(!file_exists('cfg.mdb'))
  {
    $file = fopen("../cfg.mdb", 'w');
    fclose($file);
  }
  header ("Location: /");
  exit;
}
?>

<div style="color: #000; text-align: left; padding: 5px 0 10px 40px;">
<h2>Postfix vManager Setup</h2>

<p>Running software:
<ul>

<?php
$f_phpversion = function_exists ("phpversion");
$f_get_magic_quotes_gpc = function_exists ("get_magic_quotes_gpc");
$f_mysql_connect = function_exists ("mysql_connect");
$f_mysqli_connect = function_exists ("mysqli_connect");
$f_pg_connect = function_exists ("pg_connect");
$f_session_start = function_exists ("session_start");
$f_preg_match = function_exists ("preg_match");
$f_mb_encode_mimeheader = function_exists ("mb_encode_mimeheader");

$file_config = file_exists (realpath ("../inc/config.inc.php"));

$error = 0;

/* Check for PHP version */
if($f_phpversion == 1)
{
  if(phpversion() < 5) {
    print "<li><b>Error: Depends on: PHP v5</b><br /></li>\n";
    $error += 1;
  }
  if(phpversion() >= 5) {
    $phpversion = 5;
    print "<li>PHP version " . phpversion () . "</li>\n";
  }
}
else
{
  print "<li><b>Unable to check for PHP version. (missing function: phpversion())</b></li>\n";
}

/* Check for Magic Quotes */
if($f_get_magic_quotes_gpc == 1)
{
  if (get_magic_quotes_gpc () == 0)
    print "<li>Magic Quotes: Disabled - OK</li>\n";
  else
    print "<li><b>Warning: Magic Quotes: ON (internal workaround used)</b></li>\n";
}
else
{
  print "<li><b>Unable to check for Magic Quotes. (missing function: get_magic_quotes_gpc())</b></li>\n";
}

/* Check for config.inc.php */
$config_loaded = 0;
if($file_config == 1)
{
  print "<li>Depends on: presence config.inc.php - OK</li>\n";
  require_once('../inc/config.inc.php');
  $config_loaded = 1;
}
else
{
  print "<li><b>Error: Depends on: presence config.inc.php - NOT FOUND</b><br /></li>\n";
  print "Create the file, and edit as appropriate (e.g. select database type etc)<br />";
  print "For example:<br />\n";
  print "<code><pre>cp config.inc.php.dist config.inc.php</pre></code>\n";
  $error =+ 1;
}

/* Check if PDO available or not */
if(!defined('PDO::ATTR_DRIVER_NAME'))
{
  print "<br>";
  print "<li><b>Error: PDO support in PHP is not available</b><br />\n";
  print "You must install PDO library for php<br />\n";
  print "<br>";	
  $error =+ 1;
}

/* Check if mysqli available or not */
if($f_mysqli_connect == 0)
{
  print "<br>";
  print "<li><b>Error: mysqli support in PHP is not available</b><br />\n";
  print "You must install mysqli library for php<br />\n";
  print "<br>";	
  $error =+ 1;
}

/* Session functions */
if($f_session_start == 1)
{
  print "<li>Depends on: session - OK</li>\n";
}
else
{
  print "<br>";
  print "<li><b>Error: session support in PHP is not available</b><br />\n";
  print "You must install session library for php<br />\n";
  print "<br>";	
  $error =+ 1;
}

/* PCRE functions */
if ($f_preg_match == 1)
{
  print "<li>Depends on: pcre - OK</li>\n";
}
else
{
  print "<br>";
  print "<li><b>Error: pcre support in PHP is not available</b><br />\n";
  print "You must install pcre library for php<br />\n";
  print "<br>";	
  $error =+ 1;
}

/* Multibyte functions */
if($f_mb_encode_mimeheader == 1)
{
  print "<li>Depends on: multibyte string - OK</li>\n";
}
else
{
  print "<br>";
  print "<li><b>Error: multibyte support in PHP is not available</b><br />\n";
  print "You must install multibyte library for php<br />\n";
  print "<br>";	
  $error =+ 1;
}

print "</ul>";

if ($error != 0)
{
    print "<p><b>Please fix the errors listed above.</b></p>";
}

else
{
  echo '<br>';
  echo "Every thing is fine ... let's create admin account";
  echo '<br><br>';
?>

<div class="container">

<form autocomplete="off" class="form-horizontal" action='chkadmin.php' onsubmit="submitData(this); return false;">
  <fieldset>
    <legend><strong>Add Super Admin</strong></legend>
        <div class="control-group">
            <label for="input01" class="control-label">Admin Name</label>
            <div class="controls">
              <input type="text" id="name" name="name" value="" class="input-xlarge">
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Admin Userid</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-user"></i></span>
                  <input type="text" id="username" name="username" class="input-xlarge" style="width: 242px;">
                </div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Password</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-key"></i></span>
                <input type="password" id="password" name="password" class="input-xlarge" style="width: 242px;">
                </div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Password (Confirm)</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-key"></i></span>
                <input type="password" id="conpassword" name="conpassword" class="input-xlarge" style="width: 242px;">
                </div>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit" name="img_post" id="img_post">Create Admin</button>
          </div>
        </fieldset>
</form>

</div>

<?php
  require_once('../bottom.php');
}
require_once('../js/form.js');

?>
