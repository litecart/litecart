<style>
#box-third-parties h1 {
  margin-top: 0;
}
#box-third-parties .third-party {
  padding: 1em;
  border-bottom: 1px solid #f3f3f3;
  border-radius: var(--default-border-radius);
  transition: all 200ms linear;
}
#box-third-parties .third-party:first-child {
  border-top: 1px solid #f3f3f3;
}
#box-third-parties.third-party:hover {
  background: #f3f3f3;
}
#box-third-parties .third-party a {
  text-decoration: none;
  color: inherit;
}
#box-third-parties .third-party .name {
  display: block;
  font-weight: 600;
}
#box-third-parties .third-party .details {
  display: none;
  padding: 2em;
  padding-right: 0;
}
#box-third-parties .third-party .details:hidden::after {
  content: 'x';
  position: absolute;
  top: 0;
  right: 0;
}
#box-third-parties .third-party label {
  font-weight: 700;
}
</style>

<main id="content" class="container">
  {{notices}}

  <section id="box-third-parties" class="box card">

    <div class="card-header">
      <h1><?php echo language::translate('title_thrid_parties_and_data_collecting', 'Third Parties and Data Collecting'); ?></h1>
    </div>

    <div class="card-body">
      <button name="privacy_settings" class="btn btn-default" type="button" onclick="">
        <?php echo language::translate('title_privacy_settings', 'Display Privacy Settings'); ?>
      </button>

      <?php foreach ($third_parties as $third_party) { ?>
      <article class="third-party">
        <a class="name" href="<?php echo document::href_ilink('third_parties', ['third_party_id' => $third_party['id']]); ?>">
          <span class="toggle"><?php echo !empty($third_party['active']) ? functions::draw_fonticon('icon-chevron-up') : functions::draw_fonticon('icon-chevron-down'); ?></span>
          <?php echo htmlspecialchars($third_party['name']); ?>
        </a>

        <div class="details<?php echo !empty($third_party['active']) ? ' expanded' : ''; ?>"<?php echo !empty($third_party['active']) ? ' style="display: block;"' : ''; ?>>

          <div class="form-group">
            <div class="form-label"><?php echo language::translate('title_description', 'Description'); ?></div>
            <div class="description"><?php echo $third_party['description']; ?></div>
          </div>

          <div class="form-group">
            <div class="form-label"><?php echo language::translate('title_collected_data', 'Collected Data'); ?></div>
            <div class="collected-data"><?php echo nl2br($third_party['collected_data'], false); ?></div>
          </div>

          <div class="form-group">
            <div class="form-label"><?php echo language::translate('title_purposes', 'Purposes'); ?></div>
            <div class="purposes"><?php echo nl2br($third_party['purposes'], false); ?></div>
          </div>

          <div class="form-group">
            <div class="form-label"><?php echo language::translate('title_country_of_juristdiction', 'Country of Jurisdiction'); ?></div>
            <div class="country"><?php echo $third_party['country_code']; ?></div>
          </div>

          <div class="form-group">
            <div class="form-label"><?php echo language::translate('title_classes', 'Classes'); ?></div>
            <div class="classes"><?php echo $third_party['description']; ?></div>
          </div>

          <div class="form-group">
            <div class="form-label"><?php echo language::translate('title_homepage', 'Homepage'); ?></div>
            <div class="homepage"><?php echo !empty($third_party['homepage_url']) ? '<a href="'. htmlspecialchars($third_party['homepage_url']) .'" target="_blank">'. htmlspecialchars($third_party['homepage_url']) .'</a>' : '-'; ?></div>
          </div>

          <div class="form-group">
            <div class="form-label"><?php echo language::translate('title_cookie_policy', 'Cookie Policy'); ?></div>
            <div class="cookie-policy"><?php echo !empty($third_party['cookie_policy_url']) ? '<a href="'. htmlspecialchars($third_party['cookie_policy_url']) .'" target="_blank">'. htmlspecialchars($third_party['cookie_policy_url']) .'</a>' : '-'; ?></div>
          </div>

          <div class="form-group">
            <div class="form-label"><?php echo language::translate('title_opt_out', 'Opt Out'); ?></div>
            <div class="opt-out"><?php echo !empty($third_party['opt_out_url']) ? '<a href="'. htmlspecialchars($third_party['opt_out_url']) .'" target="_blank">'. htmlspecialchars($third_party['opt_out_url']) .'</a>' : '-'; ?></div>
          </div>

          <div class="form-group">
            <div class="form-label"><?php echo language::translate('title_do_not_sell', 'Do Not Sell'); ?></div>
            <div class="do-not-sell"><?php echo !empty($third_party['do_not_sell_url']) ? '<a href="'. htmlspecialchars($third_party['do_not_sell_url']) .'" target="_blank">'. htmlspecialchars($third_party['do_not_sell_url']) .'</a>' : '-'; ?></div>
          </div>
        </div>
      </article>
      <?php } ?>
    </div>

  </section>
</main>

<script>
	$('button[name="privacy_settings"]').click(function() {
		$('#site-privacy-consent').trigger('openExpanded');
	});

	$('#box-third-parties .third-party').on('toggled', function() {
		if ($(this).find('.details').is(':hidden')) {
			$(this).find('.toggle').hide().html('<?php echo functions::draw_fonticon('icon-chevron-down'); ?>').fadeIn();
		} else {
			$(this).find('.toggle').hide().html('<?php echo functions::draw_fonticon('icon-chevron-up'); ?>').fadeIn();
		}
	});

	$('#box-third-parties .third-party a').click(function(e) {
		e.preventDefault();
		var $thirdParty = $(this).closest('.third-party');
		$thirdParty.find('.details').toggleClass('expanded').toggle('fast', function() {
			$thirdParty.trigger('toggled');
		});
	});
</script>