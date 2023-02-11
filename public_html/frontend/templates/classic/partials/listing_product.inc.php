<article class="product">
  <a class="link" href="<?php echo functions::escape_html($link) ?>" title="<?php echo functions::escape_html($name); ?>" data-id="<?php echo $product_id; ?>" data-sku="<?php echo functions::escape_html($sku); ?>" data-name="<?php echo functions::escape_html($name); ?>" data-price="<?php echo currency::format_raw($final_price); ?>">

    <div class="image-wrapper">
      <img class="image img-responsive" src="<?php echo document::href_rlink(FS_DIR_STORAGE . $image['thumbnail']); ?>" srcset="<?php echo document::href_rlink(FS_DIR_STORAGE . $image['thumbnail']); ?> 1x, <?php echo document::href_rlink(FS_DIR_STORAGE . $image['thumbnail_2x']); ?> 2x" style="aspect-ratio: <?php echo $image['ratio']; ?>;" alt="<?php echo functions::escape_html($name); ?>" />
      <?php echo $sticker; ?>
    </div>

    <div class="info">
      <h4 class="name"><?php echo $name; ?></h4>
      <div class="manufacturer-name"><?php echo !empty($manufacturer['name']) ? $manufacturer['name'] : '&nbsp;'; ?></div>
      <div class="description"><?php echo $short_description; ?></div>
      <div class="price-wrapper">
        <?php if ($campaign_price) { ?>
        <del class="regular-price"><?php echo currency::format($regular_price); ?></del> <strong class="campaign-price"><?php echo currency::format($campaign_price); ?></strong>
        <?php } else { ?>
        <span class="price"><?php echo currency::format($regular_price); ?></span>
        <?php } ?>
      </div>
    </div>
  </a>

  <button class="preview btn btn-default btn-sm" data-toggle="lightbox" data-target="<?php echo functions::escape_html($link) ?>" data-seamless="true" data-require-window-width="768" data-max-width="980">
    <?php echo functions::draw_fonticon('fa-search-plus'); ?>
  </button>
</article>
