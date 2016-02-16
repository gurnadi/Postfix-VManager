<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin' && !isset($_POST['domainID']) || empty($_POST['domainID']))
{
  header('Location: /');
  exit;
}
else
{
  $bind = array(
    ":domain" => decoded($_POST['domainID'])
  );
  $db->delete($TBL_backupmx, "domain = :domain", $bind);
  $db->delete($TBL_transport, "domain = :domain", $bind);
}

?>
