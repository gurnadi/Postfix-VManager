<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) && decoded($_SESSION['role']) != 'SuperAdmin' && (decoded($_SESSION['role']) != 'DomainAdmin' || decoded($_SESSION['role']) != 'SubAdmin'))
{
  header('Location: /');
  exit;
}
if(!isset($_POST['username']) || empty($_POST['username']) && !isset($_POST['domain']) || empty($_POST['domain']))
{
  header('Location: /');
  exit;
}
if(decoded($_POST['domain']) == '')
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['HTTP_REFERER'],'edituser') == true && (!isset($_POST['udp']) || empty($_POST['udp']) || decoded($_POST['udp']) != $_POST['username']))
{
  header('Location: /');
  exit;
}

$domain_stats = domain_property(decoded($_POST['domain']));

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if(strpos($_SERVER['HTTP_REFERER'],'edituser') == false)
{
  $bind = array (
    ":domain" => decoded($_POST['domain']),
    ":active" => 1,
    ":ds_mboxes" => 'N'
  );

  $result = $db->select($TBL_domain, "domain = :domain AND active = :active AND ds_mboxes = :ds_mboxes", $bind, '', "domain");
  if(empty($result))
  {
    $errObj->name = "Wrong Domain or Disabled";
    $obj->response = false;
  }
  if(escape_string(trim(strtolower($_POST['username']))) == '')
  {
    $errObj->username = "Please enter username (without any domain)";
    $obj->response = false;
  }
  else
  {
    $thisAdmin = explode("@",escape_string(trim(strtolower($_POST['username']))));
    $username = (isset($thisAdmin[1]) && $thisAdmin[1] == decoded($_POST['domain'])) ? $thisAdmin[0] : escape_string(trim(strtolower($_POST['username'])));

    if($domain_stats[0]['mailboxes'] != 0 && $domain_stats[0]['users'] == $domain_stats[0]['mailboxes'])
    {
      $errObj->username = "User Limit Exceeded";
      $obj->response = false;
    }
    elseif(!EmailLength($thisAdmin[0]))
    {
      $errObj->username = "Username should be 2 to 32 characters";
      $obj->response = false;
    }
    elseif(!EmailChars($username))
    {
      $errObj->username = "Wrong characters in email please check instructions ...";
      $obj->response = false;
    }
    elseif(!user_exists($username.'@'.decoded($_POST['domain'])))
    {
      $errObj->username = "User already exists ...";
      $obj->response = false;
    }
    elseif(!group_exists($username.'@'.decoded($_POST['domain']),decoded($_POST['domain'])))
    {
      $errObj->username = "Group is already exists with same name...";
      $obj->response = false;
    }
  }
}

if(empty($_POST['udp']) || (!empty($_POST['udp']) && (trim($_POST['password']) != '' || trim($_POST['conpassword']) != '')))
{
  if(trim($_POST['password']) == '')
  {
    $errObj->password = "Please enter password";
    $obj->response = false;
  }
  elseif(strlen(trim($_POST['password'])) < 4)
  {
    $errObj->password = "Password should be > 4 characters";
    $obj->response = false;
  }
  elseif(trim($_POST['conpassword']) == '')
  {
    $errObj->conpassword = "Please enter confirm password";
    $obj->response = false;
  }
  elseif(trim($_POST['password']) != trim($_POST['conpassword']))
  {
    $errObj->conpassword = "Password not matched";
    $obj->response = false;
  }
}

if(decoded($_SESSION['role']) == 'User' || decoded($_SESSION['role']) == 'SuperAdmin' && $_SESSION['userName'] == $_POST['username'])
  $quota = '0';
else
{
  $quota = escape_string(trim($_POST['quota']));
  $quota = empty($quota) ? $domain_stats[0]['maxquota'] : $_POST['quota'];

  if(!preg_match("/^[0-9]+$/", $quota))
  {
    $errObj->quota = "Mail quota should be in digits";
    $obj->response = false;
  }
  elseif($domain_stats[0]['maxquota'] != 0 && $domain_stats[0]['maxquota'] < $quota)
  {
    $errObj->quota = "Your default quota limit is: <strong>" .$domain_stats[0]['maxquota']." MBs</strong> but you entered > then allocated limit";
    $obj->response = false;
  }
}

if ($obj->response == false) {
    $obj->errors = $errObj;
    header("content-type: application/json");
    echo json_encode($obj);
    exit;
}

$domain = decoded($_POST['domain']);
$name = (!empty($_POST['name'])) ? trim($_POST['name']) : '';
$username = isset($_POST['udp']) ? escape_string(trim(strtolower($_POST['username']))) : $username.'@'.$domain;
$pass1 = trim($_POST['password']);
$pass2 = trim($_POST['conpassword']);
$active = 1;
$role = isset($_POST['dm']) ? 'SubAdmin' : 'User';

$newuser = array(
 "username" => $username,
 "password" => encpasswd($pass2),
 "name" => $name,
 "maildir" => maildir($username),
 "quota" => multiply_quota(intval($quota)),
 "domain" => $domain,
 "created" => date('Y-m-d H:i:s'),
 "modified" => date('Y-m-d H:i:s'),
 "admin" => $role,
 "active" => $active
);

if(empty($_POST['udp']))
{
  $db->insert($TBL_mailbox, $newuser);
  sendMail($_SESSION['userName'],$username, $CONF['subject'], $CONF['welcome_text']);
}
else
{

  $user_stats = user_property($username);

  unset($newuser['username']);
  unset($newuser['name']);
  unset($newuser['password']);
  unset($newuser['maildir']);
  unset($newuser['domain']);
  unset($newuser['created']);
  unset($newuser['quota']);
  unset($newuser['admin']);
  unset($newuser['active']);

  $active = ($user_stats[0]['admin'] == decoded($_SESSION['role'])) ? 1 : (isset($_POST['active']) ? 1 : 0);
  if(decoded($_SESSION['role']) == 'SuperAdmin' && $user_stats[0]['admin'] == 'DomainAdmin')
    $role = $user_stats[0]['admin'];
  else
    $role = ($user_stats[0]['admin'] == decoded($_SESSION['role'])) ? decoded($_SESSION['role']) : (isset($_POST['dm']) ? 'SubAdmin' : 'User') ;

  if($user_stats[0]['name'] != $name)
  {
    $update_newuser = array('name' => $name);
    $newuser = array_merge((array)$newuser, (array)$update_newuser);
  }
  if(!empty($pass2))
  {
    $update_newuser = array('password' => encpasswd($pass2));
    $newuser = array_merge((array)$newuser, (array)$update_newuser);
  }
  if($user_stats[0]['quota'] != $quota && $user_stats[0]['admin'] != 'SuperAdmin' && decoded($_SESSION['role']) != 'User')
  {
    $update_newuser = array('quota' => multiply_quota(intval($quota)));
    $newuser = array_merge((array)$newuser, (array)$update_newuser);
  }
  if($user_stats[0]['admin'] != $role && $user_stats[0]['admin'] != 'DomainAdmin' && $user_stats[0]['admin'] != 'SuperAdmin')
  {
    $update_newuser = array('admin' => $role);
    $newuser = array_merge((array)$newuser, (array)$update_newuser);
  }
  if($user_stats[0]['active'] != $active)
  {
    $update_newuser = array('active' => $active);
    $newuser = array_merge((array)$newuser, (array)$update_newuser);
  }

  $bind = array (
    ":username" => $username
  );

  if($_SESSION['role'] != 'User'){

    if(trim($_POST['oldgoto']) != trim($_POST['goto'])){

      if(trim($_POST['oldgoto']) != '' && trim($_POST['goto']) == '')
        $actiond = 'remove';
      elseif(trim($_POST['oldgoto']) == '' && trim($_POST['goto']) != '')
        $actiond = 'save';
      else
        $actiond = 'update';

        $result = addgoto($username,$_POST['goto'],$actiond,$_POST['isa'],$_POST['user_domain'],$_POST['you']);

	if($result != 'true')
	{
	    $errObj->goto = $result;
	    $obj->response = false;
	}

	 if($obj->response == false) {
	   $obj->errors = $errObj;
	   header("content-type: application/json");
	   echo json_encode($obj);
	   exit;
	 }
    }

    else {
       if(trim($_POST['oldgoto']) == trim($_POST['goto']) && trim($_POST['oldyou']) != trim($_POST['you'])){
          $actiond = 'update';
          $result = addgoto($username,$_POST['goto'],$actiond,$_POST['isa'],$_POST['user_domain'],$_POST['you']);
       }
    }
  }

  $db->update($TBL_mailbox, $newuser, "username = :username", $bind);

}
  if($_SESSION['userName'] == $username)
    $obj->page = "/";
  else
    $obj->page = "/users?user=".encoded($username);
  header("content-type: application/json");
  echo json_encode($obj);
?>
