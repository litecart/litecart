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
