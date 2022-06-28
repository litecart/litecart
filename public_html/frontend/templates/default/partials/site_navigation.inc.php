<style>
#site-top-navigation {
  background: #d1d1dd;
}
#site-top-navigation .container {
  display: flex;
  justify-content: space-between;
}
#site-top-navigation .nav {
  margin: 0 auto;
}
#site-top-navigation .code {
  font-size: .8em;
  background: #bcbccb;
  padding: .25em .5em;
}
</style>

<div id="site-top-navigation">
  <div class="wrapper">
    <ul class="nav">
      <li class="dropdown">
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>">
          <span class="code"><?php echo language::$selected['code']; ?></span> <?php echo language::$selected['name']; ?>
        </a>
      </li>
      <li class="dropdown">
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>">
          <span class="code"><?php echo currency::$selected['code']; ?></span> <?php echo currency::$selected['name']; ?>
        </a>
      </li>
      <li class="dropdown">
        <a class="nav-item" href="<?php echo document::href_ilink('regional_settings'); ?>" data-toggle="dropdown">
          <span class="code"><?php echo customer::$data['country_code']; ?></span> <?php echo reference::country(customer::$data['country_code'])->name; ?>
        </a>
      </li>
    </ul>
  </div>
</div>

<div id="site-navigation">
  <div class="navbar wrapper">

    <div class="navbar-header">
      <div class="navbar-brand">
        <a href="<?php echo document::href_ilink(''); ?>">
          <img src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('site_name'); ?>" title="<?php echo settings::get('site_name'); ?>" />
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
          <a href="<?php echo document::href_ilink('brands'); ?>"><?php echo language::translate('title_brands', 'Brands'); ?></a>
        </li>
        <?php } ?>
      </ul>

      <ul class="navbar-nav">
        <li class="search" style="min-width: 400px;">
          <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search')); ?>
            <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' &hellip;"'); ?>
          <?php echo functions::form_draw_form_end(); ?>
        </li>
      </ul>

      <ul class="navbar-nav">

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

        <?php if (settings::get('accounts_enabled')) { ?>
        <?php if (!empty(customer::$data['id'])) { ?>
        <li class="account dropdown">
          <a href="#" data-toggle="dropdown"><?php echo functions::draw_fonticon('fa-user-o'); ?> <span class="hidden-sm"><?php echo !empty(customer::$data['id']) ? customer::$data['firstname'] : language::translate('title_sign_in', 'Sign In'); ?></span></a>
          <ul class="dropdown-menu dropdown-menu-end">
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

        <li class="contact">
          <a href="<?php echo document::href_ilink('contact'); ?>">
            <i class="fa fa-envelope-o"></i> <?php echo language::translate('title_contact', 'Contact'); ?>
          </a>
        </li>

      </ul>

      <ul class="navbar-nav">
        <li class="shopping-cart<?php if (!empty($shopping_cart['items'])) echo ' filled'; ?> dropdown">
          <a href="#" data-toggle="dropdown">
            <!--<?php echo functions::draw_fonticon('fa-shopping-basket'); ?> <?php echo language::translate('title_cart', 'Cart'); ?>-->
            <img class="img-responsive" src="<?php echo document::link(WS_DIR_TEMPLATE .'images/'. (!empty($shopping_cart['items']) ? 'cart_filled.svg' : 'cart.svg')); ?>" style="max-height: 48px;" />
            <span class="hidden-sm hidden-md hidden-lg hidden-xl hidden-xxl"><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></span>
            <span class="badge"><?php echo $shopping_cart['num_items']; ?></span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end">
            <?php foreach ($shopping_cart['items'] as $item) { ?>
            <li>
              <div class="item">
                <div class="row">
                  <div class="col-3">
                    <img class="image img-responsive" src="<?php echo document::href_rlink($item['thumbnail']); ?>" alt="<?php echo $item['name']; ?>" />
                  </div>
                  <div class="col-8">
                    <div><a class="name" href="<?php echo document::href_ilink('product', ['product_id' => $item['product_id']]); ?>"><?php echo $item['name']; ?></a></div>
                    <div class="price"><?php echo currency::format($item['price']); ?></div>
                  </div>
                  <div class="col-1 text-end">
                    <?php echo functions::form_draw_button('remove_cart_item', [$item['key'], functions::draw_fonticon('delete',)], 'submit', 'class="btn btn-danger btn-sm"'); ?>
                  </div>
                </div>
              </div>
            </li>
            <?php } ?>
            <li class="empty text-center">
              <span><?php echo language::translate('text_your_shopping_cart_is_empty'), 'Your shopping cart is empty'; ?></span>
            </li>
            <li class="checkout">
              <a class="btn btn-success btn-block" href="<?php echo document::href_ilink('checkout/index'); ?>">
                <?php echo language::translate('title_go_to_checkout', 'Go To Checkout'); ?> <?php echo functions::draw_fonticon('fa-arrow-right'); ?>
              </a>
            </li>
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
