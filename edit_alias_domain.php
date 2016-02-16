<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin' && !isset($_POST['domainID']) || empty($_POST['domainID']))
{
  header('Location: /');
  exit;
}
else
{

  $domain = decoded($_POST['domainID']);
  $active = ($_POST['active'] == 'active') ? 1 : 0;

  $update = array(
   "modified" => date('Y-m-d H:i:s'),
   "active" => $active
  );

  $bind = array(
    ":domain" => decoded($_POST['domainID'])
  );

  $db->update($TBL_alias_domain, $update, "alias_domain = :domain", $bind);
}

?>
