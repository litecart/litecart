<article class="category" data-id="<?php echo $category_id; ?>" data-name="<?php echo htmlspecialchars($name); ?>">
  <a class="link" href="<?php echo htmlspecialchars($link); ?>">
    <img class="img-responsive hidden-xs hidden-sm" src="<?php echo document::href_link(WS_DIR_STORAGE . $image['thumbnail']); ?>" alt="" />

    <div class="caption">
      <div><?php echo $name; ?></div>
      <div class="short-description"><?php echo $short_description; ?></div>
    </div>
  </a>
</article>