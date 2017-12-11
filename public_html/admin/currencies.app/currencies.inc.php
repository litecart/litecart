<?php
  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['currencies'])) throw new Exception(language::translate('error_must_select_currencies', 'You must select currencies'));

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

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['update_rates'])) {

    try {
      if (empty($_POST['currencies'])) throw new Exception(language::translate('error_must_select_currencies', 'You must select currencies'));

      $url = document::link('https://api.fixer.io/latest', array('base' => settings::get('store_currency_code'), 'symbols' => implode(',', $_POST['currencies'])));

      $client = new http_client();
      $response = $client->call('GET', $url);

      if (empty($response)) {
        throw new Exception(language::translate('error_no_response_from_remote_machine', 'No response from remote machine'));
      }

      if (!$response = json_decode($response, true)) {
         throw new Exception(language::translate('error_invalid_response_from_remote_machine', 'Invalid response from remote machine'));
      }

      foreach ($response['rates'] as $currency_code => $rate) {
        database::query(
          "update ". DB_TABLE_CURRENCIES ."
          set value = '". (float)$rate ."'
          where code = '". database::input($currency_code) ."'
          limit 1;"
        );
      }

      notices::add('success', language::translate('success_currency_rates_updated', 'Currency rates updated'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<ul class="list-inline pull-right">
  <li><?php echo functions::form_draw_link_button(document::link('', array('doc' => 'edit_currency'), true), language::translate('title_add_new_currency', 'Add New Currency'), '', 'add'); ?></li>
</ul>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_currencies', 'Currencies'); ?></h1>

<?php echo functions::form_draw_form_begin('currencies_form', 'post'); ?>

  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
        <th></th>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th><?php echo language::translate('title_code', 'Code'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th><?php echo language::translate('title_value', 'Value'); ?></th>
        <th><?php echo language::translate('title_decimals', 'Decimals'); ?></th>
        <th><?php echo language::translate('title_prefix', 'Prefix'); ?></th>
        <th><?php echo language::translate('title_suffix', 'Suffix'); ?></th>
        <th><?php echo language::translate('title_default_currency', 'Default Currency'); ?></th>
        <th><?php echo language::translate('title_store_currency', 'Store Currency'); ?></th>
        <th><?php echo language::translate('title_priority', 'Priority'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
<?php
  $currencies_query = database::query(
    "select * from ". DB_TABLE_CURRENCIES ."
    order by status desc, priority, name;"
  );

  if (database::num_rows($currencies_query) > 0) {

    while ($currency = database::fetch($currencies_query)) {
?>
    <tr class="<?php echo empty($currency['status']) ? 'semi-transparent' : null; ?>">
      <td><?php echo functions::form_draw_checkbox('currencies['. $currency['code'] .']', $currency['code']); ?></td>
      <td><?php echo functions::draw_fonticon('fa-circle', 'style="color: '. (!empty($currency['status']) ? '#88cc44' : '#ff6644') .';"'); ?></td>
      <td><?php echo $currency['id']; ?></td>
      <td><?php echo $currency['code']; ?></td>
      <td><a href="<?php echo document::href_link('', array('doc' => 'edit_currency', 'currency_code' => $currency['code']), true); ?>"><?php echo $currency['name']; ?></a></td>
      <td class="text-right"><?php echo $currency['value']; ?></td>
      <td class="text-center"><?php echo $currency['decimals']; ?></td>
      <td class="text-center"><?php echo $currency['prefix']; ?></td>
      <td class="text-center"><?php echo $currency['suffix']; ?></td>
      <td class="text-center"><?php echo ($currency['code'] == settings::get('default_currency_code')) ? functions::draw_fonticon('fa-check') : ''; ?></td>
      <td class="text-center"><?php echo ($currency['code'] == settings::get('store_currency_code')) ? functions::draw_fonticon('fa-check') : ''; ?></td>
      <td class="text-center"><?php echo $currency['priority']; ?></td>
      <td class="text-right"><a href="<?php echo document::href_link('', array('doc' => 'edit_currency', 'currency_code' => $currency['code']), true); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
    </tr>
<?php
    }
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="13"><?php echo language::translate('title_currencies', 'Currencies'); ?>: <?php echo database::num_rows($currencies_query); ?></td>
      </tr>
    </tfoot>
  </table>

  <ul class="list-inline">
    <li>
      <div class="btn-group">
        <?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
        <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
      </div>
    </li>

    <li><?php echo functions::form_draw_button('update_rates', language::translate('title_update_rates', 'Update Rates'), 'submit', 'onclick="'. htmlspecialchars('if(!confirm("'. language::translate('text_are_you_sure', 'Are you sure?') .'")) return false;') .'"', 'fa-refresh'); ?></li>
  </ul>

<?php echo functions::form_draw_form_end(); ?>