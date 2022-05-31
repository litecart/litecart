<article class="category" data-id="{{category_id}}" data-name="{{name|escape}}">
  <a class="link" href="{{link|escape}}">
    <img class="<?php echo $image['viewport']['clipping']; ?>" src="<?php echo document::href_rlink(FS_DIR_STORAGE . $image['thumbnail']); ?>" alt="" style="aspect-ratio: <?php echo $image['viewport']['ratio']; ?>;" />

    <div class="caption">
      <div>{{name}}</div>
      <div class="short-description">{{short_description}}</div>
    </div>
  </a>
</article>