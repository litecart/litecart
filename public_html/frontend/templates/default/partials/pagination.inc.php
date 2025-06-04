<ul class="pagination">
	<?php foreach ($items as $item) { ?>
		<?php if ($item['disabled']) { ?>
		<li class="pagination-item disabled" data-page="<?php echo $item['page']; ?>">
			<span><?php echo $item['title']; ?></span>
		</li>
		<?php } else { ?>
		<li class="pagination-item<?php if ($item['active']) echo ' active'; ?>" data-page="<?php echo $item['page']; ?>">
			<a href="<?php echo functions::escape_html($item['link']); ?>">
				<?php echo $item['title']; ?>
			</a>
		</li>
		<?php } ?>
	<?php } ?>
</ul>
