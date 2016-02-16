<?php

require_once('../inc/common.php');
//require_once('../inc/class.db.php');
//require_once('../inc/functions.php');

if(isset($_SESSION['role']))
{
  header('Location: /');
  exit;
}
if(!isset($_POST['username']))
{
  header('Location: /');
  exit;
}

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if(trim(strtolower($_POST['username'])) == '')
{
  $errObj->username = "Please enter admin user id";
  $obj->response = false;
}
if(trim($_POST['password']) == '')
{
  $errObj->password = "Please enter password";
  $obj->response = false;
}
elseif(strlen(trim($_POST['password'])) < 4)
{
  $errObj->password = "Password should be > 4 characters";
  $obj->response = false;
}
elseif(trim($_POST['conpassword']) == '')
{
  $errObj->conpassword = "Please enter confirm password";
  $obj->response = false;
}
elseif(trim($_POST['password']) != trim($_POST['conpassword']))
{
  $errObj->conpassword = "Password not matched";
  $obj->response = false;
}

if ($obj->response == false) 
{
  $obj->errors = $errObj;
  header("content-type: application/json");
  echo json_encode($obj);
  exit;
}

$name = (!empty($_POST['name'])) ? trim($_POST['name']) : 'Super Admin';
$username = trim(strtolower($_POST['username']));
$pass2 = trim($_POST['conpassword']);

$newuser = array(
 "username" => $username,
 "password" => encpasswd($pass2),
 "name" => $name,
 "maildir" => '',
 "quota" => '',
 "domain" => '',
 "created" => date('Y-m-d H:i:s'),
 "modified" => date('Y-m-d H:i:s'),
 "admin" => 'SuperAdmin',
 "active" => 1
);

$db->insert($TBL_mailbox, $newuser);

$_SESSION['userName'] = $username;
$_SESSION['role'] = encoded('SuperAdmin');
$_SESSION['name'] = encoded('Super Admin');

$obj->page = "/";
header("content-type: application/json");
echo json_encode($obj);

?>
