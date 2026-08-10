<meta name="viewport" content="width=1200">

<style>
body {
	display: flex;
	height: 100vh;
}

#order-copy {
	flex: 1 1 auto;
}

#sidebar {
	display: flex;
	flex-direction: column;
	flex: 0 0 360px;
	padding: 15px;
	background: #fff;
}

#actions {
	margin-bottom: 30px;
}

#comments {
	flex: 1 1 auto;
	overflow: hidden auto;
}
</style>

<main id="main">
	<iframe id="order-copy" src="<?php echo document::ilink('printable_order_copy', [], ['order_no', 'public_key']); ?>" style="border: 0;" title="<?php echo f::escape_attr(t('title_order_copy', 'Order Copy')); ?>"></iframe>

	<div id="sidebar" class="hidden-print shadow">

		<ul id="actions" class="list-unstyled">
			<li><button id="print" class="btn btn-default btn-block btn-lg" type="button" aria-label="<?php echo f::escape_attr(t('title_print', 'Print')); ?>"><?php echo f::draw_fonticon('icon-print', 'aria-hidden="true"'); ?> <?php echo t('title_print', 'Print'); ?></button></li>
		</ul>

		<h1 style="margin-top: 0;"><?php echo t('title_comments', 'Comments'); ?></h1>

		<div id="comments" class="bubbles" role="log" aria-live="polite" aria-label="<?php echo f::escape_attr(t('title_comments', 'Comments')); ?>">
			<?php foreach ($comments as $comment) { ?>
			<div class="bubble <?php echo $comment['type']; ?>">
				<div class="text"><?php echo nl2br($comment['text']); ?></div>
				<div class="date"><?php echo f::datetime_when($comment['created_at']); ?></div>
			</div>
			<?php } ?>
		</div>
	</div>
</main>

<script>
	$('#print').on('click', function() {
		$('#order-copy').get(0).contentWindow.print();
	})

	// Scroll to last comment
	$("#comments").animate({
		scrollTop: $('#comments').prop('scrollHeight')
	}, 2000);
</script>