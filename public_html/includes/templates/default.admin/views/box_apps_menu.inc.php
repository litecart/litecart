<ul id="box-apps-menu">

  <?php foreach ($apps as $app) { ?>
  <li class="app<?php echo $app['active'] ? ' selected' : ''; ?>" data-code="<?php echo $app['code']; ?>">
    <a href="<?php echo functions::escape_html($app['link']); ?>">
      <span class="fa-stack fa-lg icon-wrapper" title="<?php echo functions::escape_html($app['name']); ?>">
        <?php echo functions::draw_fonticon('fa-circle fa-stack-2x icon-background', 'style="color: '. $app['theme']['color'] .';"'); ?>
        <?php echo functions::draw_fonticon($app['theme']['icon'] .' fa-stack-1x icon', 'style="color: #fff;"'); ?>
      </span>
      <span class="name"><?php echo $app['name']; ?></span>
    </a>

    <?php if ($app['active'] && !empty($app['menu'])) { ?>
    <ul class="docs">
      <?php foreach ($app['menu'] as $item) { ?>
      <li class="doc<?php echo $item['active'] ? ' selected' : ''; ?>" data-code="<?php echo $item['doc']; ?>">
        <a href="<?php echo functions::escape_html($item['link']); ?>">
          <span class="name"><?php echo $item['title']; ?></span>
        </a>
      </li>
      <?php } ?>
    </ul>
    <?php } ?>
  </li>
  <?php } ?>

</ul>
