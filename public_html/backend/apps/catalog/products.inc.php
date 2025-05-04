<?php

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	document::$title[] = language::translate('title_products', 'Products');

	breadcrumbs::add(language::translate('title_products', 'Products'));

	if (isset($_POST['enable']) || isset($_POST['disable'])) {

		try {

			if (empty($_POST['products'])) {
				throw new Exception(language::translate('error_must_select_products', 'You must select products'));
			}

			foreach ($_POST['products'] as $product_id) {
				$product = new ent_product($product_id);
				$product->data['status'] = !empty($_POST['enable']) ? 1 : 0;
				$product->save();
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['clone'])) {

		try {
			if (empty($_POST['products'])) {
				throw new Exception(language::translate('error_must_select_products', 'You must select products'));
			}

			if (empty($_POST['category_id'])) {
				throw new Exception(language::translate('error_must_select_category', 'You must select a category'));
			}

			foreach ($_POST['products'] as $product_id) {

				$original = new ent_product($product_id);
				$product = new ent_product();

				$product->data = $original->data;
				$product->data['id'] = null;
				$product->data['status'] = 0;
				$product->data['code'] = '';
				$product->data['sku'] = '';
				$product->data['mpn'] = '';
				$product->data['gtin'] = '';
				$product->data['categories'] = [$_POST['category_id']];
				$product->data['quantity'] = 0;
				$product->data['image'] = null;
				$product->data['images'] = [];

				foreach (['attributes', 'campaigns', 'stock_options'] as $field) {
					if (empty($product->data[$field])) continue;
					foreach (array_keys($product->data[$field]) as $key) {
						$product->data[$field][$key]['id'] = null;
					}
				}

				if (!empty($original->data['images'])) {
					foreach ($original->data['images'] as $image) {
						$product->add_image('storage://images/' . $image['filename']);
					}
				}

				foreach (array_keys($product->data['name']) as $language_code) {
					$product->data['name'][$language_code] .= ' (copy)';
				}

				$product->data['status'] = 0;
				$product->save();
			}

			notices::add('success', sprintf(language::translate('success_cloned_d_products', 'Cloned %d products'), count($_POST['products'])));
			redirect(document::ilink(null, ['category_id' => $_POST['category_id']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($_POST['products'])) {
				throw new Exception(language::translate('error_must_select_products', 'You must select products'));
			}

			foreach ($_POST['products'] as $product_id) {
				$product = new ent_product($product_id);
				$product->delete();
			}

			notices::add('success', sprintf(language::translate('success_deleted_d_products', 'Deleted %d products'), count($_POST['products'])));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (!empty($_GET['query'])) {

		$code_regex = functions::format_regex_code($_GET['query']);
		$query_fulltext = functions::escape_mysql_fulltext($_GET['query']);

		$sql_select_relevance = (
			"(
				if(p.id = '". database::input($_GET['query']) ."', 10, 0)

				+ if(p.code regexp '". database::input($code_regex) ."', 5, 0)

				+ (match(json_value(p.name, '$.". database::input(language::$selected['code']) ."') against ('*". database::input($query_fulltext) ."*'))
				+ (match(json_value(p.short_description, '$.". database::input(language::$selected['code']) ."') against ('". database::input($query_fulltext) ."')/2)
				+ (match(json_value(p.description, '$.". database::input(language::$selected['code']) ."') against ('". database::input($query_fulltext) ."')/3)

				+ (match(json_value(p.name, '$.". database::input(language::$selected['code']) ."') against ('". database::input($query_fulltext) ."' in boolean mode))
				+ (match(json_value(p.short_description, '$.". database::input(language::$selected['code']) ."') against ('". database::input($query_fulltext) ."' in boolean mode) /2)
				+ (match(json_value(p.description, '$.". database::input(language::$selected['code']) ."') against ('". database::input($query_fulltext) ."' in boolean mode) /3)

				+ if(json_value(p.name, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%', 3, 0)
				+ if(json_value(p.short_description, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%', 2, 0)
				+ if(json_value(p.description, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%', 1, 0)

				+ if (p.id in (
					select product_id from ". DB_TABLE_PREFIX ."products_stock_options
					where stock_item_id in (
						select id from ". DB_TABLE_PREFIX ."stock_items
						where sku regexp '". database::input($code_regex) ."'
						or gtin regexp '". database::input($code_regex) ."'
					)
				), 5, 0)

				+ if(b.name like '%". database::input($_GET['query']) ."%', 3, 0)

				+ if(s.name like '%". database::input($_GET['query']) ."%', 2, 0)

			) as relevance"
		);
	}

	$sql_column_price = "coalesce(". implode(", ", array_map(function($currency) {
		return "if(json_value(price, '$.". database::input($currency['code']) ."') != 0, json_value(price, '$.". database::input($currency['code']) ."') * ". $currency['value'] .", null)";
	}, currency::$currencies)) .")";

	// Table Rows, Total Number of Rows, Total Number of Pages
	$products = database::query(
		"select p.id, p.status, p.code, p.image, p.sold_out_status_id, p.date_valid_from, p.date_valid_to, p.created_at,
			json_value(p.name, '$.". database::input(language::$selected['code']) ."') as name,
		 	pp.price, pc.campaign_price, pso.num_stock_options, pso.quantity, total_reserved, pso.quantity - oi.total_reserved as quantity_available
			". (!empty($sql_select_relevance) ? ", " . $sql_select_relevance : "") ."

		from ". DB_TABLE_PREFIX ."products p

		left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)

		left join ". DB_TABLE_PREFIX ."suppliers s on (s.id = p.supplier_id)

		left join (
			select product_id, $sql_column_price as price
			from ". DB_TABLE_PREFIX ."products_prices
		) pp on (pp.product_id = p.id)

		left join (
			select product_id, $sql_column_price as campaign_price
			from ". DB_TABLE_PREFIX ."campaigns_products
			where campaign_id in (
				select id from ". DB_TABLE_PREFIX ."campaigns
				where status
				and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
				and (date_valid_to is null or date_valid_to >= '". date('Y-m-d H:i:s') ."')
			)
			group by product_id
			order by $sql_column_price asc
			limit 1
		) pc on (pc.product_id = p.id)

		left join (
			select pso.id, pso.product_id, pso.stock_item_id, count(pso.stock_item_id) as num_stock_options, sum(si.quantity) as quantity
			from ". DB_TABLE_PREFIX ."products_stock_options pso
			left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
			group by pso.product_id
		) pso on (pso.product_id = p.id)

		left join (
			select oi.product_id, sum(oi.quantity) as total_reserved
			from ". DB_TABLE_PREFIX ."orders_items oi
			left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
			where o.order_status_id in (
				select id from ". DB_TABLE_PREFIX ."order_statuses
				where stock_action = 'reserve'
			)
			group by oi.product_id
		) oi on (oi.product_id = p.id)

		where true
		". (!empty($_GET['category_id']) ? "and p.id in (
			select product_id from ". DB_TABLE_PREFIX ."products_to_categories ptc
			where category_id = ". (int)$_GET['category_id'] ."
		)" : "") ."

		group by p.id
		". (!empty($sql_select_relevance) ? "having relevance > 0" : "") ."
		". (!empty($sql_select_relevance) ? "order by relevance desc" : "order by p.status desc, name asc") .";"
	)->fetch_page(null, null, $_GET['page'], null, $num_rows, $num_pages);

	foreach ($products as $i => $product) {

		try {

			if (!empty($product['date_valid_from']) && $product['date_valid_from'] < date('Y-m-d H:i:s')) {
				throw new Exception(strtr(language::translate('text_product_cannot_be_purchased_until_x', 'The product cannot be purchased until %date'), ['%date' => functions::datetime_format('date', $product['date_valid_from'])]));
			}

			if (!empty($product['date_valid_to']) && $product['date_valid_to'] < date('Y-m-d H:i:s')) {
				throw new Exception(strtr(language::translate('text_product_expired_at_x', 'The product expired at %date and can no longer be purchased'), ['%date' => functions::datetime_format('date', $product['date_valid_to'])]));
			}

			if ($product['num_stock_options'] && $product['quantity'] <= 0) {
				throw new Exception(language::translate('text_product_is_out_of_stock', 'The product is out of stock'));
			}

		} catch (Exception $e) {
			$products[$i]['warning'] = $e->getMessage();
		}
	}

?>
<style>
.icon-exclamation-triangle {
	color: #f00;
}
table .thumbnail {
	display: inline-block;
	vertical-align: middle;
	width: 32px;
	height: 32px;
	border-radius: 4px;
	max-width: unset;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_products', 'Products'); ?>
		</div>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_product'), language::translate('title_create_new_product', 'Create New Product'), '', 'create'); ?>
	</div>

	<?php echo functions::form_begin('search_form', 'get'); ?>
		<div class="card-filter">
			<div style="min-width: 300px;"><?php echo functions::form_select_category('category_id', true); ?></div>
			<div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::ilink(null, [], true, ['page', 'query']) .'&query=\' + encodeURIComponent(this.value))"'); ?></div>
			<div><?php echo functions::form_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
		</div>
	<?php echo functions::form_end(); ?>

	<?php echo functions::form_begin('products_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th></th>
					<th class="text-center"><?php echo language::translate('title_id', 'ID'); ?></th>
					<th style="min-width: 52px;"></th>
					<th><?php echo language::translate('title_name', 'Name'); ?></th>
					<th class="main"><?php echo language::translate('title_code', 'Code'); ?></th>
					<th class="text-end"><?php echo language::translate('title_price', 'Price'); ?></th>
					<th><?php echo language::translate('title_stock_options', 'Stock Options'); ?></th>
					<th class="text-end"><?php echo language::translate('title_reserved'); ?></th>
					<th class="text-end"><?php echo language::translate('title_created', 'Created'); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($products as $product) { ?>
				<tr class="<?php if (empty($product['status'])) echo 'semi-transparent'; ?>">
					<td><?php echo functions::form_checkbox('products[]', $product['id']); ?></td>
					<td><?php echo functions::draw_fonticon($product['status'] ? 'on' : 'off'); ?></td>
					<td class="warning"><?php if (!empty($product['warning'])) echo functions::draw_fonticon('icon-exclamation-triangle', 'title="'. functions::escape_attr($product['warning']) .'"'); ?></td>
					<td class="text-center"><?php echo $product['id']; ?></td>
					<td><?php echo functions::draw_thumbnail('storage://images/' . ($product['image'] ?: 'no_image.svg'), 64, 64, settings::get('product_image_clipping')); ?></td>
					<td><a class="link" href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => $product['id']]); ?>"><?php echo $product['name'] ?: '('. language::translate('title_untitled', 'Untitled') .')'; ?></a></td>
					<td><?php echo $product['code']; ?></td>
					<td class="text-end"><?php echo functions::draw_price_tag($product['price'], $product['campaign_price'], settings::get('store_currency_code')); ?></td>
					<td class="text-center"><?php echo $product['num_stock_options']; ?></td>
					<td class="text-center"><?php echo $product['total_reserved']; ?></td>
					<td class="text-end"><?php echo functions::datetime_when($product['created_at']); ?></td>
					<td class="text-end"><a class="btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_product', ['product_id' => $product['id'], 'redirect_url' => document::link()]); ?>" title="<?php echo language::translate('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="11"><?php echo language::translate('title_products', 'Products'); ?>: <?php echo language::number_format($num_rows); ?></td>
				</tr>
			</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">

				<legend>
					<?php echo language::translate('text_with_selected', 'With selected'); ?>:
				</legend>

				<div class="flex">

					<div class="btn-group">
						<?php echo functions::form_button_predefined('enable'); ?>
						<?php echo functions::form_button_predefined('disable'); ?>
					</div>

					<?php echo functions::form_button('clone', language::translate('title_clone', 'Clone'), 'submit', '', 'icon-copy'); ?>

					<?php echo functions::form_button_predefined('delete'); ?>

				</div>
			</fieldset>
		</div>

	<?php echo functions::form_end(); ?>

	<?php if ($num_pages > 1) { ?>
	<div class="card-footer">
		<?php echo functions::draw_pagination($num_pages); ?>
	</div>
	<?php } ?>
</div>

<script>
	$('input[name="category_id"]').on('change', function(e) {
		$(this).closest('form').submit();
	});

	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');

	$('form[name="search_form"]').on('input change', function(e) {
		e.preventDefault();
		$.get('', $(this).serialize(), function(response) {
			$('.data-table tbody').html($(response).find('.data-table tbody').html());
			$('.data-table tfoot').html($(response).find('.data-table tfoot').html());
			$('.card-footer').after($(response).find('.card-footer').html()).remove();
		});
	});
</script>
