<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Postfix, User Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Postfix User Manager with PHP and MySQL">
    <meta name="author" content="Umar Draz">
    <link rel="shortcut icon" href="/img/favicon.ico" />

    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 20px;
      }
    </style>

    <link href="/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
    <link href="/css/style.css" type="text/css" rel="stylesheet">
    <link href="/css/jquery-ui.css" type="text/css" rel="stylesheet">
    <script src="/js/jquery.min.js"></script>
    <script src="/js/jquery-ui.min.js"></script>
    <script src="/js/jquery.scrollTo-min.js"></script>
    <script src="/js/bootstrap.js"></script>
    <script src="/js/bootstrap-popover.js"></script>

    <!--[if lt IE 9]>
      <script src="/js/html5shiv.js"></script>
    <![endif]-->
</head>
<body>

<!--<div class="navbar navbar-inverse navbar-fixed-top">-->
<div class="navbar navbar-fixed-top">
<div id="img_load">Please Wait ...</div>
      <div class="navbar-inner">
        <div class="container">
          <a href="/" class="brand" style="font-size: 18px; font-family: sans-serif; font-weight: bold;"><img src="/img/postfixmange.png" border="0" style="width: 24px;">&nbsp; Postfix vManager</a>
<?php
  if(isset($_SESSION['userName'])){
?>
     <div class="nav-collapse collapse">
      <ul class="nav">
<?php 
 if(decoded($_SESSION['role']) == 'SuperAdmin'){ 
?>
              <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#" <?php if(preg_match("/newdomain/i",$_SERVER['REQUEST_URI']))echo 'style="color: #004A95;"';?>>Domains <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/">List Domains</a></li>
                  <li><a href="/newdomain">New Domain</a></li>
                  <li class="divider"></li>
                  <li class="nav-header">Alias Domains</li>
                  <li><a href="/alias-domains">List Alias Domains</a></li>
                  <li><a href="/new-alias-domain">New Alias Domain</a></li>
                  <li class="divider"></li>
                  <li class="nav-header">Parking Domains</li>
                  <li><a href="/parking-domains">List Parking Domains</a></li>
                  <li class=""><a href="/new-parking-domain">New Parking Domain</a></li>
                </ul>
              </li>
<?php 
} 
  if(decoded($_SESSION['role']) != 'User'){ 
?>
              <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#" <?php if(preg_match("/user|group/i",$_SERVER['REQUEST_URI']))echo 'style="color: #004A95;"';?>>Users <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/users">Users List</a></li>
                  <li><a href="/newuser">New User</a></li>
                  <li class="divider"></li>
                  <li class="nav-header">Groups</li>
                  <li><a href="/groups">Group List</a></li>
                  <li><a href="/newgroup">New Group</a></li>
                </ul>
              </li>
<?php 
}
if(decoded($_SESSION['role']) == 'SuperAdmin' || decoded($_SESSION['role']) == 'DomainAdmin'){
?>
<li <?php if(preg_match("/admins/i",$_SERVER['REQUEST_URI'])) echo 'class="active"';?>><a href="/admins">Domain Admins</a></li>
<li <?php if(preg_match("/sendemail/i",$_SERVER['REQUEST_URI'])) echo 'class="active"';?>><a href="/sendemail">Send Email</a></li>
<?php 
}
?>
</ul>
<ul class="nav pull-right">
<li class="dropdown">
<a data-toggle="dropdown" class="dropdown-toggle" href="javascript:"><?php echo $_SESSION['userName'];?> <b class="caret"></b></a>
<ul class="dropdown-menu">
<li><span style="white-space: nowrap; padding: 5px 12px; display: block;">
<?php
if(decoded($_SESSION['role']) == 'SuperAdmin')
  echo 'You are SuperAdmin of this App';
else
  echo 'This account is managed by <strong>'.decoded($_SESSION['domain']).'</strong>';
?>
</span></li>
  <li class="divider"></li>
  <li class="nav-header"><?php if(isset($_SESSION['name'])) echo decoded($_SESSION['name']);?></li>
  <li><span style="white-space: nowrap; margin-left: 20px; margin-top: -4px; display: block;"><?php echo $_SESSION['userName'];?></span></li>
  <li><a href="/edituser?user=<?php echo encoded($_SESSION['userName']);?>" class="btn btn-primary settings"><span style="color: white; margin-left: -10px;">Settings</span></a></li>
  <li class="divider"></li>
  <li class=""><a href="/logout"><strong>Sign out</strong></a></li>
 </ul>
  </li>
</ul>
 </div>
<?php
}
?>
       </div>
      </div>
    </div>
