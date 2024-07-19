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

<iframe id="order-copy" src="<?php echo document::ilink('printable_order_copy', [], ['order_id', 'public_key']); ?>" style="border: 0;"></iframe>

<?php if ($comments) { ?>
<div id="sidebar" class="hidden-print shadow">

	<h1 style="margin-top: 0;"><?php echo language::translate('title_comments', 'Comments'); ?></h1>

	<div id="comments" class="bubbles">
		<?php foreach ($comments as $comment) { ?>
		<div class="bubble <?php echo $comment['type']; ?>">
			<div class="text"><?php echo nl2br(functions::escape_html($comment['text']), false); ?></div>
			<div class="date"><?php echo language::strftime(language::$selected['format_datetime'], strtotime($comment['date_created'])); ?></div>
		</div>
		<?php } ?>
	</div>
</div>
<?php } ?>

<script>
	// Scroll to last comment
	$("#comments").animate({scrollTop: $('#comments').prop('scrollHeight')}, 2000);
</script>