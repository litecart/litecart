<?php if ($listing_type == 'column') { ?>
  <li class="product column shadow hover-light">
    <a class="link" href="<?php echo htmlspecialchars($link) ?>" title="<?php echo htmlspecialchars($name); ?>">
      <div class="image-wrapper">
        <img class="image" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($name); ?>" />
        <?php echo $sticker; ?>
      </div>
      <div class="name"><?php echo $name; ?></div>
      <div class="manufacturer"><?php echo $manufacturer_name ? $manufacturer_name : '&nbsp;'; ?></div>
      <div class="price-wrapper">
        <?php if ($campaign_price) { ?>
        <s class="regular-price"><?php echo $price; ?></s> <strong class="campaign-price"><?php echo $campaign_price; ?></strong>
        <?php } else { ?>
        <span class="price"><?php echo $price; ?></span>
        <?php } ?>
      </div>
    </a>
    <?php if ($image) { ?>
    <a href="<?php echo htmlspecialchars($image['original']); ?>" class="lightbox zoomable" data-lightbox-group="product-listing" title="<?php echo htmlspecialchars($name); ?>" style="position: absolute; top: 15px; right: 15px; color: inherit;"><?php echo functions::draw_fonticon('fa-search'); ?></a>
    <?php } ?>
  </li>
<?php } else if ($listing_type == 'row') { ?>
  <li class="product row shadow hover-light">
    <a class="link" href="<?php echo htmlspecialchars($link) ?>" title="<?php echo htmlspecialchars($name); ?>">
      <div class="image-wrapper">
        <img class="image" src="<?php echo htmlspecialchars($image['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($image['thumbnail']); ?> 1x, <?php echo htmlspecialchars($image['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($name); ?>" />
        <?php echo $sticker; ?>
      </div>
      <div class="name"><?php echo $name; ?></div>
      <div class="manufacturer"><?php echo $manufacturer_name ? $manufacturer_name : '&nbsp;'; ?></div>
      <div class="description"><?php echo $short_description; ?></div>
      <div class="price-wrapper">
        <?php if ($campaign_price) { ?>
        <s class="regular-price"><?php echo $price; ?></s> <strong class="campaign-price"><?php echo $campaign_price; ?></strong>
        <?php } else { ?>
        <span class="price"><?php echo $price; ?></span>
        <?php } ?>
      </div>
    </a>
    <?php if ($image) { ?>
    <a href="<?php echo htmlspecialchars($image['original']); ?>" class="lightbox zoomable" data-lightbox-group="product-listing" title="<?php echo htmlspecialchars($name); ?>" style="position: absolute; top: 15px; right: 15px; color: inherit;"><?php echo functions::draw_fonticon('fa-search'); ?></a>
    <?php } ?>
  </li>
<?php } else trigger_error('Unknown product listing type definition ('. $listing_type .')', E_USER_WARNING); ?>