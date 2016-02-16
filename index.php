<?php

 require_once('inc/common.php');
 require_once('header.php');

 if(!isset($_SESSION['userName'])){
   require_once('login.php');
 }else {
 if(strpos($_SERVER['REQUEST_URI'],'sendemail') == true)
   require_once('sendemail.php');
 elseif(preg_match("/newdomain|editdomain/i",$_SERVER['REQUEST_URI']))
   require_once('newdomain.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'parking-domains') == true)
   require_once('parking-domains.php');
 elseif(preg_match("/new-parking-domain|edit-parking-domain/i",$_SERVER['REQUEST_URI']))
   require_once('new-parking-domain.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'alias-domain-users') == true)
   require_once('alias-domain-users.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'alias-domains') == true)
   require_once('alias-domains.php');
 elseif(preg_match("/new-alias-domain|edit-alias-domain|new-alias-user|edit-alias-user/i",$_SERVER['REQUEST_URI']))
   require_once('new-alias-domain.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'users') == true)
   require_once('users.php');
 elseif(preg_match("/newuser|edituser/i",$_SERVER['REQUEST_URI']))
   require_once('newuser.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'groups') == true)
   require_once('groups.php');
 elseif(preg_match("/newgroup|editgroup/i",$_SERVER['REQUEST_URI']))
   require_once('newgroup.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'logout') == true)
   require_once('logout.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'admins') == true)
   require_once('admins.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'forwarding') == true)
   require_once('forwarders.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'autoresponse') == true)
   require_once('autoresponse.php');
 elseif(strpos($_SERVER['REQUEST_URI'],'vacation') == true)
   require_once('vacation.php');
 elseif(decoded($_SESSION['role']) == 'User')
   require_once('user.php');
 elseif(decoded($_SESSION['role']) != 'SuperAdmin')
   require_once('users.php');
 else
   require_once('main.php');
 }
 require_once('bottom.php');
?>
