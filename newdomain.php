<?php

if (ereg ("newdomain.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../");
   exit;
}
if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin')
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['REQUEST_URI'],'editdomain') == true && (!isset($_GET['domain']) || empty($_GET['domain'])))
{
  header ("Location: /");
  exit;
}

if(!empty($_GET['domain']))
{
  $bind = array (
    ":domain" => decoded($_GET['domain'])
  );

  $domain = $db->select($TBL_domain, "domain = :domain", $bind, '');

  if($domain)
  {
    $domainname = $domain[0]['domain'];
    $desc = $domain[0]['description'];
    $mailboxes = $domain[0]['mailboxes'];
    $groups = $domain[0]['groups'];
    $maxquota = $domain[0]['maxquota'];
    $active = $domain[0]['active'];
    $ds_mboxes = $domain[0]['ds_mboxes'];
    $ds_groups = $domain[0]['ds_groups'];
  }
  else
  {
    header('Location: /');
    exit;
  }
}
else
{
  $domainname = '';
  $desc = '';
  $domain_admin = '';
  $password = '';
  $mailboxes = '';
  $groups = '';
  $maxquota = '';
  $active = 1;
  $ds_mboxes = 'N';
  $ds_groups = 'N';
}
?>

<div class="container">

<ul class="breadcrumb">
<li><a href="../">Home</a><span class="divider">/</span></li>
<li><a href="../">List Domains</a></li>
</ul>

<form autocomplete="off" class="form-horizontal" action='chknewdomain.php' onsubmit="submitData(this); return false;">
<?php if(isset($_GET['domain'])) echo '<input type="hidden" name="udp" id="udp" value="'.encoded($domainname).'">';?>
        <fieldset>
          <legend><strong>Add a new Domain</strong></legend>
          <div class="control-group">
            <label for="input01" class="control-label">Domain Name</label>
            <div class="controls">
	      <?php 
		 if(!empty($domain))
		 {
                   echo '<input type="hidden" id="domainname" name="domainname" value="'.$domainname.'">';
                   echo '<input type="hidden" id="oldquota" name="oldquota" value="'.$maxquota.'">';
	           echo '<input type="text" value="'.$domainname.'" disabled class="input-xlarge">';
		 }
		 else echo '<input type="text" id="domainname" name="domainname" class="input-xlarge">';
		?>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Description</label>
            <div class="controls">
              <input type="text" id="desc" name="desc" value="<?php echo $desc; ?>" class="input-xlarge">
            </div>
          </div>
<?php
if(!isset($_GET['domain']))
{
?>
          <div class="control-group">
            <label for="input01" class="control-label">Domain Admin</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-user"></i></span>
		 <?php
                 if(!empty($domain))
                 {
                  echo '<input type="hidden" id="domain_admin" name="domain_admin" value="'.$domain_admin.'">';
                  echo '<input type="text" id="domain_admin" name="domain_admin" value="'.$domain_admin.'" disabled class="input-xlarge" style="width: 242px;">';
		 }
		 else echo '<input type="text" autocomplete="off" id="domain_admin" name="domain_admin" class="input-xlarge" style="width: 242px;">';
		 ?>
                </div>
                <div class="help-line" id="help" style="display: none;"><ul><li>Enter email address without any domain</li><li>Email should start with a letter</li><li>You may use letters, numbers, underscores, plus sign and one dot (.)</li><li>Email length should be 2 to 32 characters.</li></ul></div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Password</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-key"></i></span>
                <input type="password" id="password" name="password" class="input-xlarge" style="width: 242px;">
                </div>
		<div class="help-line" id="adminpass" style="display: none;"><ul><li>Use both letters and numbers</li><li>Add special characters (such as @, ?, %)</li><li>Mix capital and lowercase letters</li></ul></div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Password (Confirm)</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-key"></i></span>
                <input type="password" id="conpassword" name="conpassword" class="input-xlarge" style="width: 242px;">
                </div>
            </div>
          </div>
<?php
}
?>

          <div class="control-group">
            <label class="control-label">Mailboxes</label>
            <div class="controls">
              <input type="text" id="mailboxes" value="<?php echo $mailboxes; ?>" name="mailboxes" class="input-xlarge">
	     <div class="help-line" id="dmbox" style="display: none;"><ul><li>e.g. 500,1000,10000</li> <li>Leave Blank for unlimited</li></ul></div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Groups</label>
            <div class="controls">
              <input type="text" id="groups" name="groups" value="<?php echo $groups; ?>" name="mailboxes" class="input-xlarge">
	     <div class="help-line" id="dmals" style="display: none;"><ul><li>e.g. 500,1000,10000</li><li>Leave Blank for unlimited</li></ul></div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Max Quota</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on">MBs</span>
                <input type="text" id="maxquota" name="maxquota" value="<?php echo $maxquota;?>" class="input-xlarge" style="width: 230px;">
	     <div class="help-line" id="dmquota" style="display: none;"><ul><li>Leave Blank for unlimited</li><li>Please enter quota in MBs</li></ul></div>
                </div>
            </div>
          </div>
	  <?php
	  if(empty($domain))
          {
          ?>
	  <div class="acontrol-group">
            <label for="optionsCheckbox" class="control-label">Add default Mailbox</label>
            <div class="controls">
                <input type="checkbox" name="defaultmbox" id="defaultmbox" checked>
            </div>
          </div>
	  <?php
	    }
	    else
	    {
	     ?>
	  <div class="acontrol-group">
            <label for="optionsCheckbox" class="control-label">Disable New Mailboxes</label>
            <div class="controls">
                <input title="If you want nobody can create new mailbox then check this" type="checkbox" name="ds_mboxes" id="ds_mboxes" <?php if($ds_mboxes == 'Y') echo 'checked';?>>
            </div>
          </div>
	  <div class="acontrol-group" style="clear: both;">
            <label for="optionsCheckbox" class="control-label">Disable New Groups</label>
            <div class="controls">
                <input type="checkbox" title="If you want nobody can create new group then check this" name="ds_groups" id="ds_groups" <?php if($ds_groups == 'Y') echo 'checked';?>>
            </div>
          </div>
	  <div class="acontrol-group" style="clear: both;">
            <label for="optionsCheckbox" class="control-label">Domain is Acitve</label>
            <div class="controls">
                <input type="checkbox" title="If you want to disable this domain then uncheck this" name="active" id="active" <?php if($active == 1) echo 'checked';?>>
            </div>
          </div>
	  <?php
	   }
	  ?>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit" id="img_post" name="img_post">Save Domain</button>
	    <button class="btn" onclick="goBack('/')" type="button">Cancel</button>
          </div>
        </fieldset>
</form>
</div>

<script type="text/javascript">
 $('#domain_admin').focusout(function () {
  $('#help').hide();
  })
 $('#domain_admin').keyup(function () {
  $('#help').show();
  })
 $('#password').focusout(function () {
  $('#adminpass').hide();
  })
 $('#password').keyup(function () {
  $('#adminpass').show();
  })
 $('#mailboxes').focusout(function () {
  $('#dmbox').hide();
  })
 $('#mailboxes').keyup(function () {
  $('#dmbox').show();
  })
 $('#groups').focusout(function () {
  $('#dmals').hide();
  })
 $('#groups').keyup(function () {
  $('#dmals').show();
  })
 $('#maxquota').focusout(function () {
  $('#dmquota').hide();
  })
 $('#maxquota').keyup(function () {
  $('#dmquota').show();
  })

function goBack() { window.history.back('/') }

</script>

<?php require_once('js/form.js');?>
