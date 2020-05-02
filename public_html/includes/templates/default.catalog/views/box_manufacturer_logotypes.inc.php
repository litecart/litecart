<style>
#box-manufacturer-logotypes {
  background: #fff;
  padding: 1rem;
  border-radius: var(--border-radius);
  column-count: 4;
  column-gap: 2rem;
}
</style>

<section id="box-manufacturer-logotypes" class="box hidden-xs hidden-sm">
  <?php foreach ($logotypes as $logotype) { ?>
  <a href="<?php echo htmlspecialchars($logotype['link']); ?>">
    <img src="<?php echo document::href_link(WS_DIR_APP . $logotype['image']['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_APP . $logotype['image']['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_APP . $logotype['image']['thumbnail_2x']); ?> 2x" alt="" title="<?php echo htmlspecialchars($logotype['title']); ?>" style="margin: 0px 15px;">
  </a>
  <?php } ?>
</section>