<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin')
{
  header('Location: /');
  exit;
}
if(!isset($_POST['domain']) || !isset($_POST['actiond']))
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

if($_POST['domain'] == '')
{
  $errObj->domain = "Please Enter Vacation domain name";
  $obj->response = false;
}

if($_POST['actiond'] != 'save')
{
  if(!isset($_POST['dmID']) || empty($_POST['dmID']) || decoded($_POST['dmID']) != $_POST['udp'])
  {
    $errObj->domain = "Invalid Domain";
    $obj->response = false;
  }
}
if($obj->response == false)
{
 $obj->errors = $errObj;
 header("content-type: application/json");
 echo json_encode($obj);
 exit;
}

if($_POST['actiond'] == 'remove')
{
  $db->delete($TBL_transport, "destination = 'vacation:'");
}
else
{
  $newdomain = array(
   "domain" => escape_string(trim(strtolower($_POST['domain']))),
   "destination" => 'vacation:'
  );

  if($_POST['actiond'] == 'save')
  {
    $db->insert($TBL_transport, $newdomain);
  }
  else
  {
    unset($newdomain['destination']);
    $db->update($TBL_transport, $newdomain, "destination = 'vacation:'");
  }
}
  $obj->page = "/vacation";
  header("content-type: application/json");
  echo json_encode($obj);
?>
