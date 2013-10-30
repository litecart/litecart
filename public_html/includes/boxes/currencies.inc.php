<?php
  if (count(currency::$currencies) < 2) return;
?>
<ul id="currencies" class="list-horizontal">
<?php
  foreach (currency::$currencies as $currency) {
    if ($currency['status']) {
      echo '<li'. (($currency['code'] == currency::$selected['code']) ? ' class="active"' : '') .'><a href="javascript:set_currency(\''. $currency['code'] .'\');">'. $currency['code'] .'</a></li>' . PHP_EOL;
    }
  }
?>
</ul>
<script type="text/javascript">
  function set_currency(code) {
    var form = $('<?php
      echo str_replace(array("\r", "\n"), '', functions::form_draw_form_begin('currency_form', 'post')
                                            . functions::form_draw_hidden_field('set_currency', '\'+ code +\'')
                                            . functions::form_draw_form_end()
           );
    ?>');
    $(document.body).append(form);
    form.submit();
  }
</script>