<ul class="pagination">
  <?php foreach($items as $item) { ?>
    <?php if ($item['disabled']) { ?>
    <li class="disabled"><span><?php echo $item['title']; ?></span></li>
    <?php } else { ?>
    <li<?php if ($item['active']) echo ' class="active"'; ?>><a href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo $item['title']; ?></a></li>
    <?php } ?>
  <?php } ?>
</ul>
