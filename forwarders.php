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

$user_stats = user_property($_SESSION['userName']);

$thisUser = array (
  ":user" => $_SESSION['userName'],
  ":active" => ($user_stats[0]['autoresponse'] == 'Y') ? 0 : 1
);

$result = $db->select($TBL_forwarders, "address = :user AND active = :active", $thisUser);

if($result)
{
  $you = $_SESSION['userName'];
  $goto = $result[0]['goto'];
  $you = preg_match("/$you/",$goto,$matches);
  if($matches)
    $you = $matches[0];
  $goto = preg_replace('/,/',"\n",$goto);
  if($you)
    $goto = preg_replace("/$you/",'',$goto);
}
else
{
  $goto = '';
  $you = '';
}
?>

<div class="container">

  <ul class="breadcrumb">
  <li><a href="../"> Home</a><span class="divider">/</span></li>
  <?php if($CONF['vacation'] == 'YES') echo '<li><a href="/autoresponse">Auto Response</a></li>';?>
  </ul>

<div id="preview">
<form action="chkforward.php" onsubmit="submitData(this); return false;" class="form-horizontal">
<input type="hidden" name="username" id="username" value="<?php echo $_SESSION['userName'];?>">
<input type="hidden" name="isa" id="isa" value="<?php echo $user_stats[0]['autoresponse'];?>">
<input type="hidden" name="actiond" id="actiond">
  <fieldset>
          <legend><strong>Forwarders</strong></legend>
    <div class="control-group" style="margin-bottom: 12px;">
      <label for="username" class="control-label">You:</label>
      <div class="controls" name="userid" id="userid" style="margin-top: 5px;"><?php echo $_SESSION['userName'];?></div>
    </div>

    <div class="control-group">
      <label for="password" class="control-label">Go To Address:</label>
      <div class="controls">
    <textarea id="goto" name="goto" class="span6" placeholder="Enter destination email address" rows="8"><?php echo trim($goto);?></textarea>
      </div>
     <p style="margin-left: 180px; margin-top: 12px; margin-bottom: -2px;">Enter multiple address in new line</p>
    </div>

    <div class="control-group">
      <label for="password" class="control-label"></label>
      <div class="controls">
    <input type="checkbox" value="you" id="you" name="you" <?php if($you) echo 'checked';?> style="margin-top: -2px; margin-right: 5px;">&nbsp;<span>Save copy in local inbox.</span>
      </div>
    </div>
	
    <div class="control-group">
      <!-- Button -->
      <div class="controls">
      <?php if($goto) echo '<button type="submit" class="btn btn-primary input-medium update">Update Forward</button>&nbsp;';
        else echo '<button type="submit" class="btn btn-primary input-medium save">Save Forward</button>&nbsp;';?>
      <?php if($goto) echo '<button type="submit" class="btn btn-danger input-medium remove">Remove Forward</button>'; ?>
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
