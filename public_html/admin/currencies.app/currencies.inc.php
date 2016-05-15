<?php
  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {

    if (!empty($_POST['currencies'])) {
      foreach (array_keys($_POST['currencies']) as $currency_code) {

        if (!empty($_POST['disable']) && $currency_code == settings::get('default_currency_code')) {
          notices::add('errors', language::translate('error_cannot_disable_default_currency', 'You cannot disable the default currency'));
          continue;
        }

        if (!empty($_POST['disable']) && $currency_code == settings::get('store_currency_code')) {
          notices::add('errors', language::translate('error_cannot_disable_store_currency', 'You cannot disable the store currency'));
          continue;
        }

        $currency = new ctrl_currency($_POST['currencies'][$currency_code]);
        $currency->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $currency->save();
      }
    }

    header('Location: '. document::link());
    exit;
  }

  if (!empty($_POST['update_rates'])) {

    foreach (array_keys(currency::$currencies) as $currency_code) {

      if ($currency_code == settings::get('store_currency_code')) continue;

      $url = 'http://download.finance.yahoo.com/d/quotes.csv?f=l1&s='. settings::get('store_currency_code') . $currency_code .'=X';

      $result = functions::http_fetch($url);

      if (empty($result)) {
        trigger_error('Could not update currency value for '. $currency_code .': No data ('. $url .')', E_USER_ERROR);
        continue;
      }

      $value = (float)trim($result) * currency::$currencies[settings::get('store_currency_code')]['value'];

      if (empty($value)) {
        trigger_error('Could not update currency value for '. $currency_code .': No value ('. $url .')', E_USER_ERROR);
        continue;
      }

      database::query(
        "update ". DB_TABLE_CURRENCIES ."
        set value = '". (float)$value ."'
        where code = '". database::input($currency_code) ."'
        limit 1;"
      );
    }

    notices::$data['success'][] = language::translate('success_currency_rates_updated', 'Currency rates updated');
    header('Location: '. document::link());
    exit;
  }
?>
<div style="float: right;"><?php echo functions::form_draw_form_begin() . functions::form_draw_button('update_rates', language::translate('title_update_rates', 'Update Rates'), 'submit', 'onclick="'. htmlspecialchars('if(!confirm("'. language::translate('text_are_you_sure', 'Are you sure?') .'")) return false;') .'"', 'fa-refresh') . functions::form_draw_form_end(); ?></div>
<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_currency'), true), language::translate('title_add_new_currency', 'Add New Currency'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_currencies', 'Currencies'); ?></h1>

<?php echo functions::form_draw_form_begin('currencies_form', 'post'); ?>

  <table width="100%" align="center" class="dataTable">
    <tr class="header">
      <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle'); ?></th>
      <th></th>
      <th><?php echo language::translate('title_id', 'ID'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_code', 'Code'); ?></th>
      <th width="100%"><?php echo language::translate('title_name', 'Name'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_value', 'Value'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_prefix', 'Prefix'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_suffix', 'Suffix'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_default_currency', 'Default Currency'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_store_currency', 'Store Currency'); ?></th>
      <th style="text-align: center;"><?php echo language::translate('title_priority', 'Priority'); ?></th>
      <th>&nbsp;</th>
    </tr>
<?php
  $currencies_query = database::query(
    "select * from ". DB_TABLE_CURRENCIES ."
    order by status desc, priority, name;"
  );

  if (database::num_rows($currencies_query) > 0) {

    while ($currency = database::fetch($currencies_query)) {
?>
    <tr class="row<?php echo !$currency['status'] ? ' semi-transparent' : null; ?>">
      <td><?php echo functions::form_draw_checkbox('currencies['. $currency['code'] .']', $currency['code']); ?></td>
      <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($currency['status']) ? '#99cc66' : '#ff6666') .';"'); ?></td>
      <td><?php echo $currency['id']; ?></td>
      <td><?php echo $currency['code']; ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_currency', 'currency_code' => $currency['code']), true); ?>"><?php echo $currency['name']; ?></a></td>
      <td style="text-align: right;"><?php echo $currency['value']; ?></td>
      <td style="text-align: center;"><?php echo $currency['prefix']; ?></td>
      <td style="text-align: center;"><?php echo $currency['suffix']; ?></td>
      <td style="text-align: center;"><?php echo ($currency['code'] == settings::get('default_currency_code')) ? 'x' : ''; ?></td>
      <td style="text-align: center;"><?php echo ($currency['code'] == settings::get('store_currency_code')) ? 'x' : ''; ?></td>
      <td style="text-align: right;"><?php echo $currency['priority']; ?></td>
      <td style="text-align: right;"><a href="<?php echo document::href_link('', array('doc' => 'edit_currency', 'currency_code' => $currency['code']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
    }
  }
?>
    <tr class="footer">
      <td colspan="12"><?php echo language::translate('title_currencies', 'Currencies'); ?>: <?php echo database::num_rows($currencies_query); ?></td>
    </tr>
  </table>

  <script>
    $(".dataTable .checkbox-toggle").click(function() {
      $(this).closest("form").find(":checkbox").each(function() {
        $(this).attr('checked', !$(this).attr('checked'));
      });
      $(".dataTable .checkbox-toggle").attr("checked", true);
    });

    $('.dataTable tr').click(function(event) {
      if ($(event.target).is('input:checkbox')) return;
      if ($(event.target).is('a, a *')) return;
      if ($(event.target).is('th')) return;
      $(this).find('input:checkbox').trigger('click');
    });
  </script>

  <p><span class="button-set"><?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?> <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?></span></p>

<?php
  echo functions::form_draw_form_end();
?>