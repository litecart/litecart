<section id="box-brand-logotypes" class="card hidden-xs hidden-sm text-center">
  <div class="card-body">
    <?php foreach ($logotypes as $logotype) { ?>
    <a href="<?php echo functions::escape_html($logotype['link']); ?>">
      <img src="<?php echo document::href_link($logotype['image']['thumbnail']); ?>" srcset="<?php echo document::href_link($logotype['image']['thumbnail']); ?> 1x, <?php echo document::href_link($logotype['image']['thumbnail_2x']); ?> 2x" alt="" title="<?php echo functions::escape_html($logotype['title']); ?>" style="margin: 0px 15px;">
    </a>
    <?php } ?>
  </div>
</section>

<script>
$('.rightArrow').click(function () {
  var leftPos = $('.innerWrapper').scrollLeft();
  $('.innerWrapper').animate({scrollLeft: leftPos + 200}, 800);
});
</script>
