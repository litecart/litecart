<section id="box-brand-logotypes" class="box hidden-xs hidden-sm">
  <ul class="list-inline text-center">
    <?php foreach ($logotypes as $logotype) { ?>
    <li>
      <a href="<?php echo htmlspecialchars($logotype['link']); ?>">
        <img src="<?php echo document::href_link(WS_DIR_STORAGE . $logotype['image']['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_STORAGE . $logotype['image']['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_STORAGE . $logotype['image']['thumbnail_2x']); ?> 2x" alt="" title="<?php echo htmlspecialchars($logotype['title']); ?>" style="margin: 0px 15px;">
      </a>
    </li>
    <?php } ?>
  </ul>
</section>