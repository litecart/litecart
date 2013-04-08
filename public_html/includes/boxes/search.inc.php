<div class="box" id="search">
  <div class="content">
    <?php echo $system->functions->form_draw_form_begin('search_form', 'get', $system->document->link(WS_DIR_HTTP_HOME . 'search.php')); ?>
      <table style="width: 100%;">
        <tr>
          <td style="text-align: left; width: 150px;"><?php echo $system->functions->form_draw_input_field('query', isset($_GET['query']) ? $_GET['query'] : '', 'text', 'placeholder="'. $system->language->translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'" style="width: 100%;"'); ?></td>
          <td style="text-align: right;"><?php echo $system->functions->form_draw_button('search', $system->language->translate('title_search', 'Search'), 'submit'); ?></td>
        </tr>
      </table>
    <?php echo $system->functions->form_draw_form_end(); ?>
  </div>
</div>