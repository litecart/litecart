<nav id="site-navigation" class="navbar hidden-print">

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
        <a href="<?php echo document::ilink(''); ?>" title="<?php echo language::translate('title_home', 'Home'); ?>"><?php echo functions::draw_fonticon('fa-home'); ?></a>
      </li>

      <?php if ($categories) { ?>
      <li class="categories dropdown">
        <a href="#" data-toggle="dropdown"><?php echo language::translate('title_categories', 'Categories'); ?></a>
        <ul class="dropdown-menu">
          <?php foreach ($categories as $item) { ?>
          <li><a href="<?php echo functions::escape_html($item['link']); ?>"><?php echo $item['title']; ?></a></li>
          <?php } ?>
        </ul>
      </li>
      <?php } ?>

      <?php if ($brands) { ?>
      <li class="brands dropdown">
        <a href="#" data-toggle="dropdown"><?php echo language::translate('title_brands', 'Brands'); ?></a>
        <ul class="dropdown-menu">
          <?php foreach ($brands as $item) { ?>
          <li><a href="<?php echo functions::escape_html($item['link']); ?>"><?php echo $item['title']; ?></a></li>
          <?php } ?>
        </ul>
      </li>
      <?php } ?>

      <?php if ($pages) { ?>
      <li class="information dropdown">
        <a href="#" data-toggle="dropdown"><?php echo language::translate('title_information', 'Information'); ?></a>
        <ul class="dropdown-menu">
          <?php foreach ($pages as $item) { ?>
          <li><a href="<?php echo functions::escape_html($item['link']); ?>"><?php echo $item['title']; ?></a></li>
          <?php } ?>
        </ul>
      </li>
      <?php } ?>
    </ul>

    <ul class="nav navbar-nav navbar-right">
      <li class="customer-service">
        <a href="<?php echo document::href_ilink('customer_service'); ?>"><?php echo language::translate('title_customer_service', 'Customer Service'); ?></a>
      </li>

      <?php if (settings::get('accounts_enabled')) { ?>
      <li class="account dropdown">
        <a href="#" data-toggle="dropdown"><?php echo functions::draw_fonticon('fa-user'); ?> <?php echo !empty(customer::$data['id']) ? customer::$data['firstname'] : language::translate('title_sign_in', 'Sign In'); ?></a>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php if (!empty(customer::$data['id'])) { ?>
            <li><a href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
            <li><a href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
            <li><a href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
          <?php } else { ?>
            <li>
              <?php echo functions::form_draw_form_begin('login_form', 'post', document::ilink('login'), false, 'class="navbar-form"'); ?>
                <?php echo functions::form_draw_hidden_field('redirect_url', document::link()); ?>

                <div class="form-group">
                  <?php echo functions::form_draw_email_field('email', true, 'required placeholder="'. language::translate('title_email_address', 'Email Address') .'"'); ?>
                </div>

                <div class="form-group">
                  <?php echo functions::form_draw_password_field('password', '', 'placeholder="'. language::translate('title_password', 'Password') .'"'); ?>
                </div>

                <div class="form-group">
                  <?php echo functions::form_draw_checkbox('remember_me', ['1', language::translate('title_remember_me', 'Remember Me')], true); ?>
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
      <?php } ?>
    </ul>
  </div>
</nav>
