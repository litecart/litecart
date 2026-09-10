<div class="fourteen-forty">
  <div class="layout row">

    <div class="col-md-3">
      <div id="sidebar">
        <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_account_links.inc.php'); ?>
      </div>
    </div>

    <div class="col-md-9">
      <main id="content">
        {snippet:notices}

        <section id="box-withdrawal-request" class="card">

          <div class="card-header">
            <h1 class="card-title"><?php echo language::translate('title_withdrawal_request', 'Withdrawal Request'); ?></h1>
          </div>

          <div class="card-body">

            <p><?php echo language::translate('description_withdrawal_request_intro', 'You are about to exercise your right of withdrawal for the order below. Please fill in the form to send us your request.'); ?></p>

            <h2 class="card-title">
              <?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?>
            </h2>

            <table class="table table-striped data-table">
              <thead>
                <tr>
                  <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
                  <th class="main"><?php echo language::translate('title_item', 'Item'); ?></th>
                  <th class="text-end"><?php echo language::translate('title_qty', 'Qty'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($order['items'] as $item) { ?>
                <tr>
                  <td><?php echo !empty($item['sku']) ? functions::escape_html($item['sku']) : '-'; ?></td>
                  <td style="white-space: normal;"><?php echo functions::escape_html($item['name']); ?></td>
                  <td class="text-end"><?php echo (float)$item['quantity']; ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>

            <?php echo functions::form_draw_form_begin('withdrawal_form', 'post', '', true, 'style="max-width: 640px;"'); ?>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_firstname', 'First Name'); ?></label>
                  <?php echo functions::form_draw_text_field('firstname', true, 'required'); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_lastname', 'Last Name'); ?></label>
                  <?php echo functions::form_draw_text_field('lastname', true, 'required'); ?>
                </div>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
                  <?php echo functions::form_draw_email_field('email', true, 'required'); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_phone_number', 'Phone Number'); ?></label>
                  <?php echo functions::form_draw_text_field('phone', true); ?>
                </div>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_reason_for_withdrawal', 'Reason for Withdrawal'); ?></label>
                <?php echo functions::form_draw_text_field('reason', true, 'required'); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_message', 'Message'); ?></label>
                <?php echo functions::form_draw_textarea('message', true, 'style="height: 200px;"'); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_attachment', 'Attachment'); ?></label>
                <?php echo functions::form_draw_file_field('attachment'); ?>
              </div>

              <?php if (settings::get('captcha_enabled')) { ?>
              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_captcha', 'CAPTCHA'); ?></label>
                  <?php echo functions::form_draw_captcha_field('captcha', 'withdrawal_request', 'required'); ?>
                </div>
              </div>
              <?php } ?>

              <div>
                <button class="btn btn-default" type="submit" name="send" value="true" style="font-weight: bold;">
                  <?php echo functions::draw_fonticon('fa-paper-plane'); ?> <?php echo language::translate('title_send_withdrawal_request', 'Send Withdrawal Request'); ?>
                </button>
                <a class="btn btn-default" href="<?php echo document::href_ilink('order_history'); ?>"><?php echo language::translate('title_cancel', 'Cancel'); ?></a>
              </div>

            <?php echo functions::form_draw_form_end(); ?>
          </div>
        </section>
      </main>
    </div>

  </div>
</div>