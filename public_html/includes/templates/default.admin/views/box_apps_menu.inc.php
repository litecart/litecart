<div id="box-apps-menu-wrapper">
  <ul id="box-apps-menu" class="list-vertical">
    <?php foreach ($apps as $app) { ?>
      <li id="app-<?php $app['code']; ?>"<?php echo $app['active'] ? ' class="selected"' : ''; ?>>
        <a href="<?php echo htmlspecialchars($app['link']); ?>">
        <span class="fa-stack fa-lg icon-wrapper">
          <?php echo functions::draw_fonticon('fa-circle fa-stack-2x icon-background', 'style="color: '. $app['theme']['color'] .';"'); ?>
          <?php echo functions::draw_fonticon($app['theme']['icon'] .' fa-stack-1x icon', 'style="color: #fff;"'); ?>
        </span>
        <span class="name"><?php echo $app['name']; ?></span>
      </a>

      <?php if ($app['active'] && !empty($app['menu'])) { ?>
      <ul class="docs">
        <?php foreach ($app['menu'] as $item) { ?>
        <li id="doc-<?php echo $item['doc']; ?>"<?php echo $item['active'] ? ' class="selected"' : ''; ?>><a href="<?php echo htmlspecialchars($item['link']); ?>"><span class="name"><?php echo $item['title']; ?></span></a></li>
        <?php } ?>
      </ul>
      <?php } ?>

    </li>
  <?php } ?>
  </ul>
</div>