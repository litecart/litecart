<div id="site-navigation" style="grid-area: navigation;">
  <div class="container navbar">

    <div class="navbar-header">
      <div class="navbar-brand">
        <a href="<?php echo document::href_ilink(''); ?>">
          <img src="<?php echo document::href_link(WS_DIR_STORAGE . 'images/logotype.png'); ?>" alt="<?php echo settings::get('site_name'); ?>" title="<?php echo settings::get('site_name'); ?>" />
        </a>
      </div>

      <button type="button" class="navbar-toggle">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <nav class="navbar-menu">
      <ul class="navbar-nav">

        <li class="search hidden-md hidden-lg hidden-xl">
          <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search')); ?>
            <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' &hellip;"'); ?>
          <?php echo functions::form_draw_form_end(); ?>
        </li>

        <?php if ($categories) { ?>
        <li class="categories dropdown">
          <a href="#" data-toggle="dropdown"><?php echo language::translate('title_products', 'Products'); ?></a>
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

      <ul class="navbar-nav navbar-end">

        <li class="customer-service">
          <a href="<?php echo document::href_ilink('customer_service'); ?>">
            <?php echo functions::draw_fonticon('fa-envelope-o'); ?> <?php echo language::translate('title_contact', 'Contact'); ?>
          </a>
        </li>

        <li class="regional-settings">
          <a href="<?php echo document::href_ilink('regional_settings'); ?>" data-toggle="lightbox" data-seamless="true">
            <?php echo functions::draw_fonticon('fa-globe'); ?>
            <span class="language"><?php echo language::$selected['code']; ?></span>
            / <span class="country"><?php echo customer::$data['country_code']; ?></span>
            / <span class="currency"><?php echo currency::$selected['code']; ?></span>
          </a>
        </li>

        <?php if (settings::get('accounts_enabled')) { ?>
        <?php if (!empty(customer::$data['id'])) { ?>
        <li class="account dropdown">
          <a href="#" data-toggle="dropdown"><?php echo functions::draw_fonticon('fa-user-o'); ?> <span class="hidden-sm"><?php echo !empty(customer::$data['id']) ? customer::$data['firstname'] : language::translate('title_sign_in', 'Sign In'); ?></span></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_order_history', 'Order History'); ?></a></li>
            <li><a href="<?php echo document::href_ilink('edit_account'); ?>"><?php echo language::translate('title_edit_account', 'Edit Account'); ?></a></li>
            <li><a href="<?php echo document::href_ilink('logout'); ?>"><?php echo language::translate('title_logout', 'Logout'); ?></a></li>
          </ul>
        </li>
        <?php } else { ?>
        <li class="account">
          <a href="<?php echo document::href_ilink('login'); ?>">
            <?php echo functions::draw_fonticon('fa-user-o'); ?> <?php echo language::translate('title_sign_in', 'Sign In'); ?>
          </a>
        </li>
        <?php } ?>
        <?php } ?>

        <li class="shopping-cart<?php if (!empty($shopping_cart['items'])) echo ' filled'; ?> dropdown">
          <a href="#" data-toggle="dropdown">
            <?php echo functions::draw_fonticon('fa-shopping-basket'); ?> <?php echo language::translate('title_cart', 'Cart'); ?>

            <?php if (!empty($shopping_cart['items'])) { ?>
            <span class="badge"><?php echo $shopping_cart['num_items']; ?></span>
            <?php } ?>
          </a>

          <ul class="dropdown-menu dropdown-menu-end">
            <?php if (!empty($shopping_cart['items'])) { ?>
            <?php foreach ($shopping_cart['items'] as $item) { ?>
            <li>
              <div class="item">
                <div class="row">
                  <div class="col-3">
                    <img class="image img-responsive" src="<?php echo document::href_link(WS_DIR_STORAGE . $item['thumbnail']); ?>" alt="<?php echo $item['name']; ?>" />
                  </div>
                  <div class="col-8">
                    <div><a class="name" href="<?php echo document::href_ilink('product', ['product_id' => $item['product_id']]); ?>"><?php echo $item['name']; ?></a></div>
                    <div class="price"><?php echo currency::format($item['price']); ?></div>
                  </div>
                  <div class="col-1 text-end">
                    <?php echo functions::form_draw_button('remove_cart_item', [1, functions::draw_fonticon('delete',)], 'submit', 'class="btn btn-danger btn-sm"'); ?>
                  </div>
                </div>
              </div>
            </li>
            <?php } ?>
            <li class="checkout"><a href="<?php echo document::href_ilink('checkout/index'); ?>"><?php echo language::translate('title_go_to_checkout', 'Go To Checkout'); ?></a></li>
            <?php } else { ?>
            <li><span><?php echo language::translate('title_empty'), 'Empty'; ?></span></li>
            <?php } ?>
          </ul>
        </li>
      </ul>
    </nav>
  </div>
</div>

<script>
  $('.navbar .navbar-toggle').click(function(){
    $(this).closest('.navbar').toggleClass('expanded');
  });

  $('#site-navigation .search').click(function(){
    $(this).find('input[name="query"]').focus();
  });
</script>
