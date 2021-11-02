<?php
  if (empty($_GET['page']) || !is_numeric($_GET['page'])) $_GET['page'] = 1;

  document::$snippets['title'][] = language::translate('title_campaigns', 'Campaigns');

  breadcrumbs::add(language::translate('title_campaigns', 'Campaigns'));

  if (isset($_POST['enable']) || isset($_POST['disable'])) {

    try {
      if (empty($_POST['campaigns'])) throw new Exception(language::translate('error_must_select_campaigns', 'You must select campaigns'));

      foreach ($_POST['campaigns'] as $campaign_id) {
        $campaign = new ent_campaign($campaign_id);
        $campaign->data['status'] = !empty($_POST['enable']) ? 1 : 0;
        $campaign->save();
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link());
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

// Table Rows
  $campaigns = [];

  $campaigns_query = database::query(
    "select pc.*, pi.name as product_name,
      pc.`". database::input(settings::get('site_currency_code')) ."` as campaign_price,
      pp.`". database::input(settings::get('site_currency_code')) ."` as product_price
    from ". DB_TABLE_PREFIX ."products_campaigns pc
    left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = pc.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
    left join ". DB_TABLE_PREFIX ."products_prices pp on (pp.product_id = pc.product_id)
    order by pc.start_date, pc.end_date;"
  );

  if ($_GET['page'] > 1) database::seek($campaigns_query, settings::get('data_table_rows_per_page') * ($_GET['page'] - 1));

  $page_items = 0;
  while ($campaign = database::fetch($campaigns_query)) {
    $campaigns[] = $campaign;
    if (++$page_items == settings::get('data_table_rows_per_page')) break;
  }

// Number of Rows
  $num_rows = database::num_rows($campaigns_query);

// Pagination
  $num_pages = ceil($num_rows/settings::get('data_table_rows_per_page'));
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_campaigns', 'Campaigns'); ?>
    </div>
  </div>

  <div class="card-action">
    <?php echo functions::form_draw_link_button(document::ilink(__APP__.'/edit_campaign'), language::translate('title_create_new_campaign', 'Create New Campaign'), '', 'add'); ?>
  </div>

  <?php echo functions::form_draw_form_begin('campaigns_form', 'post'); ?>

    <table class="table table-striped table-hover data-table">
      <thead>
        <tr>
          <th><?php echo functions::draw_fonticon('fa-check-square-o fa-fw checkbox-toggle', 'data-toggle="checkbox-toggle"'); ?></th>
          <th class="text-right"><?php echo language::translate('title_start_date', 'Start Date'); ?></th>
          <th class="text-right"><?php echo language::translate('title_end_date', 'End Date'); ?></th>
          <th class="main"><?php echo language::translate('title_product', 'Product'); ?></th>
          <th class="text-right"><?php echo language::translate('title_campaign_price', 'Campaign Price'); ?></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($campaigns as $campaign) { ?>
        <tr class="<?php echo (!empty($campaign['end_date']) && $campaign['end_date'] > 1970 && $campaign['end_date'] < date('Y-m-d H:i:s')) ? 'semi-transparent' : null; ?>">
          <td><?php echo functions::form_draw_checkbox('campaigns[]', $campaign['id']); ?></td>
          <td class="text-right"><?php echo (!empty($campaign['start_date'])) ? language::strftime(language::$selected['format_date'], strtotime($campaign['start_date'])) : ''; ?></td>
          <td class="text-right"><?php echo (!empty($campaign['end_date'])) ? language::strftime(language::$selected['format_date'], strtotime($campaign['end_date'])) : ''; ?></td>
          <td><?php echo $campaign['product_name']; ?></td>
          <td class="text-right"><?php echo currency::format($campaign['campaign_price'], false, settings::get('site_currency_code')); ?></td>
          <td class="text-right"><a href="<?php echo document::href_ilink(__APP__.'/edit_campaign', ['campaign_id' => $campaign['id']]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('fa-pencil'); ?></a></td>
        </tr>
        <?php } ?>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="6"><?php echo language::translate('title_campaigns', 'Campaigns'); ?>: <?php echo $num_rows; ?></td>
        </tr>
      </tfoot>
    </table>

  <?php echo functions::form_draw_form_end(); ?>

  <?php if ($num_pages > 1) { ?>
  <div class="card-body">
    <?php echo functions::draw_pagination($num_pages); ?>
  </div>
  <?php } ?>

</div>