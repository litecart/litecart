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

        <section id="box-withdrawal" class="card">

          <div class="card-header">
            <h1 class="card-title"><?php echo language::translate('title_withdraw_from_purchase', 'Withdraw from Purchase'); ?></h1>
          </div>

          <div class="card-body">

            <p><?php echo language::translate('description_withdrawal_intro', 'You are about to exercise your right of withdrawal. Identify the order you wish to withdraw from and we will send your request to us.'); ?></p>

            <h2 class="card-title">
              <?php echo language::translate('title_identify_order', 'Identify Order'); ?>
            </h2>

            <?php if ($signed_in) { ?>

            <?php if ($order_groups) { ?>
            <p><?php echo language::translate('description_identify_order_intro_customer', 'Select the order you wish to withdraw from.'); ?></p>

            <?php echo functions::form_draw_form_begin('identify_order_form', 'post', '', false, 'style="max-width: 640px;"'); ?>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_order_number', 'Order Number'); ?></label>
                  <?php echo functions::form_draw_select_optgroup_field('order_id', $order_groups, true, false, 'required'); ?>
                </div>
              </div>

              <div>
                <button class="btn btn-default" type="submit" name="identify" value="true" style="font-weight: bold;">
                 <?php echo language::translate('title_continue', 'Continue'); ?> <?php echo functions::draw_fonticon('fa-arrow-right'); ?>
                </button>
              </div>

            <?php echo functions::form_draw_form_end(); ?>

            <?php } else { ?>
            <p><?php echo language::translate('description_no_orders_to_withdraw', 'You have no orders to withdraw from.'); ?></p>
            <?php } ?>

            <?php } else { ?>

            <p><?php echo language::translate('description_identify_order_intro_guest_postcode', 'Enter the order number and the postcode of your purchase. We will email a link to the withdrawal request to the email address registered with the order.'); ?></p>

            <?php echo functions::form_draw_form_begin('identify_order_form', 'post', '', false, 'style="max-width: 640px;"'); ?>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_order_number', 'Order Number'); ?></label>
                  <?php echo functions::form_draw_text_field('order_id', true, 'required'); ?>
                </div>
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
                  <?php echo functions::form_draw_text_field('postcode', true, 'required'); ?>
                </div>
              </div>

              <?php if (settings::get('captcha_enabled')) { ?>
              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_captcha', 'CAPTCHA'); ?></label>
                  <?php echo functions::form_draw_captcha_field('captcha', 'withdrawal_lookup', 'required'); ?>
                </div>
              </div>
              <?php } ?>

              <div>
                <button class="btn btn-default" type="submit" name="identify" value="true" style="font-weight: bold;">
                  <?php echo functions::draw_fonticon('fa-envelope-o'); ?> <?php echo language::translate('title_send_withdrawal_link', 'Send Link'); ?>
                </button>
              </div>

            <?php echo functions::form_draw_form_end(); ?>

            <?php } ?>

          </div>
        </section>
      </main>
    </div>

  </div>
</div>
