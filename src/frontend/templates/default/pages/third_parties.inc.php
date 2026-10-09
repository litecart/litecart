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

	<section id="box-third-parties" class="box card" aria-label="<?= f::escape_attr(t('title_thrid_parties_and_data_collecting', 'Third Parties and Data Collecting')) ?>">

		<div class="card-header">
			<h1><?= t('title_thrid_parties_and_data_collecting', 'Third Parties and Data Collecting') ?></h1>
		</div>

		<div class="card-body">
			<button name="privacy_settings" class="btn btn-default" type="button" onclick="" aria-expanded="false">
				<?= t('title_privacy_settings', 'Display Privacy Settings') ?>
			</button>

			<?php foreach ($third_parties as $third_party) { ?>
			<article class="third-party" aria-label="<?= f::escape_attr(htmlspecialchars($third_party['name'])) ?>">
				<a class="name" href="<?= document::href_ilink('third_parties', ['third_party_id' => $third_party['id']]) ?>" aria-expanded="<?= !empty($third_party['active']) ? 'true' : 'false' ?>">
					<span class="toggle" aria-hidden="true"><?= !empty($third_party['active']) ? f::draw_fonticon('icon-chevron-up') : f::draw_fonticon('icon-chevron-down') ?></span>
					<?= htmlspecialchars($third_party['name']) ?>
				</a>

				<div class="details<?= !empty($third_party['active']) ? ' expanded' : '' ?>"<?= !empty($third_party['active']) ? ' style="display: block;"' : '' ?>>

					<div class="form-group">
						<div class="form-label"><?= t('title_description', 'Description') ?></div>
						<div class="description"><?= $third_party['description'] ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_collected_data', 'Collected Data') ?></div>
						<div class="collected-data"><?= nl2br($third_party['collected_data'], false) ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_purposes', 'Purposes') ?></div>
						<div class="purposes"><?= nl2br($third_party['purposes'], false) ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_country_of_juristdiction', 'Country of Jurisdiction') ?></div>
						<div class="country"><?= $third_party['country_code'] ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_classes', 'Classes') ?></div>
						<div class="classes"><?= $third_party['description'] ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_homepage', 'Homepage') ?></div>
						<div class="homepage"><?= !empty($third_party['homepage_url']) ? '<a href="'. htmlspecialchars($third_party['homepage_url']) .'" target="_blank">'. htmlspecialchars($third_party['homepage_url']) .'</a>' : '-' ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_cookie_policy', 'Cookie Policy') ?></div>
						<div class="cookie-policy"><?= !empty($third_party['cookie_policy_url']) ? '<a href="'. htmlspecialchars($third_party['cookie_policy_url']) .'" target="_blank">'. htmlspecialchars($third_party['cookie_policy_url']) .'</a>' : '-' ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_opt_out', 'Opt Out') ?></div>
						<div class="opt-out"><?= !empty($third_party['opt_out_url']) ? '<a href="'. htmlspecialchars($third_party['opt_out_url']) .'" target="_blank">'. htmlspecialchars($third_party['opt_out_url']) .'</a>' : '-' ?></div>
					</div>

					<div class="form-group">
						<div class="form-label"><?= t('title_do_not_sell', 'Do Not Sell') ?></div>
						<div class="do-not-sell"><?= !empty($third_party['do_not_sell_url']) ? '<a href="'. htmlspecialchars($third_party['do_not_sell_url']) .'" target="_blank">'. htmlspecialchars($third_party['do_not_sell_url']) .'</a>' : '-' ?></div>
					</div>
				</div>
			</article>
			<?php } ?>
		</div>

	</section>
</main>

<script>
	$('button[name="privacy_settings"]').on('click', function() {
		$('#site-privacy-consent').trigger('openExpanded');
	});

	$('#box-third-parties .third-party').on('toggled', function() {
		if ($(this).find('.details').is(':hidden')) {
			$(this).find('.toggle').hide().html('<?= f::draw_fonticon('icon-chevron-down') ?>').fadeIn();
		} else {
			$(this).find('.toggle').hide().html('<?= f::draw_fonticon('icon-chevron-up') ?>').fadeIn();
		}
	});

	$('#box-third-parties .third-party a').on('click', function(e) {
		e.preventDefault();
		var $thirdParty = $(this).closest('.third-party');
		$('.details', $thirdParty).toggleClass('expanded').toggle('fast', function() {
			$thirdParty.trigger('toggled');
		});
	});
</script>