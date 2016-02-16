<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin')
{
  header('Location: /');
  exit;
}
if(!isset($_POST['domainname']))
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['HTTP_REFERER'],'edit-alias-domain') == true && (!isset($_POST['nuD']) || decoded($_POST['nuD']) != $_POST['domainname']))
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['HTTP_REFERER'],'edit-alias-user') == true && (!isset($_POST['udp']) || empty($_POST['udp']) || !isset($_POST['userID']) || decoded($_POST['udp']) != $_POST['userID']))
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['HTTP_REFERER'],'new-alias-user') == true && (!isset($_POST['nuD']) || decoded($_POST['nuD']) != $_POST['domainname']))
{
  header('Location: /');
  exit;
}

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if(strpos($_SERVER['HTTP_REFERER'],'new-alias-domain') == true)
{
  if($_POST['domainname'] == '') {
    $errObj->domainname = "Please enter domain name";
    $obj->response = false;
  }
  else
  {
    $bind = array (
      ":domain" => escape_string(trim(strtolower($_POST['domainname'])))
    );

    $result = $db->select($TBL_domain, "domain = :domain", $bind, '', "domain");
    $result2 = $db->select($TBL_backupmx, "domain = :domain", $bind, '', "domain");
    $result3 = $db->select_distinct($TBL_alias_domain, "alias_domain = :domain", $bind, '', "alias_domain");
    if(!empty($result))
    {
      $errObj->domainname = "Domain already exists with the same name";
      $obj->response = false;
    }
    if(!empty($result2))
    {
      $errObj->domainname = "Parking Domain already exists with same name";
      $obj->response = false;
    }
    if(!empty($result3))
    {
      $errObj->domainname = "Alias Domain already exists with same name";
      $obj->response = false;
    }
  }
}

if($obj->response == false) {
  $obj->errors = $errObj;
  header("content-type: application/json");
  echo json_encode($obj);
  exit;
}

$domain = trim(strtolower($_POST['domainname']));
$defaultEmail = (empty($_POST['defaultEmail']) || $_POST['defaultEmail'] == '@') ? '@'.$domain : trim(strtolower($_POST['defaultEmail']));
$defaultEmail = (strpos($defaultEmail,'@') == false && $defaultEmail != '@'.$domain) ? $defaultEmail.'@'.$domain : $defaultEmail;
$defaultDestination = empty($_POST['defaultDestination']) ? '' : trim(strtolower($_POST['defaultDestination']));

if(strstr_after($defaultEmail, '@') != $domain)
{
  $errObj->defaultEmail = "Please enter correct email address";
  $obj->response = false;
}
elseif($defaultEmail != '@'.$domain)
{
  if(!EmailChars($defaultEmail,'alias_domain'))
  {
    $errObj->defaultEmail = "Wrong characters in email please check ...";
    $obj->response = false;
  }
}
if(strpos($_SERVER['HTTP_REFERER'],'new-alias-user') == true || strpos($_SERVER['HTTP_REFERER'],'edit-alias-user') == true)
{
  $old = array(
    ":address" => $defaultEmail
  );
  if(isset($_POST['nuD']))
    $result = $db->select($TBL_alias_domain, "address = :address", $old, '', "address");
  else
    $result = $db->select($TBL_alias_domain, "address = :address AND id != '".$_POST['userID']."'", $old, '', "address");
  if(!empty($result))
  {
    $errObj->defaultEmail = "User already exists";
    $obj->response = false;
  }
}
if(strpos($_SERVER['HTTP_REFERER'],'edit-alias-domain') != true)
{
  if($defaultDestination == '') {
    $errObj->defaultDestination = "Please enter destination address";
    $obj->response = false;
  }
  elseif($defaultDestination  == '@') {
    $errObj->defaultDestination = "Please enter correct destination address";
    $obj->response = false;
  }
  elseif($defaultDestination == '@'.$domain || strstr_after($defaultDestination, '@') == $domain || $defaultDestination == $domain ) {
    $errObj->defaultDestination = "Destination address can not be same as alias domain";
    $obj->response = false;
  }
  elseif(!check_alias_domain($defaultDestination)) {
    $errObj->defaultDestination = "Wrong destination address ...";
    $obj->response = false;
  }
  elseif($defaultEmail != '@'.$domain && preg_match('/^@/', $defaultDestination))
  {
    $errObj->defaultEmail = "Single email address can not be forward to entire domain";
    $obj->response = false;
  }
}
if($obj->response == false) {
  $obj->errors = $errObj;
  header("content-type: application/json");
  echo json_encode($obj);
  exit;
}

$isDefault = isset($_POST['nuD']) ? 'N' : 'Y';
$active = (strpos($_SERVER['HTTP_REFERER'],'new-alias-domain') == true || strpos($_SERVER['HTTP_REFERER'],'new-alias-user') == true ) ? 1 : isset($_POST['active']) ? 1 : 0;

  $new_domain = array(
   "address" => $defaultEmail,
   "alias_domain" => $domain,
   "target_domain" => $defaultDestination,
   "isDefault" => $isDefault,
   "created" => date('Y-m-d H:i:s'),
   "modified" => date('Y-m-d H:i:s'),
   "active" => $active
  );

if(strpos($_SERVER['HTTP_REFERER'],'edit-alias-domain') != true && !isset($_POST['userID']))
{
  $db->insert($TBL_alias_domain, $new_domain);
}
elseif(isset($_POST['userID']))
{
  unset($new_domain['alias_domain']);
  unset($new_domain['isDefault']);
  unset($new_domain['created']);
  $result = $db->update($TBL_alias_domain, $new_domain, "id = '".decoded($_POST['udp'])."'");
}
else
{
  if($_POST['domain_admin'] == '')
  {
    $errObj->domain_admin = "Please enter admin email address";
    $obj->response = false;
  }
  else
  {
    $thisAdmin = explode("@",escape_string(trim(strtolower($_POST['domain_admin']))));
    $admin_email = (isset($thisAdmin[1]) && $thisAdmin[1] == $domain) ? $thisAdmin[0] : escape_string(trim(strtolower($_POST['domain_admin'])));

    if(!EmailLength($thisAdmin[0]))
    {
      $errObj->domain_admin = "Admin email should be 2 to 32 characters";
      $obj->response = false;
    }
    elseif(!EmailChars($admin_email))
    {
      $errObj->domain_admin = "Wrong characters in email please check instructions ...";
      $obj->response = false;
    }
    elseif(!user_exists($admin_email.'@'.$domain))
    {
      $errObj->domain_admin = "User already exists ...";
      $obj->response = false;
    }
      elseif(preg_match('/^postmaster|abuse|webmaster|hostmaster/',$admin_email))
      {
        $errObj->domain_admin = "Postmaster, Abuse, Webmaster & Hostmaster are reserved...";
        $obj->response = false;
      }
    }
    if($_POST['password'] == '')
    {
      $errObj->password = "Please enter password";
      $obj->response = false;
    }
    else
    {
      if($_POST['conpassword'] == '')
      {
        $errObj->conpassword = "Please enter confirm password";
        $obj->response = false;
      }
      else
      {
        if(strlen($_POST['password']) < 4)
        {
          $errObj->password = "Password should be > 4 characters";
          $obj->response = false;
        }
        else
        {
          if($_POST['conpassword'] != $_POST['password'])
          {
            $errObj->conpassword = "Password not matched";
            $obj->response = false;
          }
        }
      }
    }
    if(!empty($_POST['mailboxes']) && !preg_match("/^[0-9]+$/", $_POST['mailboxes']))
    {
      $errObj->mailboxes = "Mailboxes should be in digits";
      $obj->response = false;
    }
    if(!empty($_POST['groups']) && !preg_match("/^[0-9]+$/", $_POST['groups']))
    {
      $errObj->groups = "Groups should be in digits";
      $obj->response = false;
    }
    if(!empty($_POST['maxquota']) && !preg_match("/^[0-9]+$/", $_POST['maxquota']))
    {
      $errObj->maxquota = "Max quota should be in digits";
      $obj->response = false;
    }
    if($obj->response == false)
    {
      $obj->errors = $errObj;
      header("content-type: application/json");
      echo json_encode($obj);
      exit;
    }

    $domain_admin = $admin_email.'@'.$domain;
    $description = '';
    $admin_pass = escape_string(trim($_POST['conpassword']));
    $mailboxes = empty($_POST['mailboxes']) ? 0 : intval(trim($_POST['mailboxes']));
    $groups = empty($_POST['groups']) ? 0 : intval(trim($_POST['groups']));
    $maxquota = empty($_POST['maxquota']) ? 0 : intval(trim($_POST['maxquota']));
    $defaultmbox = isset($_POST['defaultmbox']) ? 'Y' : 'N';

    /* Delete parking domain */
    $bind = array(
      ":domain" => $domain
    );

    $db->delete($TBL_alias_domain, "alias_domain = :domain", $bind);

    /* Create real domain */
    addDomain($domain,$description,$mailboxes,$groups,$maxquota,$domain_admin,$admin_pass,$defaultmbox);

  }

if(strpos($_SERVER['HTTP_REFERER'],'edit-alias-domain') == true)
  $obj->page = "/?domain=".encoded($domain);
elseif(strpos($_SERVER['HTTP_REFERER'],'new-alias-user') == true)
  $obj->page = "/alias-domain-users?domain=".encoded($_POST['domainname']);
elseif(strpos($_SERVER['HTTP_REFERER'],'edit-alias-user') == true)
  $obj->page = "/alias-domain-users?domain=".encoded($_POST['domainname']);
else
  $obj->page = "/alias-domains?domain=".encoded($domain);
header("content-type: application/json");
echo json_encode($obj);

?>
