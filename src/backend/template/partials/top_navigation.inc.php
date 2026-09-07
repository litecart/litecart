<ul id="top-navigation" class="shadow hidden-print">
	<li>
		<div>
			<label class="nav-toggle btn btn-default" for="sidebar-compact-toggle">
				<?php echo f::draw_fonticon('icon-sidebar', 'style="font-size: 1.5em;"'); ?>
			</label>
		</div>
	</li>

	<li style="flex-grow: 1;">
		<div id="search" class="dropdown">
			<?php echo f::form_input_search('query', false, ['placeholder' => f::escape_attr(t('title_search', 'Search')) . '…', 'autocomplete' => 'off']); ?>
			<div class="results dropdown-menu"></div>
		</div>
	</li>

	<li>
		<div class="btn-group" data-toggle="buttons">
			<button name="font_size" class="btn btn-default btn-sm" type="button" value="decrease"><span style="font-size: .8em;">A</span></button>
			<button name="font_size" class="btn btn-default btn-sm" type="button" value="increase"><span style="font-size: 1.25em;">A</span></button>
		</div>
	</li>

	<li class="theme-toggle">
		<?php echo f::form_toggle('theme', ['light' => f::draw_fonticon('icon-sun'), 'dark' => f::draw_fonticon('icon-moon')], (!empty($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light', 'dark'])) ? $_COOKIE['theme'] : 'light'); ?>
	</li>

	<?php foreach ($items as $item) echo $draw_menu_item($item); ?>

</ul>

<script>
	$('label:has(input[name="theme"][value="light"])').attr('title', '<?php echo f::escape_js(t('title_light_mode', 'Light Mode')); ?>');
	$('label:has(input[name="theme"][value="dark"])').attr('title', '<?php echo f::escape_js(t('title_dark_mode', 'Dark Mode')); ?>');
</script>
