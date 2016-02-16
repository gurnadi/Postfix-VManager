<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) == 'user' && !isset($_POST['userID']) || empty($_POST['userID']))
{
  header('Location: /');
  exit;
}
else
{
  $bind = array(
    ":username" => decoded($_POST['userID'])
  );
  $db->delete($TBL_mailbox, "username = :username AND admin != 'SuperAdmin' AND admin != 'DomainAdmin'", $bind);
}

?>
