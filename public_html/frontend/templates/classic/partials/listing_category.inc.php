<article class="category" data-id="<?php echo $category_id; ?>" data-name="<?php echo functions::escape_html($name); ?>">
  <a class="link" href="<?php echo functions::escape_html($link); ?>">
    <?php echo functions::draw_thumbnail($image, 320, 0, 'category', 'alt="'. functions::escape_html($name) .'"'); ?>
    <div class="caption">
      <h3 class="name"><?php echo functions::escape_html($name); ?></h3>
      <div class="short-description"><?php echo functions::escape_html($short_description); ?></div>
    </div>
  </a>
</article>
