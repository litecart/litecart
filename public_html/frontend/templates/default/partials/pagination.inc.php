<ul class="pagination">
	<?php foreach ($items as $item) { ?>
		<?php if ($item['disabled']) { ?>
		<li data-page="<?php echo $item['page']; ?>" class="disabled"><span><?php echo $item['title']; ?></span></li>
		<?php } else { ?>
		<li data-page="<?php echo $item['page']; ?>"<?php if ($item['active']) echo ' class="active"'; ?>><a href="<?php echo functions::escape_html($item['link']); ?>"><?php echo $item['title']; ?></a></li>
		<?php } ?>
	<?php } ?>
</ul>

<script>
	$('body').on('click', '.pagination a', function(e){
		e.preventDefault()
		let container = '#'+$(this).closest('[id]').attr('id')
		let page = $(this).closest('li').data('page')
		let url = $(this).attr('href')
		$(container).load(url + ' ' + container, () => {
			history.pushState({page: page}, document.title, url)
			$(document).scrollTop(1)
		})
	})

	$(window).on('popstate', function(e){
		let container = '#'+$('.pagination').closest('[id]').attr('id')
		$(container).load(location.href + ' ' + container)
	})
</script>
