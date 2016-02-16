<?php

if(ereg("newgroup.php", $_SERVER['PHP_SELF']))
{
  header ("Location: /");
  exit;
}
if(!isset($_SESSION['role']) && (decoded($_SESSION['role']) != 'SuperAdmin' || decoded($_SESSION['role']) != 'DomainAdmin' || decoded($_SESSION['role']) != 'SubAdmin'))
{
  header ("Location: /");
  exit;
}
if(strpos($_SERVER['REQUEST_URI'],'editgroup') == true && (!isset($_GET['group']) || empty($_GET['group'])))
{
  header ("Location: /");
  exit;
}
if(!empty($_GET['group']))
{
  $thisgroup = array (
    ":address" => decoded($_GET['group'])
  );
  $result = $db->select($TBL_groups, "address = :address", $thisgroup);
  if(empty($result))
  {
    header("Location: /");
    exit;
  }
  else
  {
    $address = $result[0]['address'];
    $goto = $result[0]['goto'];
    $goto = preg_replace('/,/',"\n",$goto);
    $domain = $result[0]['domain'];
    $active = $result[0]['active'];
  }
}
else
{
  $address = '';
  $goto = '';
  $active = '1';
}
?>
<script type="text/javascript">
<?php
  if(decoded($_SESSION['role']) != 'SuperAdmin' && !isset($_GET['group']))
  {
    $dmchk = $db->list_domains("SELECT count(groups.domain) tgroups, domain.domain,domain.groups,domain.ds_groups FROM domain left join groups ON (domain.domain = groups.domain) WHERE domain.ds_groups = 'N' AND domain.active = 1 AND domain.domain ='".decoded($_SESSION['domain'])."' group by domain.domain");
    if(empty($dmchk))
    {
?>
      alert('New Group for this domain is disabled, Please notify your Administrator');
      window.location='/';
<?php
    }
    elseif($dmchk[0]['groups'] != 0 && $dmchk[0]['tgroups'] == $dmchk[0]['groups'])
    {
?>
      alert('Groups limit exceeded, Please notify your Administrator');
      window.location='/';
<?php
    }
  }
?>
</script>


<div class="container">

<ul class="breadcrumb">
<li><a href="../">Home</a><span class="divider">/</span></li>
<li><a href="/groups">List Groups</a></li>
</ul>

<form class="form-horizontal" action='chknewgroup.php' onsubmit="submitData(this); return false;">
        <fieldset>
  <?php
  if(preg_match("/editgroup/i", $_SERVER['REQUEST_URI']))
    echo '<legend><strong>Update Group</strong></legend>';
  else
    echo '<legend><strong>Add New Group</strong></legend>';

  if(!isset($_GET['group']) && decoded($_SESSION['role']) == 'SuperAdmin')
  {
    echo '<div class="control-group" style="margin-bottom: 8px;">';
    echo '<label for="input01" class="control-label">Domain</label>';
    echo '<div class="controls"> <p style="margin-top: 5px;">';

     $domains = $db->list_domains("SELECT count(groups.domain) tgroups, domain.domain,domain.groups,domain.ds_groups FROM domain left join groups ON (domain.domain = groups.domain) WHERE domain.ds_groups = 'N' AND domain.active = 1 group by domain.domain");
      echo '<select id="domain" name="domain" style="width: 283px;">';
        foreach ($domains as $domain)
        {
          if($domain['groups'] == 0 || ($domain['groups'] != 0 && $domain['tgroups'] < $domain['groups']))
            echo '<option value="'.encoded(trim($domain['domain'])).'">'.trim($domain['domain']).'</option>';
        }
      echo '</select>';
      echo '</p></div></div>';
  }
  elseif(!isset($_GET['group']) && decoded($_SESSION['role']) != 'SuperAdmin')
  {
    echo '<br />';
    echo '<input type="hidden" id="domain" name="domain" value="'.$_SESSION['domain'].'">';
  }
  else
  {
    echo '<br />';
    echo '<input type="hidden" name="udp" id="udp" value="'.encoded($address).'">';
    echo '<input type="hidden" id="address" name="address" value="'.$address.'">';
//  echo '<input type="hidden" id="domain" name="domain" value="'.encoded(substr($address, strpos($address,'@')+1)).'">';
    echo '<input type="hidden" id="domain" name="domain" value="'.encoded($address).'">';
  }
?>
          <div class="control-group">
            <label for="input01" class="control-label">Group Name</label>
            <div class="controls">
	    <div>
              <?php if(isset($_GET['group'])) echo '<input type="text" id="groupid" name="groupid" disabled value="'.$address.'" class="input-xlarge">'; else echo '<input type="text" id="address" name="address" value="'.$address.'" class="input-xlarge">';?>
            </div>
                <div class="help-line" id="help" style="display: none;"><ul><li>Enter group name without any domain</li><li>Group should start with a letter</li><li>You may use letters, numbers, underscores, plus sign and one dot (.)</li><li>Group length should be 2 to 32 characters.</li></ul></div>
            </div>
              <p style="margin-left: 180px; margin-top: 12px;" class="help-block">To create a catch-all then just enter <b>catch-all</b> as group name.</p>
          </div>

    <div class="control-group">
      <label for="password" class="control-label">Go To Address:</label>
      <div class="controls">
    <textarea id="goto" name="goto" class="span6" placeholder="Enter destination email address" rows="8"><?php echo trim($goto);?></textarea>
      </div>
     <p style="margin-left: 180px; margin-top: 12px; margin-bottom: -2px;">Enter multiple address in new line</p>
    </div>

	  <div class="control-group">
            <label for="optionsCheckbox" class="control-label">Active</label>
            <div class="controls">
                <input type="checkbox" value="active" id="active" name="active" <?php if($active == 1) echo 'checked';?>>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save Group</button>
            <button class="btn" onclick="goBack('/')" type="button">Cancel</button>
          </div>
        </fieldset>
</form>

</div>

<script type="text/javascript">
 $('#address').focusout(function () {
  $('#help').hide();
  })
 $('#address').keyup(function () {
  $('#help').show();
  })

function goBack() { window.history.back('/') }
</script>


<?php require_once('js/form.js');
