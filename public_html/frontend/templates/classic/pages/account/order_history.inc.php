<main id="main" class="container">
	<div id="sidebar">
		<div id="column-left">
			<?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
		</div>
	</div>

	<div id="content">
		{{notices}}

		<section id="box-order-history" class="card">

			<div class="card-header">
				<h1 class="card-title"><?php echo language::translate('title_order_history', 'Order History'); ?></h1>
			</div>

			<table class="table table-striped table-hover data-table">
				<thead>
					<tr>
						<th class="main"><?php echo language::translate('title_order', 'Order'); ?></th>
						<th class="text-center"><?php echo language::translate('title_order_status', 'Order Status'); ?></th>
						<th class="text-end"><?php echo language::translate('title_amount', 'Amount'); ?></th>
						<th class="text-end"><?php echo language::translate('title_date', 'Date'); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php if ($orders) foreach ($orders as $order) { ?>
					<tr>
						<td><a href="<?php echo functions::escape_html($order['link']); ?>" class="lightbox-iframe"><?php echo language::translate('title_order', 'Order'); ?> #<?php echo $order['id']; ?></a></td>
						<td class="text-center"><?php echo $order['order_status']; ?></td>
						<td class="text-end"><?php echo $order['total']; ?></td>
						<td class="text-end"><?php echo $order['date_created']; ?></td>
						<td class="text-end"><a href="<?php echo functions::escape_html($order['printable_link']); ?>" target="_blank"><?php echo functions::draw_fonticon('fa-print'); ?></a></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>

			<?php if ($pagination) { ?>
			<div class="card-footer">
				<?php echo $pagination; ?>
			</div>
			<?php } ?>
		</section>
	</div>
</main>