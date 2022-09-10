<section id="box-brand-logotypes" class="card hidden-xs hidden-sm text-center" style="margin-bottom: 2em;">
  <div class="card-body">
    <?php foreach ($logotypes as $logotype) { ?>
    <a href="<?php echo functions::escape_html($logotype['link']); ?>">
      <img src="<?php echo document::href_rlink($logotype['image']['thumbnail']); ?>" srcset="<?php echo document::href_rlink($logotype['image']['thumbnail']); ?> 1x, <?php echo document::href_rlink($logotype['image']['thumbnail_2x']); ?> 2x" alt="" title="<?php echo functions::escape_html($logotype['title']); ?>" style="margin: 0px 15px;">
    </a>
    <?php } ?>
  </div>
</section>

<script>
$('.rightArrow').click(function () {
  let leftPos = $('.innerWrapper').scrollLeft();
  $('.innerWrapper').animate({scrollLeft: leftPos + 200}, 800);
});
</script>
