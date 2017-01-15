<div id="box-manufacturer-logotypes" class="box hidden-xs hidden-sm">
  <ul class="list-inline text-center">

    <?php foreach ($logotypes as $logotype) { ?>
    <li>
      <a href="<?php echo htmlspecialchars($logotype['link']); ?>">
        <img src="<?php echo htmlspecialchars($logotype['image']); ?>" alt="" title="<?php echo htmlspecialchars($logotype['title']); ?>" style="margin: 0px 15px;">
      </a>
    </li>
    <?php } ?>

  </ul>
</div>