<article class="category" data-id="{{category_id}}" data-name="{{name|escape}}">
  <a class="link" href="{{link|escape}}">
    <img class="img-responsive hidden-xs hidden-sm" src="<?php echo document::href_link($image['thumbnail']); ?>" alt="" />

    <div class="caption">
      <div>{{name}}</div>
      <div class="short-description">{{short_description}}</div>
    </div>
  </a>
</article>