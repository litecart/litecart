<ul id="box-apps-menu">

  <?php foreach ($apps as $app) { ?>
  <li class="app<?php echo $app['active'] ? ' active' : ''; ?>" data-code="<?php echo $app['code']; ?>" style="--app-color: <?php echo $app['theme']['color']; ?>;">
    <a href="<?php echo functions::escape_html($app['link']); ?>" data-toggle="ajax-load">
      <span class="app-icon" title="<?php echo functions::escape_html($app['name']); ?>">
        <?php echo functions::draw_fonticon($app['theme']['icon'] .' fa-fw'); ?>
      </span>
      <span class="name"><?php echo $app['name']; ?></span>
    </a>

    <?php if (!empty($app['menu'])) { ?>
    <ul class="docs">
      <?php foreach ($app['menu'] as $item) { ?>
      <li class="doc<?php echo $item['active'] ? ' active' : ''; ?>" data-id="<?php echo $item['doc']; ?>">
        <a href="<?php echo functions::escape_html($item['link']); ?>">
          <?php echo functions::draw_fonticon((language::$selected['direction'] == 'rtl') ? 'fa-angle-left' : 'fa-angle-right'); ?> <span class="name"><?php echo $item['title']; ?></span>
        </a>
      </li>
      <?php } ?>
    </ul>
    <?php } ?>
  </li>
  <?php } ?>

</ul>
