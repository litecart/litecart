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
	<iframe id="order-copy" src="<?= document::ilink('printable_order_copy', [], ['order_no', 'public_key']) ?>" style="border: 0;" title="<?= f::escape_attr(t('title_order_copy', 'Order Copy')) ?>"></iframe>

	<div id="sidebar" class="hidden-print shadow">

		<ul id="actions" class="list-unstyled">
			<li><button id="print" class="btn btn-default btn-block btn-lg" type="button" aria-label="<?= f::escape_attr(t('title_print', 'Print')) ?>"><?= f::draw_fonticon('icon-print', 'aria-hidden="true"') ?> <?= t('title_print', 'Print') ?></button></li>
		</ul>

		<h1 style="margin-top: 0;"><?= t('title_comments', 'Comments') ?></h1>

		<div id="comments" class="bubbles" role="log" aria-live="polite" aria-label="<?= f::escape_attr(t('title_comments', 'Comments')) ?>">
			<?php foreach ($comments as $comment) { ?>
			<div class="bubble <?= $comment['type'] ?>">
				<div class="text"><?= nl2br($comment['text']) ?></div>
				<div class="date"><?= f::datetime_when($comment['created_at']) ?></div>
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