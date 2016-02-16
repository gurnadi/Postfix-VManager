<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) == 'user' && !isset($_POST['groupID']) || empty($_POST['groupID']))
{
  header('Location: /');
  exit;
}
else
{
  $bind = array(
    ":address" => decoded($_POST['groupID'])
  );
  $db->delete($TBL_groups, "address = :address", $bind);
}

?>
