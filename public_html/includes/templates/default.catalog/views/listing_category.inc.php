<article class="category" data-id="<?php echo $category_id; ?>" data-name="<?php echo functions::escape_html($name); ?>">
  <a class="link" href="<?php echo functions::escape_html($link); ?>">
    <img class="img-responsive hidden-xs hidden-sm" src="<?php echo document::href_link(WS_DIR_APP . $image['thumbnail']); ?>" style="aspect-ratio: <?php echo $image['ratio']; ?>;" alt="" />

    <div class="caption">
      <h3><?php echo $name; ?></h3>
      <div class="short-description"><?php echo $short_description; ?></div>
    </div>
  </a>
</article>