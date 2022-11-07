<ul class="pagination">
  <?php foreach ($items as $item) { ?>
    <?php if ($item['disabled']) { ?>
    <li data-page="<?php echo $item['page']; ?>" class="disabled"><span><?php echo $item['title']; ?></span></li>
    <?php } else { ?>
    <li data-page="<?php echo $item['page']; ?>"<?php if ($item['active']) echo ' class="active"'; ?>><a href="<?php echo functions::escape_html($item['link']); ?>"><?php echo $item['title']; ?></a></li>
    <?php } ?>
  <?php } ?>
</ul>
