<?php

	document::$title[] = language::translate('title_category_tree', 'Category Tree');

	breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
	breadcrumbs::add(language::translate('title_category_tree', 'Category Tree'), document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
			}

			if (!empty($_POST['categories'])) {
				foreach ($_POST['categories'] as $category_id) {
					$category = new ent_category($category_id);
					$category->data['status'] = !empty($_POST['enable']) ? 1 : 0;
					$category->save();
				}
			}

			if (!empty($_POST['products'])) {
				foreach ($_POST['products'] as $product_id) {
					$product = new ent_product($product_id);
					$product->data['status'] = !empty($_POST['enable']) ? 1 : 0;
					$product->save();
				}
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['clone'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
			}

			if (!empty($_POST['categories'])) {
				foreach ($_POST['categories'] as $category_id) {
					$original_category = new ent_category($category_id);
					$new_category = new ent_category();

					$new_category->data = $original_category->data;
					$new_category->data['id'] = null;
					$new_category->data['status'] = 0;
					$new_category->data['code'] = '';

					foreach (array_keys($new_category->data['name']) as $language_code) {
						$new_category->data['name'][$language_code] .= ' (copy)';
					}

					if (!empty($original_category->data['image'])) {
						$new_category->save_image('storage://images/' . $original_category->data['image']);
					}

					$new_category->save();
				}
			}

			if (!empty($_POST['products'])) {
				foreach ($_POST['products'] as $product_id) {
					$original_product = new ent_product($product_id);
					$new_product = new ent_product();

					$new_product->data = $original_product->data;
					$new_product->data['id'] = null;
					$new_product->data['status'] = 0;
					$new_product->data['code'] = '';
					$new_product->data['categories'] = [$_POST['category_id']];
					$new_product->data['image'] = null;
					$new_product->data['images'] = [];

					foreach (['attributes', 'campaigns', 'stock_options'] as $field) {
						if (empty($new_product->data[$field])) continue;
						foreach (array_keys($new_product->data[$field]) as $key) {
							$new_product->data[$field][$key]['id'] = null;
						}
					}

					if (!empty($original_product->data['images'])) {
						foreach ($original_product->data['images'] as $image) {
							$new_product->add_image('storage://images/' . $image['filename']);
						}
					}

					foreach (array_keys($new_product->data['name']) as $language_code) {
						$new_product->data['name'][$language_code] .= ' (copy)';
					}

					$new_product->save();
				}
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(null, ['category_id' => $_POST['category_id']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['copy'])) {
		try {

			if (!empty($_POST['categories'])) {
				throw new Exception(language::translate('error_cant_copy_category', 'You can\'t copy a category'));
			}

			if (empty($_POST['products'])) {
				throw new Exception(language::translate('error_must_select_products', 'You must select products'));
			}

			if (isset($_POST['category_id']) && $_POST['category_id'] == '') {
				throw new Exception(language::translate('error_must_select_category', 'You must select a category'));
			}

			if (!empty($_POST['products'])) {
				foreach ($_POST['products'] as $product_id) {
					$product = new ent_product($product_id);
					$product->data['categories'][] = $_POST['category_id'];
					$product->save();
				}
			}

			notices::add('success', sprintf(language::translate('success_copied_d_products', 'Copied %d products'), count($_POST['products'])));
			header('Location: '. document::ilink(null, ['category_id' => $_POST['category_id']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['move'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
			}

			if (isset($_POST['category_id']) && $_POST['category_id'] == '') {
				throw new Exception(language::translate('error_must_select_category', 'You must select a category'));
			}

			if (isset($_POST['category_id']) && isset($_POST['categories']) && in_array($_POST['category_id'], $_POST['categories'])) {
				throw new Exception(language::translate('error_cant_move_category_to_itself', 'You can\'t move a category to itself'));
			}

			if (isset($_POST['category_id']) && isset($_POST['categories'])) {
				foreach ($_POST['categories'] as $category_id) {
					if (in_array($_POST['category_id'], array_keys(reference::category($category_id)->descendants))) {
						throw new Exception(language::translate('error_cant_move_category_to_descendant', 'You can\'t move a category to a descendant'));
						break;
					}
				}
			}

			if (!empty($_POST['products'])) {
				foreach ($_POST['products'] as $product_id) {
					$product = new ent_product($product_id);
					$product->data['categories'] = [$_POST['category_id']];
					$product->save();
				}
			}

			if (!empty($_POST['categories'])) {
				foreach ($_POST['categories'] as $category_id) {
					$category = new ent_category($category_id);
					$category->data['parent_id'] = $_POST['category_id'];
					$category->save();
				}
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink(null, ['category_id' => $_POST['category_id']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['unmount'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
			}

			if (empty($_GET['category_id'])) {
				throw new Exception(language::translate('error_category_must_be_nested_in_another_category_to_unmount', 'A category must be nested in another category to be unmounted'));
			}

			if (!empty($_POST['categories'])) {
				foreach ($_POST['categories'] as $category_id) {
					$category = new ent_category($category_id);
					if ($category->data['parent_id'] == $_GET['category_id']) {
						$category->data['parent_id'] = 0;
						$category->save();
					}
				}
			}

			if (!empty($_POST['products'])) {
				foreach ($_POST['products'] as $product_id) {
					$product = new ent_product($product_id);
					foreach (array_keys($product->data['categories']) as $key) {
						if ($product->data['categories'][$key] == $_GET['category_id']) {
							unset($product->data['categories'][$key]);
							$product->save();
						}
					}
				}
			}

			if (isset($_POST['categories']) && in_array($_GET['category_id'], $_POST['categories'])) {
				unset($_GET['category_id']);
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(language::translate('error_must_select_category_or_product', 'You must select a category or product'));
			}

			if (!empty($_POST['products'])) {
				foreach ($_POST['products'] as $product_id) {
					$product = new ent_product($product_id);
					$product->delete();
				}
			}

			if (!empty($_POST['categories'])) {
				foreach (array_reverse($_POST['categories']) as $category_id) {
					$category = new ent_category($category_id);
					$category->delete();
				}
			}

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::ilink());
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$category_branches = [];
	$opened_categories = [];
	$matched_categories = [];
	$matched_products = [];

	// Search Filter
	if (!empty($_GET['query'])) {

		$code_regex = functions::format_regex_code($_GET['query']);

		$matched_products = database::query(
			"select id from ". DB_TABLE_PREFIX ."products
			where id = '". database::input($_GET['query']) ."'
			or find_in_set('". database::input($_GET['query']) ."', keywords)
			or id in (
				select product_id from ". DB_TABLE_PREFIX ."products_info
				where name like '%". database::input($_GET['query']) ."%'
				or short_description like '%". database::input($_GET['query']) ."%'
				or description like '%". database::input($_GET['query']) ."%'
				or match(short_description) against ('*". database::input_fulltext($_GET['query']) ."*')
				or match(description) against ('*". database::input_fulltext($_GET['query']) ."*' in boolean mode)
				or find_in_set('". database::input($_GET['query']) ."', synonyms)
			)
			or id in (
				select product_id from ". DB_TABLE_PREFIX ."products_stock_options
				where stock_item_id in (
					select id from ". DB_TABLE_PREFIX ."stock_items
					where sku regexp '". database::input($code_regex) ."'
					or gtin regexp '". database::input($code_regex) ."'
				)
			)
			or brand_id in (
				select id from ". DB_TABLE_PREFIX ."brands
				where name like '%". database::input($_GET['query']) ."%'
			)
			or supplier_id in (
				select id from ". DB_TABLE_PREFIX ."brands
				where name like '%". database::input($_GET['query']) ."%'
			);"
		)->fetch_all('id');

		$matched_categories = database::query(
			"select distinct id from ". DB_TABLE_PREFIX ."categories
			where id = '". database::input($_GET['query']) ."'
			or find_in_set('". database::input($_GET['query']) ."', keywords)
			or id in (
				select category_id from ". DB_TABLE_PREFIX ."categories_info
				where name like '%". database::input($_GET['query']) ."%'
				or description like '%". database::input($_GET['query']) ."%'
				or match(name) against ('*". database::input_fulltext($_GET['query']) ."*')
				or match(description) against ('". database::input_fulltext($_GET['query']) ."' in boolean mode)
			)
			or id in (
				select distinct category_id from ". DB_TABLE_PREFIX ."products_to_categories
				where product_id in ('". implode("', '", database::input($matched_products)) ."')
			);"
		)->fetch_all('id');

		foreach ($matched_categories as $category_id) {
			$category = reference::category($category_id);
			$category_branches[] = $category->parent_id ? $category->main_category->id : $category->id;
			$opened_categories = array_merge($opened_categories, array_keys($category->path));
		}

	} else {
		$category_branches = database::query(
			"select distinct id from ". DB_TABLE_PREFIX ."categories
			where parent_id is null;"
		)->fetch_all('id');
		$opened_categories = !empty($_GET['category_id']) ? array_keys(reference::category($_GET['category_id'])->path) : [];
	}

	$num_category_rows = 0;
	$num_product_rows = 0;

?>
<style>
.warning .fa {
	color: #f00;
}

.thumbnail {
	display: inline-block;
	width: 24px;
	height: 24px;
	vertical-align: middle;
}

.icon-folder,
.icon-folder-open,
td .thumbnail {
	margin-inline-end: 16px;
}

table .icon-folder,
table .icon-folder-open {
	font-size: 1.5em;
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo language::translate('title_catalog', 'Catalog'); ?>
		</div>
	</div>

	<div class="card-action">
		<ul class="list-inline">
			<li><?php echo functions::form_button_link(document::ilink(__APP__.'/edit_category', isset($_GET['category_id']) ? ['parent_id' => $_GET['category_id']] : []), language::translate('title_create_new_category', 'Create New Category'), '', 'add'); ?></li>
			<li><?php echo functions::form_button_link(document::ilink(__APP__.'/edit_product', [], ['category_id']), language::translate('title_create_new_product', 'Create New Product'), '', 'add'); ?></li>
		</ul>
	</div>

	<?php echo functions::form_begin('search_form', 'get'); ?>
		<div class="card-filter">
			<div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. language::translate('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::ilink('', [], true, ['page', 'query']) .'&query=\' + encodeURIComponent(this.value))"'); ?></div>
			<div><?php echo functions::form_button('filter', language::translate('title_search', 'Search'), 'submit'); ?></div>
		</div>
	<?php echo functions::form_end(); ?>

	<?php echo functions::form_begin('catalog_form', 'post'); ?>

		<table class="table table-striped table-hover data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th></th>
					<th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
					<th class="text-end"><?php echo language::translate('title_price', 'Price'); ?></th>
					<th></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td>
						<?php echo functions::draw_fonticon('icon-folder-open', 'style="color: #cc6;"'); ?>
						<a href="'. document::href_ilink(null, [], [], []) .'">
							<strong>[<?php echo language::translate('title_root', 'Root'); ?>]</strong>
						</a>
					</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
<?php
	$draw_category_branch = function($category_id, $depth=1) use (&$draw_category_branch, $opened_categories, $matched_categories, $matched_products, &$num_category_rows) {

		$output = '';

		$category = database::query(
			"select c.id, c.status, ci.name
			from ". DB_TABLE_PREFIX ."categories c
			left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
			where c.id = ". (int)$category_id ."
			order by c.priority asc, ci.name asc;"
		)->fetch();

		$num_category_rows++;

		$category['properties'] = [];

		if (isset($_GET['category_id']) && $category['id'] == $_GET['category_id']) {
			$category['properties'][] = 'active';
		}

		if (in_array($category['id'], $opened_categories)) {
			$category['properties'][] = 'opened';
		}

		$output .= implode(PHP_EOL, [
			'<tr class="'. ($category['status'] ? null : ' semi-transparent') .'">',
			'  <td>'. functions::form_checkbox('categories[]', $category['id'], true) .'</td>',
			'  <td>'. functions::draw_fonticon($category['status'] ? 'on' : 'off') .'</td>',
			'  <td></td>',
			'  <td style="padding-inline-start: '. ($depth+1) .'em;">',
			'    '. functions::draw_fonticon(in_array('opened', $category['properties']) ? 'icon-folder-open' : 'icon-folder', 'style="color: #cc6;"'),
			'    '. (in_array('active', $category['properties']) ? '<strong>' : '<a class="link" href="'. document::href_ilink(null, ['category_id' => $category['id']]) .'">'),
			'      ' . ($category['name'] ?: '[untitled]'),
			'    '. (in_array('opened', $category['properties']) ? '</strong>' : '</a>'),
			'  </td>',
			'  <td></td>',
			'  <td>',
			'    <a class="btn btn-default btn-sm" href="'. document::href_ilink('f:category', ['category_id' => $category['id']]) .'" target="_blank">',
			'    '.  functions::draw_fonticon('icon-square-out'),
			'    </a>',
			'  </td>',
			'  <td class="text-end">',
			'    <a class="btn btn-default btn-sm" href="'. document::href_ilink(__APP__.'/edit_category', ['category_id' => $category['id']]) .'" title="'. language::translate('title_edit', 'Edit') .'">',
			'    '. functions::draw_fonticon('icon-pencil'),
			'    </a>',
			'  </td>',
			'</tr>',
		]);

		if (in_array($category['id'], $opened_categories)) {

			$has_subcategories = database::query(
				"select id from ". DB_TABLE_PREFIX ."categories
				where parent_id = ". (int)$category['id'] ."
				limit 1;"
			)->num_rows ? true : false;

			$has_products	= database::query(
				"select product_id from ". DB_TABLE_PREFIX ."products_to_categories
				where category_id = ". (int)$category['id']."
				limit 1;"
			)->num_rows ? true : false;

			if ($has_subcategories || $has_products) {

				// Output subcategories
				$subcategories = database::query(
					"select c.id, c.status, ci.name
					from ". DB_TABLE_PREFIX ."categories c
					left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')
					where c.parent_id = ". (int)$category['id'] ."
					". (!empty($_GET['query']) ? "and c.id in ('". implode("', '", database::input($matched_categories)) ."')" : "") ."
					order by ci.name;"
				)->fetch_all();

				foreach ($subcategories as $subcategory) {
					$output .= $draw_category_branch($subcategory['id'], $depth+1);
				}

				// Output products
				$products = database::query(
					"select p.id, p.status, p.code, p.sold_out_status_id, p.image, pi.name, pp.price, pso.num_stock_options, pso.quantity, pso.quantity - oi.total_reserved as quantity_available, p.date_valid_from, p.date_valid_to, ptc.category_id
					from ". DB_TABLE_PREFIX ."products p
					left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
					left join ". DB_TABLE_PREFIX ."products_to_categories ptc on (ptc.product_id = p.id)

					left join (
						select product_id, `". database::input(settings::get('store_currency_code')) ."` as price
						from ". DB_TABLE_PREFIX ."products_prices
					) pp on (pp.product_id = p.id)

					left join (
						select pso.id, pso.product_id, pso.stock_item_id, count(pso.stock_item_id) as num_stock_options, sum(si.quantity) as quantity
						from ". DB_TABLE_PREFIX ."products_stock_options pso
						left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
						group by pso.product_id
					) pso on (pso.product_id = p.id)

					left join (
						select oi.stock_option_id, sum(oi.quantity) as total_reserved
						from ". DB_TABLE_PREFIX ."orders_items oi
						left join ". DB_TABLE_PREFIX ."products_stock_options pso on (pso.id = oi.stock_option_id)
						where oi.order_id in (
							select id from ". DB_TABLE_PREFIX ."orders o
							where order_status_id in (
								select id from ". DB_TABLE_PREFIX ."order_statuses os
								where stock_action = 'reserve'
							)
						)
						group by pso.id
					) oi on (oi.stock_option_id = pso.id)

					where ". (!empty($category['id']) ? "p.id in (
						select product_id from ". DB_TABLE_PREFIX ."products_to_categories ptc
						where category_id = ". (int)$category['id'] ."
					)" : "ptc.category_id is null") ."

					". (!empty($_GET['query']) ? "and p.id in ('". implode("', '", database::input($matched_products)) ."')" : "") ."

					group by p.id
					order by pi.name asc;"
				)->fetch_all();

				foreach ($products as $product) {

					try {

						$product['warning'] = null;

						if (!empty($product['date_valid_from']) && strtotime($product['date_valid_from']) > time()) {
							throw new Exception(strtr(language::translate('text_product_cannot_be_purchased_until_x', 'The product cannot be purchased until %date'), ['%date' => language::strftime('date', $product['date_valid_from'])]));
						}

						if (!empty($product['date_valid_to']) && strtotime($product['date_valid_to']) < time()) {
							throw new Exception(strtr(language::translate('text_product_expired_at_x', 'The product expired at %date and can no longer be purchased'), ['%date' => language::strftime('date', $product['date_valid_to'])]));
						}

						if ($product['num_stock_options'] && $product['quantity'] <= 0) {
							throw new Exception(language::translate('text_product_is_out_of_stock', 'The product is out of stock'));
						}

					} catch (Exception $e) {
						$product['warning'] = $e->getMessage();
					}

					$output .= implode(PHP_EOL, [
						'<tr class="'. (!$product['status'] ? ' semi-transparent' : '') .'">',
						'  <td>'. functions::form_checkbox('products[]', $product['id'], true) .'</td>',
						'  <td>'. functions::draw_fonticon(!empty($product['status']) ? 'on' : 'off') .'</td>',
						'  <td class="warning">'. (!empty($warning) ? functions::draw_fonticon('icon-exclamation-triangle', 'title="'. functions::escape_attr($warning) .'"') : '') .'</td>',
						'  <td style="padding-inline-start: '. ($depth+2) .'em;">',
						'    '. empty($display_images) ? functions::draw_thumbnail('storage://images/' . $product['image'], 24, 24, 'fit') : '<span style="margin-inline-start: '. (($depth+1)*16) .'px;"></span>',
						'    <a class="link" href="'. document::href_ilink(__APP__.'/edit_product', ['category_id' => $category_id, 'product_id' => $product['id']]) .'">',
						'      '. ($product['name'] ?: '['. language::translate('title_untitled', 'Untitled') .']'),
						'    </a>',
						'  </td>',
						'  <td class="text-end">'. currency::format($product['price']) .'</td>',
						'  <td>',
						'    <a class="btn btn-default btn-sm" href="'. document::href_ilink('f:product', ['product_id' => $product['id']]) .'" title="'. language::translate('title_view', 'View') .'" target="_blank">',
						'    '. functions::draw_fonticon('icon-square-out'),
						'    </a>',
						'  </td>',
						'  <td class="text-end">',
						'    <a class="btn btn-default btn-sm" href="'. document::href_ilink(__APP__.'/edit_product', ['category_id' => $category_id, 'product_id' => $product['id']]) .'" title="'. language::translate('title_edit', 'Edit') .'">',
						'    '. functions::draw_fonticon('icon-pencil'),
						'    </a>',
						'  </td>',
						'</tr>',
					]);
				}

			} else {

				$output .= implode(PHP_EOL, [
					'<tr>',
					'  <td></td>',
					'  <td></td>',
					'  <td></td>',
					'  <td><em style="margin-inline-start: '. (($depth+1)*16) .'px;">'. language::translate('title_empty', 'Empty') .'</em></td>',
					'  <td></td>',
					'  <td></td>',
					'  <td></td>',
					'</tr>',
				]);
			}
		}

		return $output;
	};

	foreach ($category_branches as $category_id) {
		echo $draw_category_branch($category_id);
	}

?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="7"><?php echo language::translate('title_categories', 'Categories'); ?>: <?php echo $num_category_rows; ?>, <?php echo language::translate('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?></td>
					</tr>
				</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">
				<legend><?php echo language::translate('text_with_selected', 'With selected'); ?>:</legend>

				<ul class="list-inline">
					<li>
						<div class="btn-group">
							<?php echo functions::form_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?>
							<?php echo functions::form_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?>
						</div>
					</li>
					<li>
						<div style="min-width: 250px;">
							<?php echo functions::form_select_category('category_id', true); ?>
						</div>
					</li>
					<li>
						<div class="btn-group">
							<?php echo functions::form_button('move', language::translate('title_move', 'Move'), 'submit', 'onclick="if (!window.confirm(\''. str_replace("'", "\\\'", language::translate('warning_mounting_points_will_be_replaced', 'Warning: All current mounting points will be replaced.')) .'\')) return false;"'); ?>
							<?php echo functions::form_button('copy', language::translate('title_copy', 'Copy'), 'submit'); ?>
							<?php echo functions::form_button('clone', language::translate('title_clone', 'Clone'), 'submit', '', 'icon-copy'); ?>
						</div>
					</li>
					<li>
						<?php echo functions::form_button('unmount', language::translate('title_unmount', 'Unmount'), 'submit'); ?>
					</li>
					<li>
						<?php echo functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. str_replace("'", "\\\'", language::translate('text_are_you_sure', 'Are you sure?')) .'\')) return false;"', 'delete'); ?>
					</li>
				</ul>
			</fieldset>
		</div>

	<?php echo functions::form_end(); ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length)
	}).first().trigger('change')
</script>