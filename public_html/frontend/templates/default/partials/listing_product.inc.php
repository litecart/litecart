<article class="product">
  <a class="link" href="<?php echo functions::escape_html($link) ?>" title="{{name|escape}}" data-id="{{product_id}}" data-sku="{{sku|escape}}" data-name="{{name|escape}}" data-price="<?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>">

    <div class="image-wrapper">
      <img class="image img-responsive" src="<?php echo document::href_link(WS_DIR_STORAGE . $image['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_STORAGE . $image['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_STORAGE . $image['thumbnail_2x']); ?> 2x" alt="{{name|escape}}" style="aspect-ratio: <?php echo $image['ratio']; ?>;" />
      {{sticker}}
    </div>

    <div class="info">
      <div class="name">{{name}}</div>
      <div class="brand-name"><?php echo !empty($brand) ? $brand['name'] : '&nbsp;'; ?></div>
      <div class="price-wrapper">
        <?php if ($campaign_price) { ?>
        <del class="regular-price">{{regular_price|money}}</del> <strong class="campaign-price">{{campaign_price|money}}</strong>
        <?php } else { ?>
        <span class="price">{{regular_price|money}}</span>
        <?php } ?>
      </div>
    </div>
  </a>

  <button class="preview btn btn-default btn-sm" data-toggle="lightbox" data-target="<?php echo functions::escape_html($link) ?>" data-require-window-width="768" data-max-width="980">
    <?php echo functions::draw_fonticon('fa-search-plus'); ?>
  </button>
</article>
