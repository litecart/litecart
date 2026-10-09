<nav class="pagination">
	<?php foreach ($items as $item) { ?>
		<?php if ($item['disabled']) { ?>
		<span class="pagination-item disabled" data-page="<?= $item['page'] ?>">
			<?= $item['title'] ?>
		</span>
		<?php } else { ?>
		<a class="pagination-item<?php if ($item['active']) echo ' active'; ?>" href="<?= f::escape_html($item['link']) ?>" data-page="<?= $item['page'] ?>">
			<?= $item['title'] ?>
		</a>
		<?php } ?>
	<?php } ?>
</nav>
