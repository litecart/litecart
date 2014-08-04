<nav class="pagination">
  <ul class="list-horizontal">
    <?php foreach($items as $item) { ?>
    <li><a class="page button<?php if ($item['disabled']) echo ' disabled'; ?>" href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo $item['title']; ?></a></li>
    <?php } ?>
  </ul>
</nav>