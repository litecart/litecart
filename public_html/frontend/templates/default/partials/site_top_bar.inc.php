<div id="site-toolbar" class="hidden-xs">
	<div class="wrapper text-center">

		<?php if ($important_notice = settings::get('important_notice')) { ?>
		<div id="important-message">
			<?php echo $important_notice; ?>
		</div>
		<?php } ?>

	</div>
</div>