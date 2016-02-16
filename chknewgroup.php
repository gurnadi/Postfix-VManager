<?php

require_once('inc/common.php');

if(!isset($_SESSION['role']) && decoded($_SESSION['role']) != 'SuperAdmin' && (decoded($_SESSION['role']) != 'DomainAdmin' || decoded($_SESSION['role']) != 'SubAdmin'))
{
  header('Location: /');
  exit;
}
if(!isset($_POST['address']) || empty($_POST['address']) && !isset($_POST['domain']) || empty($_POST['domain']))
{
  header('Location: /');
  exit;
}
if(strpos($_SERVER['HTTP_REFERER'],'editgroup') == true && (!isset($_POST['udp']) || empty($_POST['udp']) || decoded($_POST['udp']) != $_POST['address']))
{
  header('Location: /');
  exit;
}

$domain_stats = domain_property(decoded($_POST['domain']));

$obj = new stdClass();
$obj->response = true;
$errObj = new stdClass();

if(!isset($_POST['udp']))
{
  $bind = array (
    ":domain" => decoded($_POST['domain']),
    ":active" => 1,
    ":ds_groups" => 'N'
  );

  $result = $db->select($TBL_domain, "domain = :domain AND active = :active AND ds_groups = :ds_groups", $bind, '', "domain");
  if(empty($result))
  {
    $errObj->name = "Wrong Domain or Disabled";
    $obj->response = false;
  }
  if(escape_string(trim(strtolower($_POST['address']))) == '')
  {
    $errObj->address = "Please enter group (without any domain)";
    $obj->response = false;
  }
  else
  {
    $thisAddr = explode("@",escape_string(trim(strtolower($_POST['address']))));
    $address = (isset($thisAddr[1]) && $thisAddr[1] == decoded($_POST['domain'])) ? $thisAddr[0] : escape_string(trim(strtolower($_POST['address'])));

    if($domain_stats[0]['groups'] != 0 && $domain_stats[0]['tgroups'] == $domain_stats[0]['groups'])
    {
      $errObj->address = "Groups Limit Exceeded";
      $obj->response = false;
    }
    elseif(!EmailLength($thisAddr[0]))
    {
      $errObj->address = "Group should be 2 to 32 characters";
      $obj->response = false;
    }
    elseif(!EmailChars($address))
    {
      $errObj->address = "Wrong characters in email please check instructions ...";
      $obj->response = false;
    }
    elseif(!user_exists($address.'@'.decoded($_POST['domain'])))
    {
      $errObj->address = "Username already exists with same name...";
      $obj->response = false;
    }
    elseif(!group_exists($address.'@'.decoded($_POST['domain']),decoded($_POST['domain'])))
    {
      $errObj->address = "Group already exists ...";
      $obj->response = false;
    }
  }
}
if(trim($_POST['goto']) == '')
{
  $errObj->goto = "Please enter goto address";
  $obj->response = false;
}
else
{
  $goto = strtolower($_POST['goto']);
  $goto = preg_replace ('/\\\r\\\n/', ',', $goto);
  $goto = preg_replace ('/\r\n/', ',', $goto);
  $goto = preg_replace ('/\n/', ',', $goto);
  $goto = preg_replace ('/[\s]+/i', '', $goto);
  $goto = preg_replace ('/,*$|^,*/', '', $goto);
  $goto = preg_replace ('/,,*/', ',', $goto);

  $goto_aliases = explode(',', $goto);
  $goto_aliases = array_unique($goto_aliases);

  foreach ($goto_aliases as $goto_address)
  {
    if(!validateEmail ($goto_address))
    {
      $errObj->goto = "Wrong Email ". $goto_address;
      $obj->response = false;
    }
  }
  $goto = join(",", $goto_aliases);
}

if ($obj->response == false) {
    $obj->errors = $errObj;
    header("content-type: application/json");
    echo json_encode($obj);
    exit;
}

$domain = decoded($_POST['domain']);
if(isset($_POST['udp']))
  $address = escape_string(trim(strtolower($_POST['address'])));
elseif($address == "catch-all")
  $address = "@".$domain;
else
  $address = $address.'@'.$domain;
$active = isset($_POST['active']) ? 1 : 0;

$newgroup = array(
 "address" => $address,
 "goto" => $goto,
 "domain" => $domain,
 "created" => date('Y-m-d H:i:s'),
 "modified" => date('Y-m-d H:i:s'),
 "active" => $active
);

if(empty($_POST['udp']))
{
  $db->insert($TBL_groups, $newgroup);
}
else
{
  unset($newgroup['address']);
  unset($newgroup['domain']);
  unset($newgroup['created']);

  $bind = array (
    ":address" => $address
  );

  $db->update($TBL_groups, $newgroup, "address = :address", $bind);
}
  $obj->page = "/groups?group=".encoded($address);
  header("content-type: application/json");
  echo json_encode($obj);
?>
