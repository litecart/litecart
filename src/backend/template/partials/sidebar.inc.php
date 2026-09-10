<div id="sidebar" class="hidden-print">
	<input id="sidebar-compact-toggle" type="checkbox" hidden>

	<div class="sidebar-header">
		<div class="filter">
			<?php echo f::form_input_search('filter', false, ['placeholder' => (t('title_filter', 'Filter') . '…'), 'autocomplete' => 'off']); ?>
		</div>
	</div>

	<div class="sidebar-content">

		<nav id="sidebar-menu">
			<div class=ebar>
			<ul class="groups">

				<?php foreach ($groups as $group) { ?>
				<li class="group">

					<div class="title">
						<?php echo $group['name']; ?>
					</div>

					<ul class="apps">

						<?php foreach ($group['apps'] as $app) { ?>
						<li class="app<?php echo $app['active'] ? ' active' : ''; ?>" data-id="<?php echo $app['id']; ?>" style="--app-color: <?php echo $app['theme']['color']; ?>;">

							<a href="<?php echo f::escape_html($app['link']); ?>" title="<?php echo f::escape_html($app['name']); ?>">
								<span class="app-icon">
									<?php echo f::draw_fonticon($app['theme']['icon']); ?>
								</span>
								<span class="name"><?php echo $app['name']; ?></span>
							</a>

							<?php if (!empty($app['menu'])) { ?>
							<ul class="docs">

								<?php foreach ($app['menu'] as $item) { ?>
								<li class="doc<?php echo $item['active'] ? ' active' : ''; ?>" data-id="<?php echo $item['doc']; ?>">
									<a href="<?php echo f::escape_html($item['link']); ?>">
										<span class="name"><?php echo $item['title']; ?></span>
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

		<div class="text-center">
			<a class="platform" href="<?php echo document::href_ilink('about'); ?>">
				<span class="name"><?php echo PLATFORM_NAME; ?>®</span> <span class="version"><?php echo PLATFORM_VERSION; ?></span>
			</a>
		</div>

		<div class="copyright" class="text-center">
			Copyright &copy; <?php echo date('2012-Y'); ?> LiteCart AB
		</div>

	</div>
</div>