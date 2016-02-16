<?php
if(ereg("login.php", $_SERVER['PHP_SELF']))
{
   header ("Location: /");
   exit;
}
if(!file_exists('cfg.mdb'))
{
   header ("Location: /setup/");
   exit;
}
?>

 <div class="container">
    <form class="form-horizontal" action='chklogin.php' onsubmit="submitData(this); return false;">
    <fieldset>
    <div id="legend">
    <legend class="">Login</legend>
    </div>

    <div class="control-group" style="margin-top: 20px;">
     <label class="control-label" for="username">Username</label>
     <div class="controls">
      <div class="input-prepend">
       <span class="add-on"><i class="icon-envelope"></i></span>
       <input type="text" id="username" name="username" placeholder="" class="input-xlarge">
      </div>
     </div>
     <div class="controls" style="margin-top: 10px;"><p class="help-block">e.g. username@yourdomainname.com</p></div>
    </div>
 
    <div class="control-group">
      <label class="control-label">Password</label>
      <div class="controls">
       <div class="input-prepend">
        <span class="add-on"><i class="icon-key"></i></span>
        <input type="password" id="password" name="password" placeholder="" class="input-xlarge">
      </div>
     </div>
    <div class="controls" style="margin-top: 10px;"><p class="help-block">Use your ID and password to manging your accounts.</p></div>
    </div>

    <div class="control-group">
    <div class="controls">
    <button class="btn btn-success">Sign In</button>
    </div>
    </div>
    </fieldset>
    </form>
 </div>

<?php require_once('js/form.js');?>
