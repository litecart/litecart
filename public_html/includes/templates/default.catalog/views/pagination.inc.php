<ul class="pagination">
  <?php foreach ($items as $item) { ?>
    <?php if ($item['disabled']) { ?>
    <li data-page="<?php echo $item['page']; ?>" class="disabled"><span><?php echo $item['title']; ?></span></li>
    <?php } else { ?>
    <li data-page="<?php echo $item['page']; ?>"<?php if ($item['active']) echo ' class="active"'; ?>><a href="<?php echo htmlspecialchars($item['link']); ?>"><?php echo $item['title']; ?></a></li>
    <?php } ?>
  <?php } ?>
</ul>

<script>
  $('body').on('click', '.pagination a', function(e){
    e.preventDefault();
    var container = '#'+$(this).closest('[id]').attr('id');
    $(container).load($(this).attr('href') + ' ' + container, function(){
      $(document).scrollTop(1);
    });
  });
</script>
