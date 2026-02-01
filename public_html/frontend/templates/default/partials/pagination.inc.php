<nav class="pagination">
	<?php foreach ($items as $item) { ?>
		<?php if ($item['disabled']) { ?>
		<span class="pagination-item disabled" data-page="<?php echo $item['page']; ?>">
			<?php echo $item['title']; ?>
		</span>
		<?php } else { ?>
		<a class="pagination-item<?php if ($item['active']) echo ' active'; ?>" href="<?php echo f::escape_html($item['link']); ?>" data-page="<?php echo $item['page']; ?>">
			<?php echo $item['title']; ?>
		</a>
		<?php } ?>
	<?php } ?>
</nav>


<script>
	/*
	$('body').on('click', '.pagination a', function(e) {
		e.preventDefault();

		let container = '#'+$(this).closest('[id]').attr('id');
		let page = $(this).closest('li').data('page');
		let url = $(this).attr('href');

		$(container).load(url + ' ' + container, function() {
			history.pushState({page: page}, document.title, url);
			$(document).scrollTop(1);
		});
	});

	$(window).on('popstate', function(e) {
		let container = '#'+$('.pagination').closest('[id]').attr('id');
		$(container).load(location.href + ' ' + container);
	});
	*/
</script>
