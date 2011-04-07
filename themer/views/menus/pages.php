<?php foreach($pages as $i => $page) : ?>
<div class="page">
  <a href="#" class="delete-page"><img src="/themer_asset/images/delete.png" alt="delete" /></a>
  <p class="label"><?php echo $page['Label']; ?></p>
  <a href="#" class="edit-page page-button">Edit</a>
  <img src="/themer_asset/images/sort.png" alt="sort" class="sort" />

  <div class="clear"></div>

  <div class="page-inputs">
    <label for="page[<?php echo $i; ?>][Label]">Label</label>
    <input class="page-label" type="text" name="Pages[<?php echo $i; ?>][Label]" value="<?php echo $page['Label']; ?>" /><br />
  
    <label for="page[<?php echo $i; ?>][URL]">URL</label>
    <input class="page-url" type="text" name="Pages[<?php echo $i; ?>][URL]" value="<?php echo $page['URL']; ?>" />
  
    <a href="#" class="save-page page-button">Save</a>
  </div>

  <div class="clear"></div>
</div>
<?php endforeach; ?>