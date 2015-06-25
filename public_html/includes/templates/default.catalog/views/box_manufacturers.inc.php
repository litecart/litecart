<div id="box-manufacturers" class="box">
  <h1 class="title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h1>
  <div class="content">
    <ul class="listing-wrapper" class="manufacturers">
      <?php foreach($manufacturers as $manufacturer) { ?>
      <li class="manufacturer shadow hover-light">
        <a class="link" href="<?php echo htmlspecialchars($manufacturer['link']); ?>" title="<?php echo htmlspecialchars($manufacturer['name']); ?>">
          <div class="image-wrapper">
            <img src="<?php echo htmlspecialchars($manufacturer['image']['thumbnail']); ?>" srcset="<?php echo htmlspecialchars($manufacturer['image']['thumbnail']); ?> 1x, <?php echo htmlspecialchars($manufacturer['image']['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($manufacturer['name']); ?>" />
          </div>
          <div class="title"><?php echo $manufacturer['name']; ?></div>
        </a>
      </li>
      <?php } ?>
    </ul>
  </div>
</div>