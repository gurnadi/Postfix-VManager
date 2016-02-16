<?php
if(ereg("forwarders.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../");
   exit;
}
if(!isset($_SESSION['role']) || decoded($_SESSION['role']) == 'SuperAdmin')
{
   header ("Location: ../");
   exit;
}
?>

<script type="text/javascript">
<?php
  $vchk = $db->select($TBL_transport, "destination = 'vacation:'");
  if(empty($vchk))
  {
  ?>
      alert('Vacation Domain is not set, Please notify your Administrator');
      window.location='/';
  <?php
  }
  ?>
</script>

<?php

$thisUser = array (
  ":user" => $_SESSION['userName']
);
$result = $db->select($TBL_vacation, "email = :user", $thisUser);
if($result)
{
  $isvac = 'Y';
  $subject = $result[0]['subject'];
  $message = $result[0]['body'];
}
else
{
  $isvac = '';
  $subject = $CONF['vacation_subject'];
  $message = $CONF['vacation_message'];
}
$user_stats = user_property($_SESSION['userName']);
?>

<div class="container">

  <ul class="breadcrumb">
  <li><a href="../"> Home</a><span class="divider">/</span></li>
  <li><a href="/forwarding">Forwarding</a></li>
  </ul>

<div id="preview">
<form action="chkauto.php" onsubmit="submitData(this); return false;" class="form-horizontal">
<input type="hidden" name="username" id="username" value="<?php echo $_SESSION['userName'];?>">
<input type="hidden" name="isa" id="isa" value="<?php echo $user_stats[0]['alias'];?>">
<input type="hidden" name="actiond" id="actiond">
  <fieldset>
          <legend><strong>Auto Response</strong></legend>
    <div class="control-group" style="margin-bottom: 12px;">
      <label for="username" class="control-label">Subject:</label>
      <div class="controls"><input type="text" id="subject" name="subject" class="input-xlarge" value="<?php echo $subject;?>"></div>
    </div>

    <div class="control-group">
      <label for="password" class="control-label">Message:</label>
      <div class="controls">
    <textarea id="message" name="message" class="span6" placeholder="Enter vacation message" rows="8"><?php echo $message;?></textarea>
      </div>
    </div>

    <div class="control-group">
      <!-- Button -->
      <div class="controls">
      <?php 
       if($isvac) echo '<button type="submit" class="btn btn-primary input-medium update" style="width: 170px;">Update Autoresposne</button>&nbsp; <button type="submit" class="btn btn-danger input-medium remove" style="width: 170px;">Remove Autoresponse</button>';
        else echo '<button type="submit" class="btn btn-primary input-medium save">Save Autoreponse</button>';
       ?>
      </div>
    </div>
  </fieldset>
</form>
</div>

</div>

<script>
   $('.save').click(function(){
        $('#actiond').val('save');
    });
   $('.update').click(function(){
        $('#actiond').val('update');
    });
    $('.remove').click(function(){
        $('#actiond').val('remove');
    });
</script>

<?php require_once('js/form.js');?>
