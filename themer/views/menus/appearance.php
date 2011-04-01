<?php foreach($color as $k => $v) : ?>
  <div class="color meta-box">
    
    <label 
      class="color-box"
      for="meta[color][<?php echo $k; ?>]"
      style="background-color: <?php echo $v; ?>;"></label>
      
    <input type="hidden" name="meta[color][<?php echo $k; ?>]" value="<?php echo $v; ?>" />
    
    <p><?php echo str_replace("_", " ", $k); ?> color</p>
    
    <div class="clear"></div>
  
  </div>
<?php endforeach; ?>

<?php foreach($font as $k => $v) : ?>
  <div class="font meta-box">
   
    <label for="meta[font][<?php echo $k; ?>]"><?php echo str_replace("_", " ", $k); ?> font:</label>
    
    <?php \Themer\Load::view('menus/fonts', array('index' => $k, 'selected' => $v)) ?>
  
  </div>
<?php endforeach; ?>

<?php foreach($if as $k => $v) : ?>
  <div class="if meta-box">
    
    <input
      <?php if($v == '1') : ?>checked="yes"<?php endif; ?>
      type="checkbox"
      name="meta[if][<?php echo $k; ?>]"/>
    
    <label for="meta[if][<?php echo $k; ?>]"><?php echo str_replace("_", " ", $k); ?></label>
  
  </div>
<?php endforeach; ?>

<?php foreach($text as $k => $v) : ?>
  <div class="text meta-box">
    
    <label for="meta[text][<?php echo $k; ?>]"><?php echo str_replace("_", " ", $k); ?>:</label>
    
    <input
      type="text"
      name="meta[text][<?php echo $k; ?>]"
      value="<?php echo $v; ?>" />
  
  </div>
<?php endforeach; ?>