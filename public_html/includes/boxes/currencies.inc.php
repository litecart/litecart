<?php
  foreach ($system->currency->currencies as $currency) {
    if ($currency['status']) {
      echo '<a href="javascript:set_currency(\''. $currency['code'] .'\');">'. ($currency['prefix']) . trim($currency['suffix']) .'</a> ';
    }
  }
?>
<script>
  function set_currency(code) {
    var form = $('<?php
      echo str_replace(array("\r", "\n"), '', $system->functions->form_draw_form_begin('currency_form', 'post')
                                            . $system->functions->form_draw_hidden_field('set_currency', '\'+ code +\'')
                                            . $system->functions->form_draw_form_end()
           );
    ?>');
    $(document.body).append(form);
    form.submit();
  }
</script>