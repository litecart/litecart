<?php if ($listing_type == 'column') { ?>
  <div class="col-xs-6 col-sm-4 col-md-3">
    <div class="product column hover-light" data-id="<?php echo $product_id; ?>" data-name="<?php echo htmlspecialchars($name); ?>" data-price="<?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>">
      <a class="link"<?php echo !empty(document::$settings['product_modal_window']) ? ' data-toggle="lightbox" data-require-window-width="768"' : ''; ?> href="<?php echo htmlspecialchars($link) ?>">
        <div class="image-wrapper">
          <img class="image img-responsive" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($name); ?>" />
          <?php echo $sticker; ?>
        </div>
        <div class="name"><?php echo $name; ?></div>
        <div class="manufacturer"><?php echo !empty($manufacturer) ? $manufacturer['name'] : '&nbsp;'; ?></div>
        <div class="price-wrapper">
          <?php if ($campaign_price) { ?>
          <s class="regular-price"><?php echo currency::format($regular_price); ?></s> <strong class="campaign-price"><?php echo currency::format($campaign_price); ?></strong>
          <?php } else { ?>
          <span class="price"><?php echo currency::format($regular_price); ?></span>
          <?php } ?>
        </div>
      </a>
    </div>
  </div>
<?php } else if ($listing_type == 'row') { ?>
  <div class="col-xs-12">
    <div class="product hover-light" data-id="<?php echo $product_id; ?>" data-name="<?php echo htmlspecialchars($name); ?>" data-price="<?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>">
      <a class="link"<?php echo !empty(document::$settings['product_modal_window']) ? ' data-toggle="lightbox" data-require-window-width="768"' : ''; ?> href="<?php echo htmlspecialchars($link) ?>">
        <div class="image-wrapper">
          <img class="image" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($name); ?>" />
          <?php echo $sticker; ?>
        </div>
        <div class="info">
          <div class="name"><?php echo $name; ?></div>
          <p class="description"><?php echo $short_description; ?></p>
          <div class="manufacturer"><?php echo !empty($manufacturer) ? $manufacturer['name'] : '&nbsp;'; ?></div>
        </div>
        <div class="price-wrapper">
          <?php if ($campaign_price) { ?>
          <s class="regular-price"><?php echo currency::format($regular_price); ?></s> <strong class="campaign-price"><?php echo currency::format($campaign_price); ?></strong>
          <?php } else { ?>
          <span class="price"><?php echo currency::format($regular_price); ?></span>
          <?php } ?>
        </div>
      </a>
    </div>
  </div>
<?php } else trigger_error('Unknown product listing type definition ('. $listing_type .')', E_USER_WARNING); ?>