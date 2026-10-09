<div id="sidebar" class="hidden-print">
	<input id="sidebar-compact-toggle" type="checkbox" hidden>

	<div class="sidebar-header">
		<div class="filter">
			<?= f::form_input_search('filter', false, ['placeholder' => t('title_filter', 'Filter').'…', 'autocomplete' => 'off']) ?>
		</div>
	</div>

	<div class="sidebar-content">
		<nav id="sidebar-menu">
			<ul class="groups">
				<li class="group">
					<ul class="apps">
						<li class="app<?= !defined('__APP__') ? ' active' : '' ?>" style="--app-color: #ccc;">
							<a href="<?= document::href_ilink('b:') ?>" title="<?= f::escape_attr(t('title_dashboard', 'Dashboard')) ?>">
								<span class="app-icon"><?= f::draw_fonticon('icon-grid-view-o') ?></span>
								<span class="name"><?= t('title_dashboard', 'Dashboard') ?></span>
							</a>
						</li>
					</ul>
				</li>

				<?php foreach ($groups as $group) { ?>
				<li class="group">

					<div class="title">
						<?= $group['name'] ?>
					</div>

					<ul class="apps">

						<?php foreach ($group['apps'] as $app) { ?>
						<li class="app<?= $app['active'] ? ' active' : '' ?><?= !empty($app['menu']) ? ' has-docs' : '' ?>" data-id="<?= $app['id'] ?>" style="--app-color: <?= $app['theme']['color'] ?>;">

							<a href="<?= f::escape_html($app['link']) ?>" title="<?= f::escape_html($app['name']) ?>">
								<span class="app-icon">
									<?= f::draw_fonticon($app['theme']['icon']) ?>
								</span>
								<span class="name"><?= $app['name'] ?></span>
								<?php if (!empty($app['menu'])) { ?>
								<i class="app-toggle icon-square-plus"></i>
								<?php } ?>
							</a>

							<?php if (!empty($app['menu'])) { ?>
							<ul class="docs">

								<?php foreach ($app['menu'] as $item) { ?>
								<li class="doc<?= $item['active'] ? ' active' : '' ?>" data-id="<?= $item['doc'] ?>">
									<a href="<?= f::escape_html($item['link']) ?>">
										<span class="name"><?= $item['title'] ?></span>
									</a>
								</li>
								<?php } ?>

							</ul>
							<?php } ?>
						</li>
						<?php } ?>

					</ul>
				</li>
				<?php } ?>

			</ul>
		</nav>
	</div>

	<div class="sidebar-footer">

		<a class="platform" href="<?= document::href_ilink('about') ?>">
			<img src="<?= document::href_rlink('app://backend/template/images/symbol.svg') ?>">
			<div>
				<div class="name"><?= PLATFORM_NAME ?>® <span class="version"><?= PLATFORM_VERSION ?></span></div>
				<div class="copyright" class="text-center">
					<small>Copyright &copy; <?= date('2012-Y') ?> LiteCart AB</small>
				</div>
			</div>
		</a>

	</div>
</div>