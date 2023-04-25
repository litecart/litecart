<div class="navbar navbar-sticky">

  <div class="navbar-header">
    <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
      <img src="<?php echo document::href_link('images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
    </a>

    <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search'), false, 'class="navbar-search"'); ?>
      <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' &hellip;"'); ?>
    <?php echo functions::form_draw_form_end(); ?>

    <div class="quick-access">
      <a class="regional-setting text-center" href="<?php echo document::href_ilink('regional_settings'); ?>#box-regional-settings" data-toggle="lightbox" data-seamless="true">
        <div class="navbar-icon"><?php echo functions::draw_fonticon('fa-globe'); ?></div>
        <small class="hidden-xs"><?php echo language::$selected['code']; ?> / <?php echo customer::$data['country_code']; ?> / <?php echo currency::$selected['code']; ?></small>
      </a>

      <?php if (settings::get('accounts_enabled')) { ?>
      <a class="account text-center" href="<?php echo document::href_ilink('edit_account'); ?>">
        <div class="navbar-icon"><?php echo functions::draw_fonticon('fa-user-o'); ?></div>
        <small class="hidden-xs"><?php echo language::translate('title_account', 'Account'); ?></small>
      </a>
      <?php } ?>

      <?php if (settings::get('store_phone')) { ?>
      <a class="phone text-center" href="tel:<?php echo settings::get('store_phone'); ?>">
        <div class="navbar-icon"><?php echo functions::draw_fonticon('fa-phone'); ?></div>
        <small class="hidden-xs"><?php echo (strlen(settings::get('store_phone')) < 10) ? settings::get('store_phone') : language::translate('title_call_us', 'Call Us'); ?></small>
      </a>
      <?php } ?>

      <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_cart.inc.php'); ?>

      <button type="button" class="btn btn-default navbar-toggler hidden-md hidden-lg hidden-xl hidden-xxl" data-toggle="offcanvas" data-target="#offcanvas">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>
  </div>

  <div id="offcanvas" class="offcanvas">
    <div class="offcanvas-header">
      <div class="offcanvas-title"><?php echo settings::get('store_name'); ?></div>
      <button type="button" class="btn btn-default" data-toggle="dismiss"><?php echo functions::draw_fonticon('fa-times'); ?></button>
    </div>

    <div class="offcanvas-body">
      <ul class="navbar-nav">

        <li class="nav-item">
          <a class="nav-link" href="<?php echo document::href_ilink(''); ?>"><?php echo functions::draw_fonticon('fa-home hidden-xs hidden-sm'); ?> <span class="hidden-md hidden-lg hidden-xl hidden-xxl"><?php echo language::translate('title_home', 'Home'); ?></span></a>
        </li>

        <?php if ($categories) { ?>
        <li class="nav-item categories dropdown">
          <a class="nav-link" href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_categories', 'Categories'); ?></a>
          <ul class="dropdown-menu">
            <?php foreach ($categories as $item) { ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo functions::escape_html($item['link']); ?>"><?php echo $item['title']; ?></a></li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

        <?php if ($manufacturers) { ?>
        <li class="nav-item manufacturers dropdown">
          <a class="nav-link" href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></a>
          <ul class="dropdown-menu">
            <?php foreach ($manufacturers as $item) { ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo functions::escape_html($item['link']); ?>"><?php echo $item['title']; ?></a></li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

        <?php if ($pages) { ?>
        <li class="nav-item information dropdown">
          <a class="nav-link" href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_information', 'Information'); ?></a>
          <ul class="dropdown-menu">
            <?php foreach ($pages as $item) { ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo functions::escape_html($item['link']); ?>"><?php echo $item['title']; ?></a></li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>
      </ul>

      <ul class="navbar-nav">

        <li class="nav-item customer-service">
          <a class="nav-link" href="<?php echo document::href_ilink('customer_service'); ?>"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></a>
        </li>

        <?php if (settings::get('accounts_enabled')) { ?>
        <li class="nav-item account dropdown">
          <a class="nav-link" href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo functions::draw_fonticon('fa-user'); ?> <?php echo !empty(customer::$data['id']) ? functions::escape_html(customer::$data['firstname']) : language::translate('title_sign_in', 'Sign In'); ?></a>
          <ul class="dropdown-menu dropdown-menu-end">
            <?php if (!empty(customer::$data['id'])) { ?>
              <li><a class="nav-link" href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
              <li><a class="nav-link" href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
              <li><a class="nav-link" href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
            <?php } else { ?>
              <li class="nav-item">
                <?php echo functions::form_draw_form_begin('login_form', 'post', document::ilink('login'), false, 'class="navbar-form"'); ?>
                  <?php echo functions::form_draw_hidden_field('redirect_url', document::link()); ?>

                  <div class="form-group">
                    <?php echo functions::form_draw_email_field('email', true, 'required placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
                  </div>

                  <div class="form-group">
                    <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
                  </div>

                  <div class="form-group">
                    <div class="checkbox">
                      <label><?php echo functions::form_draw_checkbox('remember_me', '1'); ?> <?php echo language::translate('title_remember_me', 'Remember Me'); ?></label>
                    </div>
                  </div>

                  <div class="btn-group btn-block">
                    <?php echo functions::form_draw_button('login', language::translate('title_sign_in', 'Sign In')); ?>
                  </div>
                <?php echo functions::form_draw_form_end(); ?>
              </li>
              <li class="nav-item text-center">
                <a class="nav-link" href="<?php echo document::href_ilink('create_account'); ?>"><?php echo language::translate('text_new_customers_click_here', 'New customers click here'); ?></a>
              </li>

              <li class="nav-item text-center">
                <a class="nav-link" href="<?php echo document::href_ilink('reset_password'); ?>"><?php echo language::translate('text_lost_your_password', 'Lost your password?'); ?></a>
              </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

      </ul>
    </div>
  </div>
</div>