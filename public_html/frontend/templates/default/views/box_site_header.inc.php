<header id="header" class="hidden-print">
  <div class="container">
    <div class="logotype">
      <a href="<?php echo document::href_ilink(''); ?>">
        <img src="<?php echo document::href_link(WS_DIR_STORAGE . 'images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
      </a>
    </div>


<!--
    <div class="text-center hidden-xs">
      <?php //include vmod::check(FS_DIR_APP . 'frontend/boxes/box_region.inc.php'); ?>
      <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search')); ?>
        <div class="input-group">
          <span class="input-group-addon">
            <?php echo functions::draw_fonticon('fa-search'); ?>
          </span>
          <?php echo functions::form_draw_text_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' &hellip;"'); ?>
          <span class="input-group-btn">
          <?php echo functions::form_draw_button('search', language::translate('title_search', 'Search'), 'class="btn btn-success"'); ?>
          </span>
        </div>
      <?php echo functions::form_draw_form_end(); ?>
    </div>
-->
    <div style="flex: 1 0 auto;">

    </div>

    <div class="shipping">
      <?php echo functions::draw_fonticon('fa-truck fa-3x pull-left'); ?>
      <div><strong><?php echo language::translate('title_free_delivery', 'Free Delivery'); ?></strong></div>
      <div>Text yada yada</div>
    </div>

    <div class="customer-service">
      <?php echo functions::draw_fonticon('fa-envelope fa-3x pull-left'); ?>
      <div><strong>{translate "title_customer_service", "Customer Service"}</strong></div>
      <div><a href="mailto://<?php echo settings::get('store_email'); ?>">{setting "store_email"}</a></div>
    </div>

    <div class="customer-service">
      <?php echo functions::draw_fonticon('fa-phone fa-3x pull-left'); ?>
      <div><strong><?php echo language::translate('title_customer_service', 'Customer Service'); ?></strong></div>
      <div><a href="tel://<?php echo settings::get('store_phone'); ?>"><?php echo settings::get('store_phone'); ?></a></div>
    </div>
  </div>
</header>