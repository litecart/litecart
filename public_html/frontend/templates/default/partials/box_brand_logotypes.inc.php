<section id="box-brand-logotypes" class="card hidden-xs hidden-sm text-center" style="margin-bottom: 2em;">
  <div class="card-body">
    <?php foreach ($logotypes as $logotype) { ?>
    <a href="<?php echo functions::escape_html($logotype['link']); ?>">
      <?php echo functions::draw_thumbnail($brand['image'], 240, 80, '', 'alt="'. functions::escape_html($brand['name']) .'" style="margin: 0px 15px;"'); ?>
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
