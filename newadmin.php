<?php

if (ereg ("sendemail.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../");
   exit;
}

?>

<div class="container">

<ul class="breadcrumb">
<li><a href="../">Home</a><span class="divider">/</span></li>
<li><a href="/users">Users List</a><span class="divider">/</span></li>
<li><a href="/newalias">New Alias</a><span class="divider">/</span></li>
<li><a href="/aliases">List Aliases</a></li>
</ul>

<form class="form-horizontal">
        <fieldset>
          <legend>Add a new domain Admin</legend>
          <div class="control-group">
            <label for="input01" class="control-label">Username</label>
            <div class="controls">
              <input type="text" id="input01" class="input-xlarge">
		<p class="help-block">Please enter a valid email address</p>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">Password</label>
            <div class="controls">
              <input type="password" id="passwd" name="passwd" class="input-xlarge">
              <p class="help-block">Password should be at least 4 characters</p>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Password (Confirm)</label>
            <div class="controls">
              <input type="password" id="passwd2" name="passwd2" class="input-xlarge">
              <p class="help-block">Please confirm password</p>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Domain</label>
            <div class="controls">
		<select class="input-xlarge" multiple="multiple" name="subject" id="subject">
		  <option value="service">General Customer Service</option>
		</select>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save Admin</button>
            <button class="btn">Cancel</button>
          </div>
        </fieldset>
</form>

</div>
