<article class="category" data-id="<?php echo $category_id; ?>" data-name="<?php echo functions::escape_html($name); ?>">
  <a class="link" href="<?php echo functions::escape_html($link); ?>" title="<?php echo $short_description ? functions::escape_html($short_description) : functions::escape_html($name); ?>">
    <img class="responsive hidden-xs hidden-sm" src="<?php echo document::href_rlink($image['thumbnail']); ?>" style="aspect-ratio: <?php echo $image['ratio']; ?>;" alt="<?php echo functions::escape_html($name); ?>" />

    <div class="caption">
      <h3><?php echo $name; ?></h3>
    </div>
  </a>
</article>
