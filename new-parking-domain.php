<?php

if (ereg ("new-parking-domain.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../");
   exit;
}
if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin')
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['REQUEST_URI'],'edit-parking-domain') == true && (!isset($_GET['domain']) || empty($_GET['domain'])))
{
  header ("Location: /");
  exit;
}
if(!empty($_GET['domain']))
{
  $bind = array (
    ":domain" => decoded($_GET['domain'])
  );

  $domains = $db->select($TBL_backupmx, "domain = :domain", $bind);

  if($domains)
  {
    $domain = $domains[0]['domain'];
    $primary_mx = $domains[0]['smtp_host'];
    $desc = $domains[0]['description'];
    $active = $domains[0]['active'];
  }
  else
  {
    header('Location: /');
    exit;
  }
}
else
{
  $domain = '';
  $primary_mx = '';
  $desc = '';
  $active = 1;
}
?>

<div class="container">

<ul class="breadcrumb">
<li><a href="../">Home</a><span class="divider">/</span></li>
<li><a href="/parking-domains">List Parking Domains</a></li>
</ul>

<form autocomplete="off" class="form-horizontal" action='chk-new-parking-domain.php' onsubmit="submitData(this); return false;">
<?php if(isset($_GET['domain'])) echo '<input type="hidden" name="udp" id="udp" value="'.encoded($domain).'">';?>
        <fieldset>
          <legend><strong>Add a new Parking Domain</strong></legend>
          <div class="control-group">
            <label for="input01" class="control-label">Domain Name</label>
            <div class="controls">
	      <?php 
		 if(!empty($domain))
		 {
                   echo '<input type="hidden" id="domain" name="domain" value="'.$domain.'">';
	           echo '<input type="text" value="'.$domain.'" disabled class="input-xlarge">';
		 }
		 else echo '<input type="text" id="domain" name="domain" class="input-xlarge">';
		?>
            </div>
          </div>
          <div class="control-group" id="mx_ent">
            <label for="input01" class="control-label">Primary MX Host</label>
            <div class="controls">
	      <div class="input-prepend">
	      <span class="add-on">SMTP</span>
              <input type="text" id="mx" name="mx" value="<?php echo $primary_mx; ?>" class="input-xlarge" style="width: 220px;">
	      </div>
		<p style="margin-top: 11px; margin-bottom: -5px;">e.g. mx1.yourdomain.com</p>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Description</label>
            <div class="controls">
              <input type="text" id="desc" name="desc" value="<?php echo $desc; ?>" class="input-xlarge">
            </div>
          </div>

<?php
if(!empty($domain))
{
?>
	  <div class="control-group" id="iactive">
            <label for="optionsCheckbox" class="control-label">Domain is Acitve</label>
            <div class="controls">
                <input type="checkbox" title="If you want to disable this domain then uncheck this" name="active" id="active" <?php if($active == 1) echo 'checked';?>>
            </div>
          </div>

<div style="display: none;" id="adminusr"><?php require_once('domain_template.php');?></div>

	  <div class="control-group" style="margin-top: -5px;">
            <label for="optionsCheckbox" class="control-label" title="Remove Backup MX and convert into Real domain">Convert into real Domain</label>
            <div class="controls">
                <input type="checkbox" name="real" id="real">
            </div>
          </div>
<?php } ?>
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

$('#real').click(function() {
    if( $(this).is(':checked')) {
        $("#adminusr").show();
        $("#iactive").hide();
        $("#mx_ent").hide();
    } else {
        $("#adminusr").hide();
        $('#adminusr input').val('');
        $("#iactive").show();
        $("#mx_ent").show();
    }
}); 

</script>

<?php require_once('js/form.js');?>
