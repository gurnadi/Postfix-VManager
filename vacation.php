<?php
if(ereg("vacation.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../");
   exit;
}
if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin')
{
   header ("Location: ../");
   exit;
}

$result = $db->select($TBL_transport, "destination = 'vacation:'");

if($result)
{
  $id = $result[0]['id'];
  $domain = $result[0]['domain'];
}
else
{
  $id = '';
  $domain = '';
}
?>

<div class="container">

  <ul class="breadcrumb">
  <li><a href="../"> Home</a><span class="divider">/</span></li>
  <li><a href="/edituser?user=<?php echo encoded($_SESSION['userName']);?>">Settings</a></li>
  </ul>

<div id="preview">
<form action="chkvacation.php" onsubmit="submitData(this); return false;" class="form-horizontal">
<input type="hidden" name="actiond" id="actiond">
<?php 
if($result)
{
 echo '<input type="hidden" name="dmID" id="dmID" value="'.encoded($result[0]['id']).'">';
 echo '<input type="hidden" name="udp" id="udp" value="'.$result[0]['id'].'">';
}
?>

  <fieldset>
          <legend><strong>Vacation Domain</strong></legend>
    <div class="control-group">
      <label for="password" class="control-label">Domain Name:</label>
      <div class="controls">
      <input type="text" id="domain" name="domain" class="input-xlarge" value="<?php echo $domain;?>" style="width: 242px;">
      </div>
     <p style="margin-left: 180px; margin-top: 12px; margin-bottom: -2px;">Enter (FQDN) domain name for vacations, the domain should be resolve able.</p>
    </div>

    <div class="control-group">
      <!-- Button -->
      <div class="controls">
      <?php if($result) echo '<button type="submit" class="btn btn-primary input-medium update">Update Domain</button>&nbsp;';
        else echo '<button type="submit" class="btn btn-primary input-medium save">Create Domain</button>&nbsp;';?>
      <?php if($result) echo '<button type="submit" class="btn btn-danger input-medium remove">Remove Domain</button>'; ?>
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
