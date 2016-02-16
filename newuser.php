<?php

if(ereg("newuser.php", $_SERVER['PHP_SELF']))
{
  header ("Location: /");
  exit;
}
if(!isset($_SESSION['role']))
{
  header ("Location: /");
  exit;
}
if(preg_match("/edituser/i", $_SERVER['REQUEST_URI']) && (!isset($_GET['user']) || empty($_GET['user'])))
{
  header ("Location: /");
  exit;
}
if(!empty($_GET['user']))
{
  $thisUser = array (
    ":user" => decoded($_GET['user'])
  );
  $result = $db->select($TBL_mailbox, "username = :user", $thisUser);
  if(empty($result))
  {
    header("Location: /");
    exit;
  }
  else
  {
    $user_stats = user_property($result[0]['username']);
    $username = $result[0]['username'];
    $name = $result[0]['name'];
    $quota = divide_quota($result[0]['quota']);
    $admin = $result[0]['admin'];
    $active = $result[0]['active'];
  }
}
else
{
  $username = '';
  $name = '';
  $quota = '';
  $admin = '';
  $active = '';
}
?>
<script type="text/javascript">
<?php
  if(decoded($_SESSION['role']) != 'SuperAdmin' && !isset($_GET['user']))
  {
    $dmchk = $db->list_domains("SELECT count(*) users, domain.domain,domain.mailboxes,domain.groups,domain.ds_mboxes FROM domain,mailbox WHERE domain.ds_mboxes = 'N' AND domain.active = 1 AND domain.domain = mailbox.domain AND domain.domain ='".decoded($_SESSION['domain'])."' group by domain.domain");
    if(empty($dmchk))
    {
?>
      alert('New Account for this domain is disabled, Please notify your Administrator');
      window.location='/';
<?php
    }
    elseif($dmchk[0]['mailboxes'] != 0 && $dmchk[0]['users'] == $dmchk[0]['mailboxes'])
    {
?>
      alert('Users limit exceeded, Please notify your Administrator');
      window.location='/';
<?php
    }
  }
?>
</script>

<div class="container">

<?php 
if((isset($user_stats) && $user_stats[0]['admin'] == decoded($_SESSION['role'])) && decoded($_SESSION['role']) != 'SuperAdmin')
{
echo '
  <ul class="breadcrumb">
  <li><a href="../">Home</a><span class="divider">/</span></li>
  <li><a href="/forwarding">Forwarding</a><span class="divider">/</span></li>';
  if($CONF['vacation'] == 'YES') echo '<li><a href="/autoresponse">Auto Response</a></li>';
  echo '</ul>';
}
else
{
echo '
  <ul class="breadcrumb">
  <li><a href="../">Home</a><span class="divider">/</span></li>';
  if(decoded($_SESSION['role']) == 'SuperAdmin') { echo '<li><a href="/vacation">Vacation Domain</a></li>'; }
  echo '</ul>';
}
?>
<form autocomplete="off" class="form-horizontal" action='chknewuser.php' onsubmit="submitData(this); return false;">
  <fieldset>
  <?php
  if(preg_match("/edituser/i", $_SERVER['REQUEST_URI']))
    echo '<legend><strong>Update Settings</strong></legend>';
  else
    echo '<legend><strong>Add New Mailbox</strong></legend>';
  ?>

<?php
  if(!isset($_GET['user']) && decoded($_SESSION['role']) == 'SuperAdmin')
  {
    echo '<div class="control-group" style="margin-bottom: 8px;">';
    echo '<label for="input01" class="control-label">Domain</label>';
    echo '<div class="controls"> <p style="margin-top: 5px;">';

    $domains = $db->list_domains("SELECT count(*) users, domain.domain,domain.mailboxes,domain.groups,domain.maxquota,domain.ds_mboxes FROM domain,mailbox WHERE domain.ds_mboxes = 'N' AND domain.active = 1 AND domain.domain = mailbox.domain group by domain.domain");
      echo '<select id="domain" name="domain" style="width: 283px;">';
	foreach ($domains as $domain)
	{
	  if($domain['ds_mboxes'] != 'Y' && $domain['mailboxes'] == 0 || ($domain['mailboxes'] != 0 && $domain['users'] < $domain['mailboxes']))
	    echo '<option value="'.encoded($domain['domain']).'">'.$domain['domain'].'</option>';
        }
      echo '</select>';
      echo '</p></div></div>';
  }
  elseif(!isset($_GET['user']) && decoded($_SESSION['role']) != 'SuperAdmin')
  {
    echo '<br />';
    echo '<input type="hidden" id="domain" name="domain" value="'.$_SESSION['domain'].'">';
  }
  else
  {
    echo '<br />';
    echo '<input type="hidden" name="udp" id="udp" value="'.encoded($username).'">';
    echo '<input type="hidden" id="username" name="username" value="'.$username.'">';
    echo '<input type="hidden" id="domain" name="domain" value="'.encoded(substr($username, strpos($username,'@')+1)).'">';
  }
?>  
        <div class="control-group">
            <label for="input01" class="control-label">Full Name</label>
            <div class="controls">
              <input type="text" id="name" name="name" value="<?php echo $name;?>" class="input-xlarge">
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Username</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-user"></i></span>
		 <?php if(isset($_GET['user'])) echo '<input type="text" id="userid" name="userid" disabled value="'.$username.'"  class="input-xlarge" style="width: 242px;">'; else echo '<input type="text" id="username" name="username" class="input-xlarge" style="width: 242px;">';?>
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
		<div class="help-line" id="adminpass" style="display: none;"><ul><li>Password should be > 4 characters</li><li>Use both letters and numbers</li><li>Add special characters (such as @, ?, %)</li><li>Mix capital and lowercase letters</li></ul></div>
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
if((isset($_GET['user']) && $user_stats[0]['admin'] != 'SuperAdmin' && decoded($_SESSION['role']) != 'User') || !isset($_GET['user']))
{
echo '

          <div class="control-group">
            <label for="input01" class="control-label">Mail Quota</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on">MBs</span>
                <input type="text" id="quota" name="quota" value="'.$quota.'" class="input-xlarge" style="width: 230px;">
                </div>
		<div class="help-line" id="usrquota" style="display: none;"><ul><li>Leave Blank for default quota</li><li>Please enter quota in MBs</li></ul></div>
            </div>
          </div>';
}
if(isset($_GET['user']) && $user_stats[0]['admin'] != decoded($_SESSION['role']))
{
?>
	  <div class="acontrol-group" style="margin-top: -5px;">
            <label for="optionsCheckbox" class="control-label">Active</label>
            <div class="controls">
                <input type="checkbox" value="active" id="active" name="active" <?php if($active == 1) echo 'checked';?>>
            </div>
          </div>
<?php
}
if(isset($_GET['user']) && $user_stats[0]['admin'] != decoded($_SESSION['role']) && $user_stats[0]['admin'] != 'DomainAdmin' || !isset($_GET['user']))
{
?>
	  <div class="acontrol-group" style="clear: both;">
            <label for="optionsCheckbox" class="control-label">Domain Admin</label>
            <div class="controls">
                <input type="checkbox" value="admins" id="dm" name="dm" <?php if($admin == 'SubAdmin') echo 'checked';?>>
            </div>
          </div>
<?php

 if($_SESSION['role'] != 'User') {

  if(isset($_GET['user']))
  {
	$thisUser = array (
	  ":user" => decoded($_GET['user']),
	  ":active" => ($user_stats[0]['autoresponse'] == 'Y') ? 0 : 1
	);

	$go_result = $db->select($TBL_forwarders, "address = :user AND active = :active", $thisUser);

	if($go_result)
	{
	  $you = decoded($_GET['user']);
	  $goto = $go_result[0]['goto'];
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

        echo '<input type="hidden" id="oldgoto" name="oldgoto" value="'.$goto.'">';
        echo '<input type="hidden" id="user_domain" name="user_domain" value="'.$user_stats[0]['domain'].'">';
        echo '<input type="hidden" id="isa" name="isa" value="'.$user_stats[0]['autoresponse'].'">';
        echo '<input type="hidden" id="oldyou" name="oldyou" value="'.$you.'">';

?>
   <div class="acontrol-group" style="clear: both;">
      <label for="password" class="control-label">Forwarders</label>
      <div class="controls" style="margin-top: 14px;">
    <textarea id="goto" name="goto" class="span6" placeholder="Enter destination email address" rows="5" style="width: 270px;"><?php echo trim($goto);?></textarea>
      </div>
     <p style="margin-left: 180px; margin-top: 12px; margin-bottom: 5px;">Enter multiple address in new line</p>
    </div>

    <div class="control-group" style="clear: both;">
      <label for="password" class="control-label"></label>
      <div class="controls"><input type="checkbox" value="you" id="you" name="you" <?php if($you) echo 'checked';?> style="margin-top: -2px; margin-right: 5px;">&nbsp;<span>Save copy in local inbox.</span>
      </div>
    </div>
<?php
   }
 }
}
if(!isset($_GET['user']))
{
echo '
	  <div class="acontrol-group" style="clear: both;">
            <label for="optionsCheckbox" class="control-label">Send Welcome Email</label>
            <div class="controls">
                <input type="checkbox" value="sendwelcome" id="sendwelcome" name="sendwelcome" checked>
            </div>
          </div>';
}

?>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit" name="img_post" id="img_post">Save User</button>
            <button class="btn" onclick="goBack('/')" type="button">Cancel</button>
          </div>
        </fieldset>
</form>

</div>

<script type="text/javascript">
 $('#username').focusout(function () {
  $('#help').hide();
  })
 $('#username').keyup(function () {
  $('#help').show();
  })
 $('#password').focusout(function () {
  $('#adminpass').hide();
  })
 $('#password').keyup(function () {
  $('#adminpass').show();
  })
 $('#quota').focusout(function () {
  $('#usrquota').hide();
  })
 $('#quota').keyup(function () {
  $('#usrquota').show();
  })

function goBack() { window.history.back('/') }
</script>

<?php require_once('js/form.js');?>
