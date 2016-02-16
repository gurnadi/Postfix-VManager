<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin')
{
  header('Location: /');
  exit;
}
if(!isset($_POST['domain']))
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['HTTP_REFERER'],'edit-parking-domain') == true && (!isset($_POST['udp']) || empty($_POST['udp']) || decoded($_POST['udp']) != $_POST['domain']))
{
  header('Location: /');
  exit;
}

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if($_POST['domain'] == '')
{
  $errObj->domain = "Please enter domain name";
  $obj->response = false;
}
else
{
  if(!check_domain(escape_string(trim(strtolower($_POST['domain'])))))
  {
    $errObj->domain = "Domain name is wrong";
    $obj->response = false;
  }
  if(empty($_POST['udp']))
  {
   $bind = array (
      ":domain" => escape_string(trim(strtolower($_POST['domain'])))
    );

    $result = $db->select($TBL_backupmx, "domain = :domain", $bind, '', "domain");
    $result2 = $db->select($TBL_domain, "domain = :domain", $bind, '', "domain");
    $result3 = $db->select($TBL_alias_domain, "alias_domain = :domain", $bind, '', "domain");
    if(!empty($result))
    {
      $errObj->domain = "Parking Domain already exists";
      $obj->response = false;
    }
    if(!empty($result2))
    {
      $errObj->domain = "Domain already exists with same name";
      $obj->response = false;
    }
    if(!empty($result3))
    { 
      $errObj->domain = "Alias Domain already exists with same name";
      $obj->response = false;
    }
  }
}
if($_POST['mx'] == '')
{
  $errObj->mx = "Please enter primary mail server address";
  $obj->response = false;
}
if($obj->response == false)
{
  $obj->errors = $errObj;
  header("content-type: application/json");
  echo json_encode($obj);
  exit;
}

$domain = escape_string(trim(strtolower($_POST['domain'])));
$mx = escape_string(trim(strtolower($_POST['mx'])));
$description = (!empty($_POST['desc'])) ? trim($_POST['desc']) : '';
if(!empty($_POST['udp']))
  $active = isset($_POST['active']) ? 1 : 0;
else
  $active = 1;

$bind = array (
  ":domain" => $domain
);

$newdomain = array(
 "domain" => $domain,
 "smtp_host" => $mx,
 "description" => $description,
 "created" => date('Y-m-d H:i:s'),
 "modified" => date('Y-m-d H:i:s'),
 "active" => $active
);

$newtransport = array(
 "domain" => $domain,
 "destination" => 'smtp:'.$mx
);

if(empty($_POST['udp']))
{
  $db->insert($TBL_backupmx, $newdomain);
  $db->insert($TBL_transport, $newtransport);
}
else
{
  if(isset($_POST['real']))
  {
    if($_POST['domain_admin'] == '')
    {
      $errObj->domain_admin = "Please enter admin email address";
      $obj->response = false;
    }
    else
    {
      $thisAdmin = explode("@",escape_string(trim(strtolower($_POST['domain_admin']))));
      $admin_email = (isset($thisAdmin[1]) && $thisAdmin[1] == escape_string(trim(strtolower($_POST['domain'])))) ? $thisAdmin[0] : escape_string(trim(strtolower($_POST['domain_admin'])));

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
      elseif(!user_exists($admin_email.'@'.escape_string(trim(strtolower($_POST['domain'])))))
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
    $admin_pass = escape_string(trim($_POST['conpassword']));
    $mailboxes = empty($_POST['mailboxes']) ? 0 : intval(trim($_POST['mailboxes']));
    $groups = empty($_POST['groups']) ? 0 : intval(trim($_POST['groups']));
    $maxquota = empty($_POST['maxquota']) ? 0 : intval(trim($_POST['maxquota']));
    $defaultmbox = isset($_POST['defaultmbox']) ? 'Y' : 'N';

    /* Delete parking domain */
    $bind = array(
      ":domain" => $domain
    );

    $db->delete($TBL_backupmx, "domain = :domain", $bind);
    $db->delete($TBL_transport, "domain = :domain", $bind);

    /* Create real domain */
    addDomain($domain,$description,$mailboxes,$groups,$maxquota,$domain_admin,$admin_pass,$defaultmbox);

    unset($_SESSION['epdomain']);
  }
  else
  {
    unset($newdomain['domain']);
    unset($newdomain['created']);
    unset($newtransport['domain']);

    $result = $db->update($TBL_backupmx, $newdomain, "domain = :domain", $bind);
    $result = $db->update($TBL_transport, $newtransport, "domain = :domain", $bind);
    if($result == 1)
    {
      unset($_SESSION['epdomain']);
    }
  }
}

  if(isset($_POST['real']))
    $obj->page = "/?domain=".encoded($domain);  
  else
    $obj->page = "/parking-domains?domain=".encoded($domain);  

  header("content-type: application/json");
  echo json_encode($obj);

?>
