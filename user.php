<?php
if(ereg("user.php", $_SERVER['PHP_SELF'])){
  header ("Location: /");
  exit;
}
?>

<div class="container" style="padding: 20px">
<h4 style="margin-bottom: 20px;">Welcome: <?php echo $_SESSION['userName'];?></h4>
<div style="float: left; width: 200px;"><a href="/forwarding">Forwarding</a></div><div style="float: left;">Add a forwarding address</div>
<div style="clear: both; margin-bottom: -10px">&nbsp;</div>
<div style="float: left; width: 200px;"><a href="/autoresponse">Auto Response</a></div><div>Set an "out of office" message or auto responder for your mail</div>
</div>
