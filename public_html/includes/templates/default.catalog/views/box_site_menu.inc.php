<?php
  if (!function_exists('custom_draw_site_menu_item')) {
    function custom_draw_site_menu_item($item, $indent=0) {

      if (!empty($item['subitems'])) {
        $output = '<li class="dropdown" data-type="'. $item['type'] .'" data-id="'. $item['id'] .'">'
                . '  <a href="'. htmlspecialchars($item['link']) .'" class="dropdown-toggle" data-toggle="dropdown">'. $item['title'] .' <b class="caret"></b></a>'
                . '  <ul class="dropdown-menu">' . PHP_EOL;

        foreach ($item['subitems'] as $subitem) {
          $output .= custom_draw_site_menu_item($subitem, $indent+1);
        }

        $output .= '  </ul>' . PHP_EOL
                 . '</li>' . PHP_EOL;

      } else {
        $output = '<li data-type="'. $item['type'] .'" data-id="'. $item['id'] .'">'
                . '  <a href="'. htmlspecialchars($item['link']) .'">'. $item['title'] .'</a>'
                . '</li>' . PHP_EOL;
      }

      return $output;
    }
  }
?>
<div id="site-menu">
  <nav class="navbar">

    <div class="navbar-header">
      <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search'), false, 'class="navbar-form"'); ?>
        <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' &hellip;"'); ?>
      <?php echo functions::form_draw_form_end(); ?>

      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#default-menu">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <div id="default-menu" class="navbar-collapse collapse">

      <ul class="nav navbar-nav">
        <li class="hidden-xs">
          <a href="<?php echo document::ilink(''); ?>" class="navbar-brand"><?php echo functions::draw_fonticon('fa-home'); ?></a>
        </li>

        <?php foreach ($categories as $item) echo custom_draw_site_menu_item($item); ?>

        <?php if ($manufacturers) { ?>
        <li class="manufacturers dropdown">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <?php foreach ($manufacturers as $item) echo custom_draw_site_menu_item($item); ?>
          </ul>
        </li>
        <?php } ?>

        <?php if ($pages) { ?>
        <li class="information dropdown">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_information', 'Information'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <?php foreach ($pages as $item) echo custom_draw_site_menu_item($item); ?>
          </ul>
        </li>
        <?php } ?>
      </ul>

      <ul class="nav navbar-nav navbar-right">
        <li class="account dropdown">
          <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo functions::draw_fonticon('fa-user'); ?> <?php echo !empty(customer::$data['id']) ? customer::$data['firstname'] : language::translate('title_sign_in', 'Sign In'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <?php if (!empty(customer::$data['id'])) { ?>
              <li><a href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
              <li><a href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
              <li><a href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
            <?php } else { ?>
              <li>
                <?php echo functions::form_draw_form_begin('login_form', 'post', document::ilink('login'), false, 'class="navbar-form" style="min-width: 300px;"'); ?>
                  <?php echo functions::form_draw_hidden_field('redirect_url', !empty($_GET['redirect_url']) ? $_GET['redirect_url'] : document::link()); ?>

                  <div class="form-group">
                    <?php echo functions::form_draw_email_field('email', true, 'required="required" placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
                  </div>

                  <div class="form-group">
                    <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
                  </div>

                  <div class="btn-group btn-block">
                    <?php echo functions::form_draw_button('login', language::translate('title_sign_in', 'Sign In')); ?>
                  </div>
                <?php echo functions::form_draw_form_end(); ?>
              </li>
              <li class="text-center">
                <a href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('text_new_customers_click_here', 'New customers click here'); ?></a>
              </li>

              <li class="text-center">
                <a href="<?php echo document::href_ilink('reset_password'); ?>"><?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?></a>
              </li>
            <?php } ?>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</div>
