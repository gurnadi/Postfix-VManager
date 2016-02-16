<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) == 'SuperAdmin' || $CONF['vacation'] != 'YES')
{
  header('Location: /');
  exit;
}
if(!isset($_POST['message']) || !isset($_POST['actiond']))
{
  header('Location: /');
  exit;
}
if($_POST['actiond'] != 'save' && $_POST['actiond'] != 'remove' && $_POST['actiond'] != 'update')
{
  header('Location: /');
  exit;
}
else
{
  $vchk = $db->select($TBL_transport, "destination = 'vacation:'");
  if(empty($vchk))
  {
    header('Location: /');
    exit;
  }
  else
   $vacation_domain = $vchk[0]['domain'];
}

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if($_POST['username'] != $_SESSION['userName'])
{
  $errObj->userid = "Your user is not matching";
  $obj->response = false;
}
elseif($_POST['actiond'] != 'remove')
{
  if($_POST['subject'] == '')
  {
    $errObj->subject = "Please vacation subject";
    $obj->response = false;
  }
  if(trim($_POST['message']) == '')
  {
    $errObj->message = "Please vacation message";
    $obj->response = false;
  }
  else
  {
    $goto = preg_replace('/@/', '#', $_SESSION['userName']);
    $goto .= '@'.$vacation_domain;
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
  $db->delete($TBL_forwarders, "address='".$_SESSION['userName']."' AND active=1");
  $db->delete($TBL_vacation, "email='".$_SESSION['userName']."'");
  $db->delete($TBL_vacation_notification, "on_vacation='".$_SESSION['userName']."'");
  $mbox = array("autoresponse" => 'N');
  $db->update($TBL_mailbox, $mbox, "username='".$_SESSION['userName']."'");

  if($_POST['isa'] == 'Y')
  {
    $isalias = array("active" => 1);
    $db->update($TBL_forwarders, $isalias, "address='".$_SESSION['userName']."'");
  }
}
else
{
  $bind = array (
    ":username" => $_SESSION['userName']
  );

  $alias = array(
   "address" => $_SESSION['userName'],
   "goto" => $goto,
   "domain" => decoded($_SESSION['domain']),
   "created" => date('Y-m-d H:i:s'),
   "modified" => date('Y-m-d H:i:s'),
   "active" => 1
  );

  $vacation = array(
   "email" => $_SESSION['userName'],
   "subject" => $_POST['subject'],
   "body" => $_POST['message'],
   "domain" => decoded($_SESSION['domain']),
   "created" => date('Y-m-d H:i:s'),
   "active" => 1
  );

  if($_POST['actiond'] == 'save')
  {
    if($_POST['isa'] == 'Y')
    {
      $isalias = array("active" => 0);
      $db->update($TBL_forwarders, $isalias, "address = :username", $bind);
    }
    $db->insert($TBL_forwarders, $alias);
    $db->insert($TBL_vacation, $vacation);
    $mbox = array("autoresponse" => 'Y');
    $db->update($TBL_mailbox, $mbox, "username='".$_SESSION['userName']."'");
  }
  else
  {
    unset($alias['email']);
    $db->update($TBL_vacation, $vacation, "email = :username", $bind);
  }
}
  $obj->page = "/autoresponse";
  header("content-type: application/json");
  echo json_encode($obj);
?>
