<?php

	document::$title[] = t('title_category_tree', 'Category Tree');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_category_tree', 'Category Tree'), document::ilink());

	if (isset($_POST['enable']) || isset($_POST['disable'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(t('error_must_select_category_and_product', 'You must select a category or product'));
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

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['clone'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(t('error_must_select_select_category_and_product', 'You must select a category or product'));
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

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(null, ['category_id' => $_POST['category_id']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['copy'])) {
		try {

			if (!empty($_POST['categories'])) {
				throw new Exception(t('error_cant_copy_category', 'You can\'t copy a category'));
			}

			if (empty($_POST['products'])) {
				throw new Exception(t('error_must_select_products', 'You must select products'));
			}

			if (isset($_POST['category_id']) && $_POST['category_id'] == '') {
				throw new Exception(t('error_must_select_category', 'You must select a category'));
			}

			if (!empty($_POST['products'])) {
				foreach ($_POST['products'] as $product_id) {
					$product = new ent_product($product_id);
					$product->data['categories'][] = $_POST['category_id'];
					$product->save();
				}
			}

			notices::add('success', strtr(t('success_copied_d_products', 'Copied {n} products'), [
				'{n}' => count($_POST['products'])
			]));

			redirect(document::ilink(null, ['category_id' => $_POST['category_id']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['move'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(t('error_must_select_category_and_product', 'You must select a category or product'));
			}

			if (isset($_POST['category_id']) && $_POST['category_id'] == '') {
				throw new Exception(t('error_must_select_category', 'You must select a category'));
			}

			if (isset($_POST['category_id']) && isset($_POST['categories']) && in_array($_POST['category_id'], $_POST['categories'])) {
				throw new Exception(t('error_cant_move_category_to_itself', 'You can\'t move a category to itself'));
			}

			if (isset($_POST['category_id']) && isset($_POST['categories'])) {
				foreach ($_POST['categories'] as $category_id) {
					if (in_array($_POST['category_id'], array_keys(reference::category($category_id)->descendants))) {
						throw new Exception(t('error_cant_move_category_to_descendant', 'You can\'t move a category to a descendant'));
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

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(null, ['category_id' => $_POST['category_id']]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['unmount'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(t('error_must_select_category_and_product', 'You must select a category or product'));
			}

			if (empty($_GET['category_id'])) {
				throw new Exception(t('error_category_must_be_nested_in_another_category_to_unmount', 'A category must be nested in another category to be unmounted'));
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

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {
		try {

			if (empty($_POST['categories']) && empty($_POST['products'])) {
				throw new Exception(t('error_must_select_category_and_product', 'You must select a category or product'));
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

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
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
			or code regexp '". database::input($code_regex) ."'
			or (
				json_value(name, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%'
				or json_value(short_description, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%'
				or json_value(description, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%'
				or find_in_set('". database::input($_GET['query']) ."', json_value(synonyms, '$.". database::input(language::$selected['code']) ."'))
			)
			or (
				match(json_value(name, '$.". database::input(language::$selected['code']) ."') against ('*". database::input_fulltext($_GET['query']) ."*')
				or match(json_value(short_description, '$.". database::input(language::$selected['code']) ."') against ('*". database::input_fulltext($_GET['query']) ."*')
				or match(json_value(description, '$.". database::input(language::$selected['code']) ."') against ('*". database::input_fulltext($_GET['query']) ."*')
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
			or (
				json_value(name, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%'
				or json_value(short_description, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%'
			)
			or (
				match(json_value(name, '$.". database::input(language::$selected['code']) ."') against ('*". database::input_fulltext($_GET['query']) ."*')
				or match(json_value(short_description, '$.". database::input(language::$selected['code']) ."') against ('*". database::input_fulltext($_GET['query']) ."*')
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
	vertical-align: middle;
	width: 32px;
	height: 32px;
	border-radius: 4px;
	max-width: unset;
}

.icon-folder,
.icon-folder-open,
td .thumbnail {
	margin-inline-end: 16px;
}

table .icon-folder,
table .icon-folder-open {
	font-size: 1.25em;
	line-height: 0.8;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_catalog', 'Catalog'); ?>
		</div>
	</div>

	<div class="card-action">
		<ul class="list-inline">
			<li><?php echo functions::form_button_link(document::ilink(__APP__.'/edit_category', isset($_GET['category_id']) ? ['parent_id' => $_GET['category_id']] : []), t('title_create_new_category', 'Create New Category'), '', 'create'); ?></li>
			<li><?php echo functions::form_button_link(document::ilink(__APP__.'/edit_product', [], ['category_id']), t('title_create_new_product', 'Create New Product'), '', 'create'); ?></li>
		</ul>
	</div>

	<?php echo functions::form_begin('search_form', 'get'); ?>
		<div class="card-filter">
			<div class="expandable"><?php echo functions::form_input_search('query', true, 'placeholder="'. t('text_search_phrase_or_keyword', 'Search phrase or keyword') .'"  onkeydown=" if (event.keyCode == 13) location=(\''. document::ilink('', [], true, ['page', 'query']) .'&query=\' + encodeURIComponent(this.value))"'); ?></div>
			<div><?php echo functions::form_button('filter', t('title_search', 'Search'), 'submit'); ?></div>
		</div>
	<?php echo functions::form_end(); ?>

	<?php echo functions::form_begin('catalog_form', 'post'); ?>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo functions::draw_fonticon('icon-square-check', 'data-toggle="checkbox-toggle"'); ?></th>
					<th></th>
					<th></th>
					<th class="main"><?php echo t('title_name', 'Name'); ?></th>
					<th class="text-end"><?php echo t('title_price', 'Price'); ?></th>
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
							<strong>[<?php echo t('title_root', 'Root'); ?>]</strong>
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
			"select c.id, c.status, json_value(c.name, '$.". database::input(language::$selected['code']) ."') as name
			from ". DB_TABLE_PREFIX ."categories c
			where c.id = ". (int)$category_id ."
			order by c.priority asc, name asc;"
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
			'    <a class="btn btn-default btn-sm" href="'. document::href_ilink(__APP__.'/edit_category', ['category_id' => $category['id']]) .'" title="'. t('title_edit', 'Edit') .'">',
			'    '. functions::draw_fonticon('edit'),
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
					"select c.id, c.status, json_value(c.name, '$.". database::input(language::$selected['code']) ."') as name
					from ". DB_TABLE_PREFIX ."categories c
					where c.parent_id = ". (int)$category['id'] ."
					". (!empty($_GET['query']) ? "and c.id in ('". implode("', '", database::input($matched_categories)) ."')" : "") ."
					order by name;"
				)->fetch_all();

				foreach ($subcategories as $subcategory) {
					$output .= $draw_category_branch($subcategory['id'], $depth+1);
				}

				$sql_column_price = "coalesce(". implode(", ", array_map(function($currency) {
					return "if(json_value(price, '$.". database::input($currency['code']) ."') != 0, json_value(price, '$.". database::input($currency['code']) ."') * ". $currency['value'] .", null)";
				}, currency::$currencies)) .")";

				// Output products
				$products = database::query(
					"select p.id, p.status, p.code, p.sold_out_status_id, p.image, p.valid_from, p.valid_to,
						json_value(p.name, '$.". database::input(language::$selected['code']) ."') as name,
						pp.price, pc.campaign_price, pso.num_stock_options, pso.total_quantity, oi.total_reserved, pso.total_quantity - oi.total_reserved as quantity_available,
						ptc.category_id

					from ". DB_TABLE_PREFIX ."products p

					left join ". DB_TABLE_PREFIX ."products_to_categories ptc on (ptc.product_id = p.id)

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
							and (valid_from is null or valid_from <= '". date('Y-m-d H:i:s') ."')
							and (valid_to is null or valid_to >= '". date('Y-m-d H:i:s') ."')
						)
						group by product_id
						order by $sql_column_price asc
						limit 1
					) pc on (pc.product_id = p.id)

					left join (
						select pso.id, pso.product_id, pso.stock_item_id, count(pso.stock_item_id) as num_stock_options, sum(si.quantity) as total_quantity
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

					where ". (!empty($category['id']) ? "p.id in (
						select product_id from ". DB_TABLE_PREFIX ."products_to_categories ptc
						where category_id = ". (int)$category['id'] ."
					)" : "ptc.category_id is null") ."

					". (!empty($_GET['query']) ? "and p.id in ('". implode("', '", database::input($matched_products)) ."')" : "") ."

					group by p.id
					order by name asc;"
				)->fetch_all();

				foreach ($products as $product) {

					try {

						$product['warning'] = null;

						if (!empty($product['valid_from']) && strtotime($product['valid_from']) > time()) {
							throw new Exception(strtr(t('text_product_cannot_be_purchased_until_x', 'The product cannot be purchased until {date}'), [
								'{date}' => functions::datetime_format('date', $product['valid_from']),
							]));
						}

						if (!empty($product['valid_to']) && strtotime($product['valid_to']) < time()) {
							throw new Exception(strtr(t('text_product_expired_at_x', 'The product expired at {date} and can no longer be purchased'), [
								'{date}' => functions::datetime_format('date', $product['valid_to']),
							]));
						}

						if ($product['num_stock_options'] && $product['total_quantity'] <= 0) {
							throw new Exception(t('text_product_is_out_of_stock', 'The product is out of stock'));
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
						'      '. ($product['name'] ?: '['. t('title_untitled', 'Untitled') .']'),
						'    </a>',
						'  </td>',
						'  <td class="text-end">'. functions::draw_price_tag($product['price'], $product['campaign_price'], settings::get('store_currency_code')) .'</td>',
						'  <td>',
						'    <a class="btn btn-default btn-sm" href="'. document::href_ilink('f:product', ['product_id' => $product['id']]) .'" title="'. t('title_view', 'View') .'" target="_blank">',
						'    '. functions::draw_fonticon('icon-square-out'),
						'    </a>',
						'  </td>',
						'  <td class="text-end">',
						'    <a class="btn btn-default btn-sm" href="'. document::href_ilink(__APP__.'/edit_product', ['category_id' => $category_id, 'product_id' => $product['id']]) .'" title="'. t('title_edit', 'Edit') .'">',
						'    '. functions::draw_fonticon('edit'),
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
					'  <td><em style="margin-inline-start: '. (($depth+1)*16) .'px;">'. t('title_empty', 'Empty') .'</em></td>',
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
						<td colspan="99">
							<?php echo t('title_categories', 'Categories'); ?>: <?php echo $num_category_rows; ?>, <?php echo t('title_products', 'Products'); ?>: <?php echo $num_product_rows; ?>
						</td>
					</tr>
				</tfoot>
		</table>

		<div class="card-body">
			<fieldset id="actions">

				<legend>
					<?php echo t('text_with_selected', 'With selected'); ?>:
				</legend>

				<div class="flex">

					<div class="btn-group">
						<?php echo functions::form_button_predefined('enable'); ?>
						<?php echo functions::form_button_predefined('disable'); ?>
					</div>

					<div style="min-width: 250px;">
						<?php echo functions::form_select_category('category_id', true); ?>
					</div>

					<div class="btn-group">
						<?php echo functions::form_button('move', t('title_move', 'Move'), 'submit', 'onclick="if (!window.confirm(\''. str_replace("'", "\\\'", t('warning_previous_mount_points_will_be_reset', 'Warning: All previous mount points will be reset.')) .'\')) return false;"'); ?>
						<?php echo functions::form_button('copy', t('title_copy', 'Copy'), 'submit'); ?>
						<?php echo functions::form_button('clone', t('title_clone', 'Clone'), 'submit', '', 'icon-copy'); ?>
					</div>

					<?php echo functions::form_button('unmount', t('title_unmount', 'Unmount'), 'submit'); ?>

					<?php echo functions::form_button_predefined('delete'); ?>

				</div>

			</fieldset>
		</div>

	<?php echo functions::form_end(); ?>
</div>

<script>
	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');
</script>