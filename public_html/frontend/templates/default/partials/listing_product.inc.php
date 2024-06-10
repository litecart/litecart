<article class="product" data-id="{{product_id}}" data-sku="{{sku|escape}}" data-name="{{name|escape}}" data-price="<?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>">
  <a class="link" href="<?php echo functions::escape_html($link) ?>">

    <div class="image-wrapper">
      <?php echo functions::draw_thumbnail($image, 200, 0, 'product', 'loading="lazy" alt="'. functions::escape_attr($name) .'"'); ?>
      {{sticker}}
    </div>

    <div class="info">
      <div class="name">{{name}}</div>

      <div class="price-wrapper">
        <?php if ($campaign_price) { ?>
        <del class="regular-price">{{regular_price|money}}</del> <strong class="campaign-price">{{campaign_price|money}}</strong>
        <?php } else { ?>
        <span class="price">{{regular_price|money}}</span>
        <?php } ?>
      </div>
    </div>
  </a>

  <div class="actions">
    <button class="preview btn btn-default btn-sm" data-toggle="lightbox" data-target="<?php echo functions::escape_html($link) ?>" data-seamless="true" data-require-window-width="768" data-max-width="980">
      <?php echo functions::draw_fonticon('fa-search-plus'); ?>
    </button>

    <button name="add_cart_product" class="preview btn btn-success btn-sm">
      <?php echo functions::draw_fonticon('fa-shopping-cart'); ?>
    </button>
  </div>
</article>
