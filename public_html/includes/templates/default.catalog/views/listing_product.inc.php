<?php if ($listing_type == 'column') { ?>
  <div class="col-xs-6 col-sm-4 col-md-3">
    <div class="product column shadow hover-light">
      <a class="link"<?php echo !empty(document::$settings['product_modal_window']) ? ' data-toggle="lightbox" data-require-window-width="768"' : ''; ?> href="<?php echo htmlspecialchars($link) ?>" title="<?php echo htmlspecialchars($name); ?>">
        <div class="image-wrapper">
          <img class="image img-responsive" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($name); ?>" />
          <?php echo $sticker; ?>
        </div>
        <div class="name"><?php echo $name; ?></div>
        <div class="manufacturer"><?php echo !empty($manufacturer) ? $manufacturer['name'] : '&nbsp;'; ?></div>
        <div class="price-wrapper">
          <?php if ($campaign_price) { ?>
          <s class="regular-price"><?php echo $price; ?></s> <strong class="campaign-price"><?php echo $campaign_price; ?></strong>
          <?php } else { ?>
          <span class="price"><?php echo $price; ?></span>
          <?php } ?>
        </div>
      </a>
    </div>
  </div>
<?php } else if ($listing_type == 'row') { ?>
  <div class="col-xs">
    <div class="product shadow hover-light">
      <a class="link"<?php echo !empty(document::$settings['product_modal_window']) ? ' data-toggle="lightbox" data-require-window-width="768"' : ''; ?> href="<?php echo htmlspecialchars($link) ?>" title="<?php echo htmlspecialchars($name); ?>">
        <div class="image-wrapper">
          <img class="image" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($name); ?>" />
          <?php echo $sticker; ?>
        </div>
        <div class="info">
          <div class="name"><?php echo $name; ?></div>
          <div class="manufacturer"><?php echo !empty($manufacturer) ? $manufacturer['name'] : '&nbsp;'; ?></div>
          <div class="description"><?php echo $short_description; ?></div>
          <div class="price-wrapper">
            <?php if ($campaign_price) { ?>
            <s class="regular-price"><?php echo $price; ?></s> <strong class="campaign-price"><?php echo $campaign_price; ?></strong>
            <?php } else { ?>
            <span class="price"><?php echo $price; ?></span>
            <?php } ?>
          </div>
        </div>
      </a>
    </div>
  </div>
<?php } else trigger_error('Unknown product listing type definition ('. $listing_type .')', E_USER_WARNING); ?>