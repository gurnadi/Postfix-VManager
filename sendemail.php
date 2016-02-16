<?php

if(ereg("sendemail.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../");
   exit;
}
if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin' && decoded($_SESSION['role']) != 'DomainAdmin')
{
  header('Location: /');
  exit;
}
?>

<script type="text/javascript">
<?php
  if(decoded($_SESSION['role']) != 'SuperAdmin')
  {
    $bind = array (
      ":domain" => decoded($_SESSION['domain'])
    );
    $userchk = $db->select($TBL_mailbox, "domain = :domain", $bind, '', "domain");
    if(empty($userchk))
    {
?>
      alert('There is no user in your domain, you can not send emails');
      window.location='/';
<?php
    }
  }
?>
</script>


<div class="container">

<ul class="breadcrumb">
<?php
if(!isset($_GET['broadcast']))
  echo '<li><a href="../">Home</a><span class="divider">/</span></li><li><a href="/sendemail?broadcast=y">Broadcast Message</a><span class="divider"><div class="icon-hand-right"></div></span></li><li class="active">Send email to all users</li>';
else
  echo '<li><a href="../">Home</a><span class="divider">/</span></li><li><a href="/sendemail">Send Email</a><span class="divider"><div class="icon-hand-right"></div></span></li><li class="active">Send email to single user</li>';
?>
</ul>

<div id="preview">
<form action="chksendemail.php" onsubmit="submitData(this); return false;" class="form-horizontal">
  <fieldset>
          <legend>Send an Email</legend>
    <div class="control-group" style="margin-bottom: 12px;">
      <label for="username" class="control-label">From:</label>
      <div class="controls" style="margin-top: 7px;"><?php if(decoded($_SESSION['role']) == 'SuperAdmin') echo $CONF['sendEmail_From']; else echo $_SESSION['userName'];?></div>
      <input type="hidden" id="from" name="from" value="<?php if(decoded($_SESSION['role']) == 'SuperAdmin') echo $CONF['sendEmail_From']; else echo $_SESSION['userName'];?>">
    </div>

    <?php 
    if(isset($_GET['broadcast']))
    {
      if(decoded($_SESSION['role']) == 'SuperAdmin')
      {
        echo '<form method="post" style="margin: 0px;">';
        $domains = $db->list_domains("SELECT domain FROM domain where active=1");
	echo '<div class="control-group">';
	echo '<label for="domain" class="control-label">Domain:</label>';
	echo '<div class="controls">';
        echo '<select id="domain" name="domain" style="width: 283px;">';
          echo '<option value="all-domains">All Domains</option>';
          foreach ($domains as $domain)
          {
            echo '<option value="'.$domain['domain'].'">'.$domain['domain'].'</option>';
          }
        echo '</select>';
	echo '</form>';
	echo '</div></div>';
	echo '<input type="hidden" name="toemail" id="toemail" value="broadcast">';
      }
      else
      {
        echo '<input type="hidden" value="'.$_SESSION['domain'].'" name="domain" id="domain">';
	echo '<input type="hidden" name="toemail" id="toemail" value="broadcast">';
      }
    }
    else
    {
      echo '<div class="control-group">';
      echo '<label for="username" class="control-label">To:</label>';
      echo '<div class="controls">';
      echo '<input type="text" class="input-xlarge" placeholder="Recipient Email" name="toemail" id="toemail">';
      echo '</div>';
      echo '<div class="controls" style="margin-top: 15px; margin-bottom: -10px;"><p>Enter multiple email addresses with qoma sperated</p></div>';
      echo '</div>';
    }
    ?>

    <div class="control-group">
      <label for="password" class="control-label">Subject</label>
      <div class="controls">
        <input type="text" class="input-xlarge" placeholder="Subject" name="subject" id="subject">
      </div>
    </div>

    <div class="control-group">
      <label for="password" class="control-label">Message</label>
      <div class="controls">
    <textarea id="message" name="message" class="span6" placeholder="Your Message" rows="8"></textarea>
      </div>
    </div>

    <div class="control-group">
      <!-- Button -->
      <div class="controls">
      <button id="contact-submit" type="submit" class="btn btn-primary input-medium">Send Email</button>
      </div>
    </div>
  </fieldset>
</form>
</div>

</div>

<?php require_once('js/form.js');?>
