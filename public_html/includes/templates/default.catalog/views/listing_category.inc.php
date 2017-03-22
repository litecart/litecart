<div class="category category-<?php echo $category_id; ?> col-xs-whole col-sm-halfs col-md-thirds">
  <a class="link shadow hover-light" href="<?php echo htmlspecialchars($link); ?>">
    <img src="<?php echo htmlspecialchars($image['thumbnail']); ?>" alt="" title="<?php echo htmlspecialchars($name); ?>" />
    <div class="caption">
      <h3><?php echo $name; ?></h3>
      <?php echo $short_description ? '<p>'.$short_description.'</p>' : $short_description; ?>
    </div>
  </a>
</div>
