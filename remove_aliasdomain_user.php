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
    ":address" => decoded($_POST['userID'])
  );
  $db->delete($TBL_alias_domain, "address = :address AND isDefault != 'Y'", $bind);
}

?>
