<?php echo $system->functions->form_draw_form_begin('search_form', 'get', $system->document->link('search.php')); ?>
  <?php echo $system->functions->form_draw_input_field('query', isset($_GET['query']) ? $_GET['query'] : ''); ?>
  <?php echo $system->functions->form_draw_button('search', $system->language->translate('title_search', 'Search'), 'submit'); ?>
<?php echo $system->functions->form_draw_form_end(); ?>
<script type="text/javascript">
  $("input[name=query]").live("click", function(event) {
    if ($(this).val() == "<?php echo $system->language->translate('text_search_phrase_or_keyword', 'Search phrase or keyword'); ?>") {
      $(this).val("");
    }
  });
  $("input[name=query]").live("blur", function(event) {
    if ($(this).val() == "") {
      $(this).val("<?php echo $system->language->translate('text_search_phrase_or_keyword', 'Search phrase or keyword'); ?>");
    }
  });
  $("input[name=query]").trigger('blur');
</script>