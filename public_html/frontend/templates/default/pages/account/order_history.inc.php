<main id="main" class="container">
	<div class="grid">
		<div class="col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_account_links.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">
				{{notices}}

				<section id="box-order-history" class="card" aria-label="<?php echo f::escape_attr(t('title_order_history', 'Order History')); ?>">

					<div class="card-header">
						<h1 class="card-title"><?php echo t('title_order_history', 'Order History'); ?></h1>
					</div>

					<table class="table data-table">
						<caption class="hidden"><?php echo t('title_order_history', 'Order History'); ?></caption>
						<thead>
						<tr>
							<th scope="col" class="main"><?php echo t('title_order', 'Order'); ?></th>
							<th scope="col" class="text-end"></th>
							<th scope="col" class="text-center"><?php echo t('title_order_status', 'Order Status'); ?></th>
							<th scope="col" class="text-end"><?php echo t('title_amount', 'Amount'); ?></th>
							<th scope="col" class="text-end"><?php echo t('title_date', 'Date'); ?></th>
							<th scope="col"><span class="hidden"><?php echo t('title_actions', 'Actions'); ?></span></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($orders as $order) { ?>
						<tr>
							<td><a href="<?php echo f::escape_html($order['link']); ?>" class="lightbox-iframe"><?php echo $order['no']; ?></a></td>
							<td class="text-center"><?php echo $order['num_downloads'] ? '<a href="'. document::href_ilink('downloads') .'">'. t('title_downloads', 'Downloads') .'</a>' : ''; ?></td>
							<td class="text-center"><?php echo $order['order_status']; ?></td>
							<td class="text-end"><?php echo $order['total']; ?></td>
							<td class="text-end"><?php echo $order['created_at']; ?></td>
							<td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo f::escape_html($order['printable_link']); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo f::escape_html(t('title_print', 'Print')); ?>" aria-label="<?php echo f::escape_attr(t('title_print', 'Print') .': '. $order['no']); ?>"><?php echo f::draw_fonticon('icon-print', 'aria-hidden="true"'); ?></a></td>
						</tr>
						<?php } ?>
						</tbody>
					</table>

					<?php if ($pagination) { ?>
					<div class="card-footer">
						{{pagination}}
					</div>
					<?php } ?>
				</section>
		</div>
	</div>
</main>
