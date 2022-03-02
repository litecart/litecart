<section id="box-categories">

  <div class="listing categories">
    <?php foreach ($categories as $category) { ?>
      <article class="category" data-id="<?php echo $category['id']; ?>" data-name="<?php echo functions::escape_html($category['name']); ?>">
        <a class="link" href="<?php echo functions::escape_html($category['link']); ?>">
          <img class="img-responsive hidden-xs hidden-sm" src="<?php echo document::href_link(WS_DIR_STORAGE . $category['image']['thumbnail']); ?>" alt="" />

          <div class="caption">
            <div><?php echo $category['name']; ?></div>
            <div class="short-description"><?php echo $category['short_description']; ?></div>
          </div>
        </a>
      </article>
    <?php } ?>
  </div>

</section>