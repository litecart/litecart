<ul id="box-apps-menu">

  <?php foreach ($apps as $app) { ?>
  <li class="app<?php echo $app['active'] ? ' selected' : ''; ?>" data-code="<?php echo $app['code']; ?>" style="--app-color: <?php echo $app['theme']['color']; ?>;">
    <a href="<?php echo htmlspecialchars($app['link']); ?>">
      <span class="app-icon" title="<?php echo htmlspecialchars($app['name']); ?>">
        <?php echo functions::draw_fonticon($app['theme']['icon'] .' fa-fw'); ?>
      </span>
      <span class="name"><?php echo $app['name']; ?></span>
    </a>

    <?php if ($app['active'] && !empty($app['menu'])) { ?>
    <ul class="docs">
      <?php foreach ($app['menu'] as $item) { ?>
      <li class="doc<?php echo $item['active'] ? ' selected' : ''; ?>" data-code="<?php echo $item['doc']; ?>">
        <a href="<?php echo htmlspecialchars($item['link']); ?>">
          <?php echo functions::draw_fonticon('fa-angle-right'); ?> <span class="name"><?php echo $item['title']; ?></span>
        </a>
      </li>
      <?php } ?>
    </ul>
    <?php } ?>
  </li>
  <?php } ?>

</ul>
