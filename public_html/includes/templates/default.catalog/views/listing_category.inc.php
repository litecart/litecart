<article class="category" data-id="<?php echo $category_id; ?>" data-name="<?php echo functions::escape_html($name); ?>">
  <a class="link" href="<?php echo functions::escape_html($link); ?>">
    <img class="img-responsive hidden-xs hidden-sm" src="<?php echo document::href_rlink(FS_DIR_STORAGE . $image['thumbnail']); ?>"<?php echo settings::get('image_lazyload') ? ' loading="lazy"' : ''; ?> style="aspect-ratio: <?php echo $image['ratio']; ?>;" alt="">

    <div class="caption">
      <div class="name"><?php echo $name; ?></div>
      <div class="short-description"><?php echo $short_description; ?></div>
    </div>
  </a>
</article>