<?php

 require_once('inc/common.php');

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if ($_POST['alias'] == '') {
    $errObj->alias = "Please enter your alias";
    $obj->response = false;
}
if ($_POST['forward'] == '') {
    $errObj->forward = "Please enter forward address";
    $obj->response = false;
}
if ($obj->response == false) {
    $obj->errors = $errObj;
    header("content-type: application/json");
    echo json_encode($obj);
    exit;
}

$username = escape_string(trim($_POST['username']));
$password = escape_string(trim($_POST['password']));

$bind = array (
  ":username" => trim($_POST['username']),
  ":active" => 1
);

$result = $db->select($TBL_mailbox, "username = :username AND active = :active", $bind, "username,password");

if(!empty($result))
{

  $password = encpasswd ($password, $result[0]['password']);

  $bind = array (
    ":username" => trim($_POST['username']),
    ":password" => $password,
    ":active" => 1
  );

  if($result[0]['password'] == $password)
  { 
    $_SESSION['userName'] = $result[0]['username'];
    $obj->page = "/";
    header("content-type: application/json");
    echo json_encode($obj);
    exit;
  }
  else
  {
    $errObj->password = "Username or password wrong";
    $obj->response = false;
    if ($obj->response == false) {
    $obj->errors = $errObj;
    header("content-type: application/json");
    echo json_encode($obj);
    exit;
    }
  }
}
else
{
  $errObj->password = "Username or password wrong";
  $obj->response = false;
  if ($obj->response == false) {
    $obj->errors = $errObj;
    header("content-type: application/json");
    echo json_encode($obj);
    exit;
  }
}                

?>
