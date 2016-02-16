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
if(strpos($_SERVER['HTTP_REFERER'],'editdomain') == true && (!isset($_POST['udp']) || empty($_POST['udp']) || decoded($_POST['udp']) != $_POST['domainname']))
{
  header('Location: /');
  exit;
}

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if(empty($_POST['udp']))
{
  if($_POST['domainname'] == '')
  {
    $errObj->domainname = "Please enter domain name";
    $obj->response = false;
  }
  elseif(!check_domain(escape_string(trim(strtolower($_POST['domainname'])))))
  {
    $errObj->domainname = "Domain name is wrong";
    $obj->response = false;
  }
  else
  {
    $bind = array (
      ":domain" => escape_string(trim(strtolower($_POST['domainname'])))
    );
    $result = $db->select($TBL_domain, "domain = :domain", $bind, '', "domain");
    $result2 = $db->select($TBL_backupmx, "domain = :domain", $bind, '', "domain");
    $result3 = $db->select($TBL_alias_domain, "alias_domain = :domain", $bind, '', "alias_domain");
    if(!empty($result))
    {
      $errObj->domainname = "Domain already exists";
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
  if($_POST['domain_admin'] == '')
  {
    $errObj->domain_admin = "Please enter admin email address";
    $obj->response = false;
  }
  else
  {
    $thisAdmin = explode("@",escape_string(trim(strtolower($_POST['domain_admin']))));
    $admin_email = (isset($thisAdmin[1]) && $thisAdmin[1] == escape_string(trim(strtolower($_POST['domainname'])))) ? $thisAdmin[0] : escape_string(trim(strtolower($_POST['domain_admin'])));

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
    elseif(!user_exists($admin_email.'@'.escape_string(trim(strtolower($_POST['domainname'])))))
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

$domain = escape_string(trim(strtolower($_POST['domainname'])));
if(empty($_POST['udp']))
{
  $domain_admin = $admin_email.'@'.$domain;
  $admin_pass2 = escape_string(trim($_POST['conpassword']));
}
$description = (!empty($_POST['desc'])) ? trim($_POST['desc']) : '';
$mailboxes = empty($_POST['mailboxes']) ? 0 : intval(trim($_POST['mailboxes']));
$groups = empty($_POST['groups']) ? 0 : intval(trim($_POST['groups']));
$maxquota = empty($_POST['maxquota']) ? 0 : intval(trim($_POST['maxquota']));

$bind = array (
  ":domain" => $domain
);

if(!empty($_POST['udp']))
{
  $active = isset($_POST['active']) ? 1 : 0;
  $ds_mboxes = isset($_POST['ds_mboxes']) ? 'Y' : 'N';
  $ds_groups = isset($_POST['ds_groups']) ? 'Y' : 'N';
}
else
{
  $active = 1;
  $ds_mboxes = 1;
  $ds_groups = 1;
}

$newdomain = array(
 "domain" => $domain,
 "description" => $description,
 "groups" => $groups,
 "mailboxes" => $mailboxes,
 "maxquota" => $maxquota,
 "transport" => 'virtual',
 "ds_mboxes" => $ds_mboxes,
 "ds_groups" => $ds_groups,
 "created" => date('Y-m-d H:i:s'),
 "modified" => date('Y-m-d H:i:s'),
 "active" => $active
);

if(empty($_POST['udp']))
{
  $admin = array(
   "username" => $domain_admin,
   "password" => encpasswd($admin_pass2),
   "name" => 'Domain Admin',
   "domain" => $domain,
   "maildir" => maildir($domain_admin),
   "quota" => multiply_quota($maxquota),
   "created" => date('Y-m-d H:i:s'),
   "modified" => date('Y-m-d H:i:s'),
   "admin" => 'DomainAdmin',
   "active" => $active
  );

  $db->insert($TBL_domain, $newdomain);
  $db->insert($TBL_mailbox, $admin);
  sendMail($_SESSION['userName'],$domain_admin, $CONF['subject'], $CONF['welcome_text']);

  if(isset($_POST['defaultmbox']))
  {
    foreach ($CONF['default_mailboxes'] as $address)
    {
      $userAddr = $address . "@" . $domain;

      $default = array(
        "username" => $userAddr,
        "password" => encpasswd($CONF['default_mailboxes_passwd']),
        "name" => 'Default Postmaster Account',
        "domain" => $domain,
        "maildir" => maildir($userAddr),
        "quota" => multiply_quota($maxquota),
        "created" => date('Y-m-d H:i:s'),
        "modified" => date('Y-m-d H:i:s'),
        "admin" => 'User',
        "active" => $active
      );
      $db->insert($TBL_mailbox, $default);
      sendMail($_SESSION['userName'],$userAddr, $CONF['subject'], $CONF['welcome_text']);
    }
  }
}
else
{
  unset($newdomain['domain']);
  unset($newdomain['created']);
  $result = $db->update($TBL_domain, $newdomain, "domain = :domain", $bind);

  /*
  $update_bind = array(':admin' => 'User');
  $bind = array_merge((array)$bind, (array)$update_bind);
  $db->update($TBL_mailbox, $admin, "domain = :domain AND admin != :admin", $bind);
  */

  if($maxquota != intval(trim($_POST['oldquota'])))
  {
     $quota = array(
       "quota" =>  multiply_quota($maxquota)
     );
     $db->update($TBL_mailbox, $quota, "domain = :domain", $bind);
  }
}
  $obj->page = "/?domain=".encoded($domain);  
  header("content-type: application/json");
  echo json_encode($obj);

?>
