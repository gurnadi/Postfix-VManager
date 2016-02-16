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
  $db->delete($TBL_domain, "domain = :domain", $bind);
  $db->delete($TBL_mailbox, "domain = :domain AND admin != 'SuperAdmin'", $bind);
  $db->delete($TBL_forwarders, "domain = :domain", $bind);
  $db->delete($TBL_groups, "domain = :domain", $bind);
  $db->delete($TBL_vacation, "domain = :domain", $bind);
}

?>
