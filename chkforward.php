<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) == 'SuperAdmin')
{
  header('Location: /');
  exit;
}
if(!isset($_POST['goto']) || !isset($_POST['actiond']))
{
  header('Location: /');
  exit;
}
if($_POST['actiond'] != 'save' && $_POST['actiond'] != 'remove' && $_POST['actiond'] != 'update')
{
  header('Location: /');
  exit;
}

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if($_POST['username'] == '')
{
  $errObj->userid = "Please Enter Username";
  $obj->response = false;
}
elseif($_POST['username'] != $_SESSION['userName'])
{
  $errObj->userid = "Your user is not matching";
  $obj->response = false;
}

if(trim($_POST['goto']) == '') {
    $errObj->goto = "Please enter goto address";
    $obj->response = false;
}

if($obj->response == false) {
   $obj->errors = $errObj;
   header("content-type: application/json");
   echo json_encode($obj);
   exit;
 }

$result = addgoto($_SESSION['userName'],$_POST['goto'],$_POST['actiond'],$_POST['isa'],decoded($_SESSION['domain']),$_POST['you']);

  if($result != 'true')
  {
    $errObj->goto = $result;
    $obj->response = false;
  }

 if($obj->response == false) {
   $obj->errors = $errObj;
   header("content-type: application/json");
   echo json_encode($obj);
   exit;
 }

  $obj->page = "/forwarding";
  header("content-type: application/json");
  echo json_encode($obj);

?>
