<select name="meta[font][<?php echo $index ?>]">
<?php foreach(\Themer\Data::get('tumblr.fonts') as $font => $style) : ?>
  <option
    <?php if($selected === $font) : ?>selected="selected"<?php endif; ?>
    value="<?php echo $font; ?>"
    style="font-family: <?php echo $style; ?>;"><?php echo $font ?></option>
<?php endforeach; ?>
</select>
