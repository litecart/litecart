<?php if ($listing_type == 'column') { ?>
  <li class="product column shadow hover-light">
    <a class="link" href="<?php echo htmlspecialchars($link) ?>" title="<?php echo htmlspecialchars($name); ?>">
      <div class="image-wrapper" style="position: relative;">
        <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($name); ?>" width="100" />
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
    <a href="<?php echo htmlspecialchars($image); ?>" class="fancybox" data-fancybox-group="product-listing" title="<?php echo htmlspecialchars($name); ?>"><img src="<?php echo htmlspecialchars($preview_icon); ?>" alt="" width="16" height="16" class="zoomable" style="position: absolute; top: 15px; right: 15px;" /></a>
    <?php } ?>
  </li>
<?php } else if ($listing_type == 'row') { ?>
  <li class="product row shadow hover-light">
    <a class="link" href="<?php echo htmlspecialchars($link) ?>" title="<?php echo htmlspecialchars($name); ?>">
      <div class="image-wrapper" style="position: relative;">
        <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($name); ?>" width="100" />
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
    <a href="<?php echo htmlspecialchars($image); ?>" class="fancybox" data-fancybox-group="product-listing" title="<?php echo htmlspecialchars($name); ?>"><img src="<?php echo htmlspecialchars($preview_icon); ?>" alt="" width="16" height="16" class="zoomable" style="position: absolute; top: 15px; right: 15px;" /></a>
    <?php } ?>
<?php } ?>