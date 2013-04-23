<div class="box" id="search">
  <div class="content">
    <?php echo $system->functions->form_draw_form_begin('search_form', 'get', $system->document->link(WS_DIR_HTTP_HOME . 'search.php')); ?>
    <table>
      <tr>
        <td style="width: 100%;">
      <?php echo $system->functions->form_draw_search_field('query', true, 'placeholder="'. $system->language->translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'" style="width: 100%;"'); ?>
        </td>
        <td>
      <?php echo $system->functions->form_draw_button('search', $system->language->translate('title_search', 'Search'), 'submit'); ?>
        </td>
      </tr>
    </table>
    <?php echo $system->functions->form_draw_form_end(); ?>
  </div>
</div>