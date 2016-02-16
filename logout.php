<?php
  if(strpos($_SERVER['PHP_SELF'], "logout.php"))
  {
   header ("Location: /");
   exit;
}
session_destroy();
header ("Location: /");
?>
