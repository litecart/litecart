<li class="category shadow hover-light">
  <a class="link" href="<?php echo htmlspecialchars($link); ?>" title="<?php echo htmlspecialchars($name); ?>">
    <div class="image-wrapper" style="position: relative;">
      <div class="image" style="position: relative;">
    </div>
    <img src="<?php echo htmlspecialchars($image); ?>" width="340" height="180" alt="<?php echo htmlspecialchars($name); ?>" />
      <div class="footer" style="position: absolute; bottom: 0;">
        <div class="title"><?php echo $name; ?></div>
        <div class="description"><?php echo $short_description; ?></div>
      </div>
    </div>
  </a>
</li>