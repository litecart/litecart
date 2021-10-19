<section id="box-brand-logotypes" class="box box-default hidden-xs hidden-sm text-center">
  <?php foreach ($logotypes as $logotype) { ?>
  <a href="<?php echo htmlspecialchars($logotype['link']); ?>">
    <img src="<?php echo document::href_link($logotype['image']['thumbnail']); ?>" srcset="<?php echo document::href_link($logotype['image']['thumbnail']); ?> 1x, <?php echo document::href_link($logotype['image']['thumbnail_2x']); ?> 2x" alt="" title="<?php echo htmlspecialchars($logotype['title']); ?>" style="margin: 0px 15px;">
  </a>
  <?php } ?>
</section>

<script>
$('.rightArrow').click(function () {
  var leftPos = $('.innerWrapper').scrollLeft();
  $('.innerWrapper').animate({scrollLeft: leftPos + 200}, 800);
});
</script>
