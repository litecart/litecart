<article class="category" data-id="{{category_id}}" data-name="{{name|escape}}">
  <a class="link" href="{{link|escape}}">
    <?php echo functions::draw_thumbnail($image, 480, 0, 'category', 'alt="'. functions::escape_html($name) .'"'); ?>
    <div class="caption">
      <div>{{name}}</div>
      <div class="short-description">{{short_description}}</div>
    </div>
  </a>
</article>