<div class="col-xs-12 col-sm-6 col-md-4">
  <div class="category" data-id="<?php echo $category_id; ?>" data-name="<?php echo htmlspecialchars($name); ?>">
    <a class="link shadow hover-light" href="<?php echo htmlspecialchars($link); ?>" title="<?php echo htmlspecialchars($name); ?>">
      <img src="<?php echo htmlspecialchars($image['thumbnail']); ?>" alt="" />
      <div class="caption">
        <h3><?php echo $name; ?></h3>
        <?php echo $short_description ? '<p>'.$short_description.'</p>' : $short_description; ?>
      </div>
    </a>
  </div>
</div>