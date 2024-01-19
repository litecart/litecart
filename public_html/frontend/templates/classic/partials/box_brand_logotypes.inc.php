<section id="box-brand-logotypes" class="card hidden-xs hidden-sm">
  <div class="card-body">
    <ul class="list-inline text-center">
      <?php foreach ($brands as $brand) { ?>
      <li>
        <a href="<?php echo functions::escape_html($brand['link']); ?>">
          <?php echo functions::draw_thumbnail($brand['image'], 240, 80, '', 'alt="'. functions::escape_html($brand['name']) .'" style="margin: 0px 15px;"'); ?>
        </a>
      </li>
      <?php } ?>
    </ul>
  </div>
</section>