<style>
#main {
  --app-color: <?php echo $theme['color']; ?>;
}
#top-bar {
  border-bottom: 5px solid var(--app-color);
}
</style>

<ul id="top-bar" class="hidden-print">
  <li>
    <div>
      <label class="nav-toggle" for="sidebar-compressed">
        <?php echo functions::draw_fonticon('fa-bars'); ?>
      </label>
    </div>
  </li>

  <li>
    {{breadcrumbs}}
  </li>

  <li style="flex-grow: 1;"></li>

  <li>
    <div class="btn-group btn-block btn-group-inline" data-toggle="buttons">
      <label class="btn btn-default btn-sm<?php echo empty($_COOKIE['dark_mode']) ? ' active' : ''; ?>"><input type="radio" name="dark_mode" value="0" <?php echo empty($_COOKIE['dark_mode']) ? ' checked ' : ''; ?>/> <?php echo language::translate('title_light', 'Light'); ?></label>
      <label class="btn btn-default btn-sm<?php echo !empty($_COOKIE['dark_mode']) ? ' active' : ''; ?>"><input type="radio" name="dark_mode" value="1" <?php echo !empty($_COOKIE['dark_mode']) ? ' checked ' : ''; ?>/> <?php echo language::translate('title_dark', 'Dark'); ?></label>
    </div>
  </li>

  <li class="language dropdown">
    <a href="#" data-toggle="dropdown" class="dropdown-toggle"><img src="<?php echo document::href_link(WS_DIR_APP . 'assets/languages/'. language::$selected['code'] .'.png'); ?>" alt="<?php echo language::$selected['code']; ?>" title="<?php echo htmlspecialchars(language::$selected['name']); ?>" style="max-height: 1em;" /> <b class="caret"></b></a>
    <ul class="dropdown-menu">
      <?php foreach (language::$languages as $language) { ?>
      <li>
        <a href="<?php echo document::href_ilink(null, ['language' => $language['code']]); ?>">
          <img src="<?php echo document::href_link(WS_DIR_APP . 'assets/languages/'. $language['code'] .'.png'); ?>" alt="<?php echo $language['code']; ?>" style="max-height: 1em;" /> <?php echo $language['name']; ?>
        </a>
      </li>
      <?php } ?>
    </ul>
  </li>

  <?php if ($webmail_link = settings::get('webmail_link', '')) { ?>
  <li>
    <a href="<?php echo $webmail_link; ?>" target="_blank" title="<?php echo language::translate('title_webmail', 'Webmail'); ?>">
      <?php echo functions::draw_fonticon('fa-envelope'); ?>
    </a>
  </li>
  <?php } ?>

  <?php if ($control_panel_link = settings::get('control_panel_link', '')) { ?>
  <li>
    <a href="<?php echo $control_panel_link; ?>" target="_blank" title="<?php echo language::translate('title_control_card', 'Control Panel'); ?>">
      <?php echo functions::draw_fonticon('fa-cogs'); ?>
    </a>
  </li>
  <?php } ?>

  <?php if ($database_admin_link = settings::get('database_admin_link')) { ?>
  <li>
    <a href="<?php echo $database_admin_link; ?>" target="_blank" title="<?php echo language::translate('title_database_manager', 'Database Manager'); ?>">
      <?php echo functions::draw_fonticon('fa-database'); ?>
    </a>
  </li>
  <?php } ?>

  <li>
    <a href="<?php echo document::href_ilink('f:'); ?>" title="<?php echo language::translate('title_frontend', 'Frontend'); ?>">
      <?php echo functions::draw_fonticon('fa-desktop'); ?> <?php echo language::translate('title_frontend', 'Frontend'); ?>
    </a>
  </li>

  <li>
    <a class="help" href="https://wiki.litecart.net/" target="_blank" title="<?php echo language::translate('title_help', 'Help'); ?>">
      <?php echo functions::draw_fonticon('fa-question-circle'); ?> <?php echo language::translate('title_help', 'Help'); ?>
    </a>
  </li>

  <li>
    <a href="<?php echo document::href_ilink('logout'); ?>" title="<?php echo language::translate('title_sign_out', 'Sign Out'); ?>">
      <?php echo functions::draw_fonticon('fa-sign-out'); ?> <?php echo language::translate('title_sign_out', 'Sign Out'); ?>
    </a>
  </li>
</ul>

<div id="content">
  {{notices}}
  {{content}}
</div>
