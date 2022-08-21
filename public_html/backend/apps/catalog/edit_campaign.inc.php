<?php

  if (!empty($_GET['campaign_id'])) {
    $campaign = new ent_campaign($_GET['campaign_id']);
  } else {
    $campaign = new ent_campaign();
  }

  if (!$_POST) {
    $_POST = $campaign->data;
  }

  document::$snippets['title'][] = !empty($campaign->data['id']) ? language::translate('title_edit_campaign', 'Edit Campaign') : language::translate('title_create_new_campaign', 'Create New Campaign');

  breadcrumbs::add(language::translate('title_campaigns', 'Campaigns'), document::ilink(__APP__.'/campaigns'));
  breadcrumbs::add(!empty($campaign->data['id']) ? language::translate('title_edit_campaign', 'Edit Campaign') : language::translate('title_create_new_campaign', 'Create New Campaign'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['product_id'])) throw new Exception(language::translate('error_must_select_product', 'You must select a product'));
      if ($_POST['start_date'] > $_POST['end_date']) throw new Exception(language::translate('error_start_date_cannot_be_greater_than_end_date', 'The start date cannot be greater than the end date'));

      $fields = [
        'product_id',
        'start_date',
        'end_date',
      ];

      foreach (array_keys(currency::$currencies) as $currency_code) {
        $fields[] = $currency_code;
      }

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $campaign->data[$field] = $_POST[$field];
      }

      $campaign->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/campaigns'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($campaign->data['id'])) throw new Exception(language::translate('error_must_provide_campaign', 'You must provide a campaign'));

      $campaign->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/campaigns'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $currencies = array_map(function($currency){
    return ['code' => $currency['code'], 'decimals' => (int)$currency['decimals'], 'value' => $currency['value']];
  }, currency::$currencies);
?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($campaign->data['id']) ? language::translate('title_edit_campaign', 'Edit Campaign') : language::translate('title_create_new_campaign', 'Create New Campaign'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('campaign_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="form-group">
        <label><?php echo language::translate('title_product', 'Product'); ?></label>
        <?php echo functions::form_product_field('product_id', true, false); ?>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_start_date', 'Start Date'); ?></label>
          <?php echo functions::form_datetime_field('start_date', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_end_date', 'End Date'); ?></label>
          <?php echo functions::form_datetime_field('end_date', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <label><strong><?php echo settings::get('store_currency_code'); ?></strong></label>
          <?php echo functions::form_currency_field(settings::get('store_currency_code'), settings::get('store_currency_code'), true); ?>
        </div>
        <?php foreach (array_keys(currency::$currencies) as $currency_code) { ?>
        <?php if ($currency_code == settings::get('store_currency_code')) continue; ?>
        <div class="col-md-4">
          <label><?php echo $currency_code; ?></label>
          <?php echo functions::form_currency_field($currency_code, $currency_code, true); ?>
        </div>
        <?php } ?>
      </div>

      <div class="card-action">
        <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo (!empty($campaign->data['id'])) ? functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<script>
  var currencies = <?php echo json_encode($currencies); ?>;

  $('input[name="<?php echo settings::get('store_currency_code'); ?>"]').on('input', function(){
    var campaign_price = $(this).val();
    $.each(currencies, function(i,currency){
      if (currency.code == '<?php echo settings::get('store_currency_code'); ?>') return;
      var currency_campaign_price = parseFloat(Number(campaign_price / currency.value).toFixed(currency.decimals)) || '';
      $('input[name="'+ currency.code +'"]').attr('placeholder', currency_campaign_price);
    });
  }).trigger('input');
</script>