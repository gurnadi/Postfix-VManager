<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) && (decoded($_SESSION['role']) != 'SuperAdmin' || (decoded($_SESSION['role']) != 'DomainAdmin' || decoded($_SESSION['role']) != 'SubAdmin')))
{
  header('Location: /');
  exit;
}
if(!isset($_POST['from']) || empty($_POST['from']))
{
  header('Location: /');
  exit;
}

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if($_POST['toemail'] == '') {
  $errObj->toemail = "Please Enter receipent email id";
  $obj->response = false;
}
else
{
  if($_POST['toemail'] == 'broadcast')
  {
    if(decoded($_SESSION['role']) == 'SuperAdmin')
    {
      if($_POST['domain'] == 'all-domains')
        $users = $db->select($TBL_mailbox,"admin != 'Superadmin'",'','','username');
      else
        $users = $db->select($TBL_mailbox,"admin != 'Superadmin' AND domain ='".$_POST['domain']."'");
    }
    else
    {
      $users = $db->select($TBL_mailbox,"username != '".$_SESSION['userName']."' AND domain ='".decoded($_SESSION['domain'])."'",'','','username');
    }
    if(!$users)
    {
      $errObj->domain = "Wrong Domain";
      $obj->response = false;
    }
    else
    {
      $emails = array();
      foreach($users as $user)
      {
        $emails[] = $user['username'];
      }
  	$toemail = join(",", $emails);
    }
  }
  else
  {
    $emails = explode(',',$_POST['toemail']);
    $emails = array_unique($emails);

    foreach ($emails as $email)
    {
      if(!validateEmail ($email))
      {
        $errObj->toemail = "Wrong Email ". $email;
        $obj->response = false;
        break;
      }
    }
    $toemail = join(",", $emails);
  }
}
if ($_POST['subject'] == '') {
    $errObj->subject = "Enter subject for this email";
    $obj->response = false;
}
if ($_POST['message'] == '') {
    $errObj->message = "Enter message for this email";
    $obj->response = false;
}
if ($obj->response == false) {
    $obj->errors = $errObj;
    header("content-type: application/json");
    echo json_encode($obj);
    exit;
}

$from = $_POST['from'];
$toemail = $toemail;
$subject = $_POST['subject'];
$message = $_POST['message'];

sendMail($from, $toemail, $subject, nl2br($message));

$obj->page = "/sendemail";
header("content-type: application/json");
echo json_encode($obj);

?>
