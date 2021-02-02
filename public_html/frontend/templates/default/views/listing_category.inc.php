<article class="category" data-id="<?php echo $category_id; ?>" data-name="<?php echo htmlspecialchars($name); ?>">
  <a class="link" href="<?php echo htmlspecialchars($link); ?>" title="<?php echo $short_description ? htmlspecialchars($short_description) : htmlspecialchars($name); ?>">
    <img class="img-responsive hidden-xs hidden-sm" src="<?php echo document::href_link(WS_DIR_STORAGE . $image['thumbnail']); ?>" alt="" />

    <div class="caption">
      <h3><?php echo $name; ?></h3>
    </div>
  </a>
</article>