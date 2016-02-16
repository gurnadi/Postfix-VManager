<?php

if(ereg("new-alias-domain.php", $_SERVER['PHP_SELF']))
{
   header ("Location: /");
   exit;
}
if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin')
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['REQUEST_URI'],'edit-alias-domain') == true && (!isset($_GET['domainName']) || empty($_GET['domainName'])))
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['REQUEST_URI'],'edit-alias-user') == true && (!isset($_GET['userID']) || empty($_GET['userID'])))
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['REQUEST_URI'],'new-alias-user') == true && (!isset($_GET['newUser']) || empty($_GET['newUser'])))
{
  header('Location: /');
  exit;
}
if(!empty($_GET['domainName']) || !empty($_GET['userID']) || !empty($_GET['newUser']))
{
  if(isset($_GET['domainName']) || isset($_GET['newUser']))
  {
    if(isset($_GET['newUser']))
    {
      $domainID = explode('?',$_GET['newUser']);
      $domainID = $domainID[0];
    }
    else
      $domainID = $_GET['domainName'];

    $bind = array (
      ":domain" => decoded($domainID)
    );
    $domain = $db->select($TBL_alias_domain, "alias_domain = :domain AND isDefault = 'Y'", $bind);
  }
  elseif($_GET['userID'])
  {
    $bind = array (
      ":id" => decoded($_GET['userID'])
    );
    $domain = $db->select($TBL_alias_domain, "id = :id", $bind);
  }
  if($domain)
  {
    $domainname = $domain[0]['alias_domain'];
    $defaultEmail = $domain[0]['address'];
    $defaultDestination = $domain[0]['target_domain'];
    $active = $domain[0]['active'];
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
  $defaultEmail = '';
  $defaultDestination = '';
  $active = '';
}
?>

<div class="container">

<ul class="breadcrumb">
<li><a href="../">Home</a><span class="divider">/</span></li>
<li><a href="/alias-domains">List Alias Domain</a></li>
</ul>

  <?php
  if(strpos($_SERVER['REQUEST_URI'],'edit-alias-domain') == true)
    echo '<legend><strong>Update Alias domain</strong></legend>';
  elseif(strpos($_SERVER['REQUEST_URI'],'new-alias-user') == true)
    echo '<legend><strong>Create new alias address</strong></legend>';
  elseif(strpos($_SERVER['REQUEST_URI'],'edit-alias-user') == true)
    echo '<legend><strong>Update alias address</strong></legend>';
  else
    echo '<legend><strong>Add New Alias Domain</strong></legend>';
  ?>

<form class="form-horizontal" action='chkaliasdomain.php' onsubmit="submitData(this); return false;" style="clear: both;">
<?php 
  if(isset($_GET['domainName']) || isset($_GET['newUser']))
  {
    echo '<input type="hidden" name="nuD" id="nUD" value="'.encoded($domainname).'">';
    echo '<input type="hidden" name="domainname" id="domainname" value="'.$domainname.'">';
  }
  elseif(isset($_GET['userID']))
  {
    echo '<input type="hidden" name="udp" id="udp" value="'.$_GET['userID'].'">';
    echo '<input type="hidden" name="userID" id="userID" value="'.decoded($_GET['userID']).'">';
    echo '<input type="hidden" name="domainname" id="domainname" value="'.$domainname.'">';
  }
?>
        <fieldset>
          <div class="control-group" style="margin-top: 15px;">
            <label for="input01" class="control-label">Domain Name</label>
            <div class="controls">
    		<?php
                 if(!empty($domainname))
                 {
                   echo '<input type="text" value="'.$domainname.'" disabled class="input-xlarge">';
                 }
                 else echo '<input type="text" id="domainname" name="domainname" class="input-xlarge">';
	        ?>
            </div>
          </div>
  
<?php
if(!isset($_GET['domainName']))
{
?>

        <div class="control-group" id="aliasemail">
            <label for="input01" class="control-label">Email Address</label>
            <div class="controls">
		 <div class="input-prepend">
		 <span class="add-on"><i class="icon-envelope"></i></span>
                <input type="text" id="defaultEmail" name="defaultEmail" value="<?php if(!isset($_GET['newUser'])) echo $defaultEmail;?>" class="input-xlarge" style="width: 242px;"><a href="javascript:;" id="emhelp" rel="popover" data-content="Enter <b>email address</b> without any domain or If you want all emails go to destination address, then leave blank <br><br> <b>Note:</b> this is default email alias you can not delete this, but you can change." data-original-title="Email Address"><span class="qs"><i class="icon-question-sign icon-large"></i></span></a>
		</div>
            </div>
          </div>
          <div class="control-group" id="desti">
            <label for="input01" class="control-label">Destination Address</label>
            <div class="controls">
		 <div class="input-prepend">
		 <span class="add-on"><i class="icon-user"></i></span>
                <input type="text" id="defaultDestination" name="defaultDestination" value="<?php if(!isset($_GET['newUser'])) echo $defaultDestination;?>" class="input-xlarge" style="width: 242px;"><a href="javascript:;" id="ehelp" rel="popover" data-content="e.g username@otherdomain.com <br> e.g @otherdomain.com" data-original-title="Destination"><span class="qs"><i class="icon-question-sign icon-large"></i></span></a>
		</div>
            </div>
          </div>
<?php
}

if(!isset($_GET['domainName']) && isset($_GET['userID']))
{
?>
          <div class="control-group" id="iactive">
            <label for="optionsCheckbox" class="control-label">Acitve</label>
            <div class="controls">
                <input type="checkbox" title="If you want to disable this domain then uncheck this" name="active" id="active" <?php if($active == 1) echo 'checked';?>>
            </div>
          </div>
<?php
}
?>

<?php
if(isset($_GET['domainName']))
{
require_once('domain_template.php');
}
?>

        <div class="form-actions" style="clear: both;">
            <button class="btn btn-primary" type="submit" id="img_post"><?php 
	    if(strpos($_SERVER['REQUEST_URI'],'new-alias-user') == true) echo 'Save Alias'; elseif(strpos($_SERVER['REQUEST_URI'],'edit-alias-user') == true) echo 'Update Alias'; else echo 'Save Domain';?></button>
            <button class="btn btn-primary" type="button" id="img_load" style="display: none;"><i class="icon-refresh icon-spin"></i> Please wait...</i></button>
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

$(function ()
{ $("#ehelp").popover({trigger: 'focus'});
});
$(function ()
{ $("#emhelp").popover({trigger: 'focus'});
});

$('#real').click(function() {
    if( $(this).is(':checked')) {
        $("#adminusr").show();
        $("#iactive").hide();
        $("#aliasemail").hide();
        $("#desti").hide();
    } else {
        $("#adminusr").hide();
        $('#adminusr input').val('');
        $("#iactive").show();
        $("#aliasemail").show();
        $("#desti").show();
    }
});

</script>

<?php require_once('js/form.js'); ?>
