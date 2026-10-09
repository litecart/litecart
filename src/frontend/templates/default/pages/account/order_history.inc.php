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

				<section id="box-order-history" class="card" aria-label="<?= f::escape_attr(t('title_order_history', 'Order History')) ?>">

					<div class="card-header">
						<h1 class="card-title"><?= t('title_order_history', 'Order History') ?></h1>
					</div>

					<table class="table data-table">
						<caption class="hidden"><?= t('title_order_history', 'Order History') ?></caption>
						<thead>
						<tr>
							<th scope="col" class="main"><?= t('title_order', 'Order') ?></th>
							<th scope="col" class="text-end"></th>
							<th scope="col" class="text-center"><?= t('title_order_status', 'Order Status') ?></th>
							<th scope="col" class="text-end"><?= t('title_amount', 'Amount') ?></th>
							<th scope="col" class="text-end"><?= t('title_date', 'Date') ?></th>
							<th scope="col"><span class="hidden"><?= t('title_actions', 'Actions') ?></span></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($orders as $order) { ?>
						<tr>
							<td><a href="<?= f::escape_html($order['link']) ?>" class="lightbox-iframe"><?= $order['no'] ?></a></td>
							<td class="text-center"><?= $order['num_downloads'] ? '<a href="'. document::href_ilink('downloads') .'">'. t('title_downloads', 'Downloads') .'</a>' : '' ?></td>
							<td class="text-center"><?= $order['order_status'] ?></td>
							<td class="text-end"><?= $order['total'] ?></td>
							<td class="text-end"><?= $order['created_at'] ?></td>
							<td class="text-end"><a class="btn btn-default btn-sm" href="<?= f::escape_html($order['printable_link']) ?>" target="_blank" rel="noopener noreferrer" title="<?= f::escape_html(t('title_print', 'Print')) ?>" aria-label="<?= f::escape_attr(t('title_print', 'Print') .': '. $order['no']) ?>"><?= f::draw_fonticon('icon-print', 'aria-hidden="true"') ?></a></td>
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
