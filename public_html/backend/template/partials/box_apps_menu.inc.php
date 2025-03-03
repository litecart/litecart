<ul id="box-apps-menu">

	<?php foreach ($groups as $group) { ?>
	<li class="group">
		<span class="name"><?php echo $group['name']; ?></span>
	</li>

	<?php foreach ($group['apps'] as $app) { ?>
	<li class="app<?php echo $app['active'] ? ' active' : ''; ?>" data-id="<?php echo $app['id']; ?>" style="--app-color: <?php echo $app['theme']['color']; ?>;">
		<a href="<?php echo functions::escape_html($app['link']); ?>" data-toggle="ajax-load">
			<span class="app-icon" title="<?php echo functions::escape_html($app['name']); ?>">
				<?php echo functions::draw_fonticon($app['theme']['icon']); ?>
			</span>
			<span class="name"><?php echo $app['name']; ?></span>
		</a>

		<?php if (!empty($app['menu'])) { ?>
		<ul class="docs">
			<?php foreach ($app['menu'] as $item) { ?>
			<li class="doc<?php echo $item['active'] ? ' active' : ''; ?>" data-id="<?php echo $item['doc']; ?>">
				<a href="<?php echo functions::escape_html($item['link']); ?>">
					<span style="opacity: .5;">&bullet;</span> <span class="name"><?php echo $item['title']; ?></span>
				</a>
			</li>
			<?php } ?>
		</ul>
		<?php } ?>
	</li>
	<?php } ?>

	<?php } ?>

</ul>

