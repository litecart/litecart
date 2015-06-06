<li class="category shadow hover-light">
  <a class="link" href="<?php echo htmlspecialchars($link); ?>" title="<?php echo htmlspecialchars($name); ?>">
    <div class="image-wrapper">
      <img src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($name); ?>" />
      <div class="footer">
        <div class="title"><?php echo $name; ?></div>
        <div class="description"><?php echo $short_description; ?></div>
      </div>
    </div>
  </a>
</li>