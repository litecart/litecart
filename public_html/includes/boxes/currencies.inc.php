<?php
  if (count($system->currency->currencies) < 2) return;
?>
<ul id="currencies" class="list-horizontal">
<?php
  foreach ($system->currency->currencies as $currency) {
    if ($currency['status']) {
      echo '  <li id="'. $currency['code'] .'"'. (($currency['code'] == $system->currency->selected['code']) ? ' class="active"' : '') .'><a href="javascript:set_currency(\''. $currency['code'] .'\');">'. $currency['code'] .'</a></li>' . PHP_EOL;
    }
  }
?>
</ul>
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