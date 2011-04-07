<div id="info" class="menu">
  <?php \Themer\Load::view('menus/info', $info); ?>
</div>

<div id="appearance" class="menu">
  <div id="color-picker">
    <div id="picker-nav">
      <input type="text" id="picker-input" value="" />
      <a href="#" id="picker-ok">OK</a>
      <a href="#" id="picker-cancel">Cancel</a>
    </div>
  </div>
  
  <div class="content">
  <?php if( ! empty($metas)) : ?>
    <?php \Themer\Load::view('menus/appearance', $metas); ?>
  <?php else : ?>
    <p class="info">There doesn't appear to be any custom Tumblr variable data embedded in this theme.</p>
  <?php endif ?>
  </div>
</div>

<div id="pages" class="menu">
  <?php \Themer\Load::view('menus/pages', array('pages' => $pages)); ?>
</div>

<div id="advanced" class="menu"></div>