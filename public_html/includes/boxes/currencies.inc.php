<?php
  if (count($system->currency->currencies) < 2) return;
?>
<ul id="currencies" class="list-horizontal">
<?php
  foreach ($system->currency->currencies as $currency) {
    if ($currency['status']) {
      echo '<li><a href="javascript:set_currency(\''. $currency['code'] .'\');">'. ($currency['prefix']) . trim($currency['suffix']) .'</a></li>' . PHP_EOL;
    }
  }
?>
</ul>
<script type="text/javascript">
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