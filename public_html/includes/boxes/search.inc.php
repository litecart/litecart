<div class="box" id="search">
  <div class="content">
    <?php echo $system->functions->form_draw_form_begin('search_form', 'get', $system->document->link(WS_DIR_HTTP_HOME . 'search.php')); ?>
      <div class="input-wrapper">&nbsp;<img src="{snippet:template_path}images/search.png" width="12" height="12" alt="" style="vertical-align: middle;" /><?php echo $system->functions->form_draw_search_field('query', true, 'placeholder="'. $system->language->translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"'); ?></div>
    <?php echo $system->functions->form_draw_form_end(); ?>
  </div>
</div>