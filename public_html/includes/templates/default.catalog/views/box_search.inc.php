<div id="box-search" class="box">
  <div class="content">
    <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search')); ?>
      <div class="input-wrapper">&nbsp;<?php echo functions::draw_fonticon('fa-search'); ?> <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search', 'Search') .'"'); ?></div>
    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>
