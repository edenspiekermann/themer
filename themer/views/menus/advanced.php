<div class="advanced-box float">
  <div class="head">
    <input
      <?php if($AskEnabled) : ?>checked="yes"<?php endif; ?>
      type="checkbox"
      value="1"
      name="AskEnabled"/>
      <label for="AskEnabled">Enable questions</label>
  </div>

  <div class="content">
    <label for="AskLabel">Ask page title</label><br />
    <input type="text" name="AskLabel" value="<?php echo $AskLabel ?>" />
  </div>
</div>

<div class="advanced-box float">
  <div class="head">
    <input
      <?php if($AskEnabled) : ?>checked="yes"<?php endif; ?>
      type="checkbox"
      value="1"
      name="SubmissionsEnabled"/>
      <label for="SubmissionsEnabled">Enable submissions</label>
  </div>
  
  <div class="content">
    <label for="SubmitLabel">Submit page title</label><br />
    <input type="text" name="SubmitLabel" value="<?php echo $SubmitLabel ?>" />
  </div>
</div>

<div class="clear"></div>

<div class="advanced-box single twitter">
  <label for="TwtitterUsername">Twitter Username</label><br />
  <input type="text" name="TwitterUsername" value="<?php echo $TwitterUsername ?>" />
  <p class="info">Leave blank to disable.</p>
  <div class="clear"></div>
</div>

<div class="advanced-box single">
  <label for="per_page">Post Limit:</label>
  <select name="_per_page">
  <?php for($i = 1; $i <= 15; $i++) : ?>
    <option
      <?php if($i == $_per_page) : ?>selected="selected"<?php endif; ?>
      value="<?php echo $i; ?>"><?php echo $i; ?></option>
  <?php endfor; ?>
  </select>
</div>

<div class="advanced-box">
  <div class="head">
    <h2>CustomCSS</h2>
  </div>

  <div class="content">
    <textarea name="CustomCSS" id="custom-css"><?php echo $CustomCSS; ?></textarea>
  </div>
</div>