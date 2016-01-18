<nav class="pagination">
  <ul class="list-horizontal">
    <?php foreach($items as $item) { ?>
    <?php if ($item['disabled']) { ?>
    <li><span class="page button disabled"><?php echo $item['title']; ?></span></li>
    <?php } else { ?>
    <li><a class="page button<?php if ($item['active']) echo ' active'; ?>" href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo $item['title']; ?></a></li>
    <?php } ?>
    <?php } ?>
  </ul>
</nav>