<section id="box-manufacturer-logotypes" class="card hidden-xs hidden-sm">
  <div class="card-body">
    <ul class="list-inline text-center">
      <?php foreach ($logotypes as $logotype) { ?>
      <li>
        <a href="<?php echo functions::escape_html($logotype['link']); ?>">
          <img src="<?php echo document::href_link(WS_DIR_APP . $logotype['image']['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_APP . $logotype['image']['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_APP . $logotype['image']['thumbnail_2x']); ?> 2x" alt="" title="<?php echo functions::escape_html($logotype['title']); ?>" style="margin: 0px 15px;">
        </a>
      </li>
      <?php } ?>
    </ul>
  </div>
</section>