<header id="header" class="hidden-print">
  <div class="wrapper">
    <div class="row">
      <div class="col-sm-6">
        <div class="logotype text-center text-md-start">
          <a href="<?php echo document::href_ilink(''); ?>">

          </a>
        </div>
      </div>

      <div class="hidden-xs col-sm-6">
        <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search')); ?>
          <div class="input-group">
            <span class="input-group-icon">
              <?php echo functions::draw_fonticon('fa-search'); ?>
            </span>
            <?php echo functions::form_draw_text_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' &hellip;"'); ?>
            <?php echo functions::form_draw_button('search', language::translate('title_search', 'Search'), 'class="btn btn-success"'); ?>
          </div>
        <?php echo functions::form_draw_form_end(); ?>
      </div>
    </div>
  </div>
</header>