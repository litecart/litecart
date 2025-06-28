<style>
.brightness .form-toggle {
	padding: 0 !important;
	gap: 0;
}
</style>

<ul id="toolbar" class="hidden-print">
	<li>
		<div>
			<label class="nav-toggle btn btn-default" for="sidebar-compact">
				<?php echo functions::draw_fonticon('icon-sidebar', 'style="font-size: 1.5em;"'); ?>
			</label>
		</div>
	</li>

	<li style="flex-grow: 1;">
		<div id="search" class="dropdown">
			<?php echo functions::form_input_search('query', false, 'placeholder="'. functions::escape_attr(t('title_search_entire_platform', 'Search entire platform')) .'&hellip;" autocomplete="off"'); ?>
			<div class="results dropdown-menu"></div>
		</div>
	</li>

	<li>
		<div class="btn-group" data-toggle="buttons">
			<button name="font_size" class="btn btn-default btn-sm" type="button" value="decrease"><span style="font-size: .8em;">A</span></button>
			<button name="font_size" class="btn btn-default btn-sm" type="button" value="increase"><span style="font-size: 1.25em;">A</span></button>
		</div>
	</li>

	<li class="brightness">
		<?php echo functions::form_toggle('dark_mode', ['0' => functions::draw_fonticon('icon-sun'), '1' => functions::draw_fonticon('icon-moon')]); ?>
	</li>

	<?php foreach ($items as $item) echo $draw_menu_item($item); ?>

</ul>
