          <div class="control-group">
            <label for="input01" class="control-label">Domain Admin</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-user"></i></span>
                 <input type="text" autocomplete="off" id="domain_admin" name="domain_admin" class="input-xlarge" style="width: 242px;">
                </div>
                <div class="help-line" id="help" style="display: none;"><ul><li>Enter email address without any domain</li><li>Email should start with a letter</li><li>You may use letters, numbers, underscores, plus sign and one dot (.)</li><li>Email length should be 2 to 32 characters.</li></ul></div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Password</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-key"></i></span>
                <input type="password" id="password" name="password" class="input-xlarge" style="width: 242px;">
                </div>
                <div class="help-line" id="adminpass" style="display: none;"><ul><li>Use both letters and numbers</li><li>Add special characters (such as @, ?, %)</li><li>Mix capital and lowercase letters</li></ul></div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Password (Confirm)</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on"><i class="icon-key"></i></span>
                <input type="password" id="conpassword" name="conpassword" class="input-xlarge" style="width: 242px;">
                </div>
            </div>
          </div>

          <div class="control-group">
            <label class="control-label">Mailboxes</label>
            <div class="controls">
              <input type="text" id="mailboxes" value="" name="mailboxes" class="input-xlarge">
             <div class="help-line" id="dmbox" style="display: none;"><ul><li>e.g. 500,1000,10000</li> <li>Leave Blank for unlimited</li></ul></div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Groups</label>
            <div class="controls">
              <input type="text" id="groups" name="groups" value="" name="mailboxes" class="input-xlarge">
             <div class="help-line" id="dmals" style="display: none;"><ul><li>e.g. 500,1000,10000</li><li>Leave Blank for unlimited</li></ul></div>
            </div>
          </div>
          <div class="control-group">
            <label for="input01" class="control-label">Max Quota</label>
            <div class="controls">
                 <div class="input-prepend">
                 <span class="add-on">MBs</span>
                <input type="text" id="maxquota" name="maxquota" value="" class="input-xlarge" style="width: 230px;">
             <div class="help-line" id="dmquota" style="display: none;"><ul><li>Leave Blank for unlimited</li><li>Please enter quota in MBs</li></ul></div>
                </div>
            </div>
          </div>
          <div class="control-group">
            <label for="optionsCheckbox" class="control-label">Add default Mailbox</label>
            <div class="controls">
                <input type="checkbox" name="defaultmbox" id="defaultmbox" checked>
            </div>
          </div>
