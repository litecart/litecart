<?php

	administrator::require_login();

	try {

		if (empty($_GET['customer_id'])) {
			throw new Exception('No customer ID provided.');
		}

		$customer = new ent_customer($_GET['customer_id']);

	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
		return;
	}

	document::$title[] = t('title_customer', 'Customer') .' #'. (int)$customer->data['id'];

	breadcrumbs::add(t('title_customers', 'Customers'), document::ilink(__APP__.'/customers'));
	breadcrumbs::add(t('title_customer', 'Customer') .' #'. (int)$customer->data['id'], document::ilink());

	if (empty($_GET['page']) || !is_numeric($_GET['page']) || $_GET['page'] < 1) {
		$_GET['page'] = 1;
	}

	// Orders summary

	$orders_stats = database::query(
		"select count(o.id) as total_count,
			sum(o.total) as total_sales,
			max(o.created_at) as last_order_at
		from ". DB_PREFIX ."orders o
		where o.order_status_id in (
			select id from ". DB_PREFIX ."order_statuses
			where is_sale
		)
		and (o.customer_id = ". (int)$customer->data['id'] ."
			or o.customer_email = '". database::input($customer->data['email']) ."');"
	)->fetch();

	$orders_stats['total_count'] = (int)($orders_stats['total_count'] ?? 0);
	$orders_stats['total_sales'] = (float)($orders_stats['total_sales'] ?? 0);
	$orders_stats['aov'] = $orders_stats['total_count'] > 0 ? $orders_stats['total_sales'] / $orders_stats['total_count'] : 0;
	$orders_stats['is_repeat'] = $orders_stats['total_count'] > 1;

	// Recent orders

	$orders = database::query(
		"select id, no, order_status_id, total, currency_code, created_at
		from ". DB_PREFIX ."orders
		where customer_id = ". (int)$customer->data['id'] ."
		or customer_email = '". database::input($customer->data['email']) ."'
		order by created_at desc
		limit 25;"
	)->fetch_all();

	// Journal: add / list notes

	if (isset($_POST['add_note'])) {

		try {

			$text = trim((string)($_POST['note_text'] ?? ''));

			if ($text === '') {
				throw new Exception(t('error_note_empty', 'Note cannot be empty'));
			}

			$author_id = !empty(administrator::$data['id']) ? (int)administrator::$data['id'] : null;

			database::query(
				"insert into ". DB_PREFIX ."customer_notes
				(customer_id, author_id, author, text, hidden, created_at)
				values (". (int)$customer->data['id'] .",
					". ($author_id !== null ? (int)$author_id : 'null') .",
					'staff',
					'". database::input($text) ."',
					0,
					'". date('Y-m-d H:i:s') ."');"
			);

			customer::log([
				'customer_id' => (int)$customer->data['id'],
				'type' => 'customer_note',
				'description' => 'Added customer note',
				'data' => ['note_length' => strlen($text)],
			]);

			notices::add('success', t('success_note_added', 'Note added'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete_note'])) {

		try {

			database::query(
				"delete from ". DB_PREFIX ."customer_notes
				where id = ". (int)$_POST['note_id'] ."
				and customer_id = ". (int)$customer->data['id'] ."
				limit 1;"
			);

			notices::add('success', t('success_note_deleted', 'Note deleted'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$notes = database::query(
		"select n.*, a.username as author_username
		from ". DB_PREFIX ."customer_notes n
		left join ". DB_PREFIX ."administrators a on (a.id = n.author_id)
		where n.customer_id = ". (int)$customer->data['id'] ."
		order by n.created_at desc, n.id desc
		limit 100;"
	)->fetch_all();

	// Sent emails (filter JSON recipients LIKE)

	$emails = database::query(
		"select id, status, subject, sent_at, created_at
		from ". DB_PREFIX ."emails
		where recipients like '%". database::input($customer->data['email']) ."%'
		order by created_at desc
		limit 100;"
	)->fetch_all();

	$available_tags = [];

	database::query(
		"select tags from ". DB_PREFIX ."customers
		where tags != '' and tags is not null;"
	)->each(function($row) use (&$available_tags) {
		foreach (f::string_split($row['tags']) as $tag) {
			if (!in_array($tag, $available_tags)) {
				$available_tags[] = $tag;
			}
		}
	});

	if (isset($_POST['save_tags'])) {

		try {

			$submitted = array_values(array_unique(array_filter(array_map('trim', preg_split('#\s*,\s*#u', (string)($_POST['tags'] ?? ''))))));

			database::query(
				"update ". DB_PREFIX ."customers
				set tags = '". database::input(implode(',', $submitted)) ."'
				where id = ". (int)$customer->data['id'] ."
				limit 1;"
			);

			customer::log([
				'customer_id' => (int)$customer->data['id'],
				'type' => 'customer_tags_updated',
				'description' => 'Updated customer tags',
				'data' => ['tags' => $submitted],
			]);

			notices::add('success', t('success_tags_saved', 'Tags saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Wishlist / favorites

	$favorites = database::query(
		"select f.product_id, f.created_at as added_at, p.name, p.code
		from ". DB_PREFIX ."favorites f
		left join ". DB_PREFIX ."products p on (p.id = f.product_id)
		where f.customer_id = ". (int)$customer->data['id'] ."
		order by f.created_at desc;"
	)->fetch_all();

	// Order comments (status changes / order notes)

	$order_comments = !empty($orders) ? database::query(
		"select oc.*, a.username as author_username, o.id as order_id, o.no as order_no
		from ". DB_PREFIX ."orders_comments oc
		left join ". DB_PREFIX ."administrators a on (a.id = oc.author_id)
		left join ". DB_PREFIX ."orders o on (o.id = oc.order_id)
		where oc.order_id in (". implode(', ', array_map(fn($o) => (int)$o['id'], $orders)) .")
		order by oc.created_at desc
		limit 100;"
	)->fetch_all() : [];

	// Event logs (moved from edit_customer)

	$ip_addresses = database::query(
		"select ip_address from ". DB_PREFIX ."event_logs
		where customer_id = ". (int)$customer->data['id'] ."
		and (ip_address is not null and ip_address != '');"
	)->fetch_all('ip_address');

	$fingerprints = database::query(
		"select fingerprint from ". DB_PREFIX ."event_logs
		where (
			customer_id = ". (int)$customer->data['id'] ."
			or ip_address in ('". implode("', '", database::input($ip_addresses)) ."')
		)
		and (fingerprint is not null and fingerprint != '');"
	)->fetch_all('fingerprint');

	$session_ids = database::query(
		"select session_id from ". DB_PREFIX ."event_logs
		where (
			customer_id = ". (int)$customer->data['id'] ."
			or ip_address in ('". implode("', '", database::input($ip_addresses)) ."')
			or fingerprint in ('". implode("', '", database::input($fingerprints)) ."')
		)
		and (session_id is not null and session_id != '');"
	)->fetch_all('session_id');

	$activity_logs = database::query(
		"select * from ". DB_PREFIX ."event_logs
		where (
			customer_id = ". (int)$customer->data['id'] ."
			". (!empty($ip_addresses) ? "or ip_address in ('". implode("', '", database::input($ip_addresses)) ."')" : '') ."
			". (!empty($fingerprints) ? "or fingerprint in ('". implode("', '", database::input($fingerprints)) ."')" : '') ."
			". (!empty($session_ids) ? "or session_id in ('". implode("', '", database::input($session_ids)) ."')" : '') ."
		)
		order by created_at desc
		limit 200;"
	)->fetch_all();

	// Group label

	$group_name = '';
	if (!empty($customer->data['group_id'])) {
		$group_name = database::query(
			"select name from ". DB_PREFIX ."customer_groups
			where id = ". (int)$customer->data['group_id'] ."
			limit 1;"
		)->fetch('name');
	}

	// Display name + initials for header

	$_display_name = trim(($customer->data['company'] ?: trim($customer->data['firstname'] .' '. $customer->data['lastname']))) ?: t('title_unknown_customer', 'Unknown customer');
	$_initials = mb_strtoupper(mb_substr((string)$customer->data['firstname'] ?: $customer->data['company'] ?: '?', 0, 1)) . mb_strtoupper(mb_substr((string)$customer->data['lastname'] ?: '?', 0, 1));
	if (empty($customer->data['firstname']) && empty($customer->data['company'])) {
		$_initials = mb_strtoupper(mb_substr((string)$customer->data['email'] ?: '?', 0, 1));
	}

	// Merge activity feed (yemails, orders, order comments, event logs)

	$feed = [];

	foreach ($emails as $email) {
		$feed[] = [
			'timestamp' => $email['sent_at'] ?: $email['created_at'],
			'type' => 'email',
			'icon' => 'icon-email',
			'title' => t('title_email_sent', 'Email sent') .': '. ($email['subject'] ?: '('. t('title_no_subject', 'No subject') .')'),
			'body' => '',
			'meta' => ucfirst((string)$email['status']),
		];
	}

	foreach ($orders as $order) {
		$_status_name = !empty($order['order_status_id']) ? (reference::order_status((int)$order['order_status_id'])->name ?? '') : '';
		$feed[] = [
			'timestamp' => $order['created_at'],
			'type' => 'order',
			'icon' => 'icon-orders',
			'title' => t('title_order_placed', 'Order placed') .' #'. (int)$order['id'],
			'body' => currency::format($order['total'], false, $order['currency_code'] ?? settings::get('store_currency_code')),
			'meta' => $_status_name,
			'link' => document::href_ilink('orders/order', ['order_id' => (int)$order['id']]),
		];
	}

	foreach ($order_comments as $oc) {
		$feed[] = [
			'timestamp' => $oc['created_at'],
			'type' => 'order',
			'icon' => 'icon-orders',
			'title' => t('title_order_update', 'Order update') .' #'. (int)$oc['order_id'] .': '. ($oc['author'] === 'system' ? t('title_status_change', 'Status change') : t('title_comment', 'Comment')),
			'body' => (string)$oc['text'],
			'meta' => $oc['author_username'] ?: ($oc['author'] === 'customer' ? t('title_customer', 'Customer') : t('title_system', 'System')),
			'link' => document::href_ilink('orders/order', ['order_id' => (int)$oc['order_id']]),
		];
	}

	foreach ($activity_logs as $entry) {
		$_is_login = stripos((string)$entry['type'], 'login') !== false;
		$_is_tag = $entry['type'] === 'customer_tags_updated';
		$_is_note = $entry['type'] === 'customer_note';
		$feed[] = [
			'timestamp' => $entry['created_at'],
			'type' => $_is_login ? 'login' : ($_is_tag ? 'tag' : ($_is_note ? 'note' : 'event')),
			'icon' => $_is_login ? 'icon-login' : ($_is_tag ? 'icon-tag' : ($_is_note ? 'icon-note' : 'icon-event')),
			'title' => (string)($entry['description'] ?: $entry['type']),
			'body' => (string)$entry['hostname'],
			'meta' => (string)$entry['ip_address'],
		];
	}

	usort($feed, function($a, $b) {
		return strcmp((string)$b['timestamp'], (string)$a['timestamp']);
	});

	// Bucketize by day (Today / Yesterday / This week / Older)

	$_now_ts = time();
	$_today_start = strtotime('today', $_now_ts);
	$_yesterday_start = strtotime('-1 day', $_today_start);
	$_week_start = strtotime('-6 days', $_today_start);

	$_buckets = ['today' => [], 'yesterday' => [], 'this_week' => [], 'older' => []];
	foreach ($feed as $item) {
		$_ts = strtotime((string)$item['timestamp']);
		if ($_ts >= $_today_start) {
			$_buckets['today'][] = $item;
		} elseif ($_ts >= $_yesterday_start) {
			$_buckets['yesterday'][] = $item;
		} elseif ($_ts >= $_week_start) {
			$_buckets['this_week'][] = $item;
		} else {
			$_buckets['older'][] = $item;
		}
	}

?>
<style>
#customer-dashboard {
	--theme-color: <?= '#21a261' ?>;
}

#customer-dashboard .profile-header {
	position: relative;
	background: var(--card-background);
	border: var(--card-border-width) solid var(--card-border-color);
	border-radius: var(--border-radius);
	box-shadow: var(--card-shadow);
	margin-bottom: 1.5em;
	padding: 1.75em 2em 1.5em;
	border-top: 4px solid var(--theme-color);
}

#customer-dashboard .profile-header .avatar {
	width: 72px;
	height: 72px;
	border-radius: 50%;
	background: var(--theme-color);
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.6em;
	font-weight: 700;
	letter-spacing: 0.04em;
	flex-shrink: 0;
}

#customer-dashboard .profile-header .avatar .fonticon {
	font-size: 1.8em;
}

#customer-dashboard .profile-header .name {
	font-size: 1.6em;
	font-weight: 600;
	line-height: 1.1;
	margin: 0 0 0.25em;
}

#customer-dashboard .profile-header .email {
	opacity: 0.7;
	margin: 0 0 0.25em;
	font-size: 0.95em;
}

#customer-dashboard .profile-header .meta-line {
	font-size: 0.85em;
	opacity: 0.65;
}

#customer-dashboard .profile-header .actions {
	display: flex;
	gap: 0.5em;
	flex-wrap: wrap;
	justify-content: flex-end;
}

#customer-dashboard .profile-header .tags-row {
	margin-top: 1em;
	padding-top: 1em;
	border-top: 1px dashed var(--default-border-color);
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 0.4em;
}

#customer-dashboard .profile-header .tags-row .tag-chip {
	display: inline-flex;
	align-items: center;
	gap: 0.35em;
	padding: 0.35em 0.85em;
	border-radius: 999px;
	background: rgba(33, 162, 97, 0.12);
	color: var(--theme-color);
	font-size: 0.85em;
	font-weight: 500;
}

#customer-dashboard .profile-header .tags-row details {
	flex: 1 1 100%;
}

#customer-dashboard .profile-header .tags-row details > summary {
	list-style: none;
	display: inline-flex;
	align-items: center;
	gap: 0.35em;
	padding: 0.35em 0.85em;
	border: 1px dashed var(--default-border-color);
	border-radius: 999px;
	cursor: pointer;
	font-size: 0.85em;
	opacity: 0.75;
}

#customer-dashboard .profile-header .tags-row details > summary::-webkit-details-marker {
	display: none;
}

#customer-dashboard .profile-header .tags-row details[open] > summary {
	display: none;
}

#customer-dashboard .profile-header .tags-row details .tag-form {
	display: flex;
	gap: 0.5em;
	margin-top: 0.5em;
	flex-wrap: wrap;
}

#customer-dashboard .stat-strip {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
	gap: 1em;
	margin-bottom: 1.5em;
}

#customer-dashboard .stat-card {
	background: var(--card-background);
	border: var(--card-border-width) solid var(--card-border-color);
	border-radius: var(--border-radius);
	box-shadow: var(--card-shadow);
	padding: 1.25em 1.5em;
	position: relative;
	overflow: hidden;
}

#customer-dashboard .stat-card::before {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	width: 3px;
	height: 100%;
	background: var(--theme-color);
	opacity: 0.6;
}

#customer-dashboard .stat-card .stat-icon {
	position: absolute;
	top: 1em;
	right: 1em;
	opacity: 0.2;
	font-size: 1.6em;
}

#customer-dashboard .stat-card .label {
	display: block;
	font-size: 0.75em;
	text-transform: uppercase;
	letter-spacing: 0.06em;
	opacity: 0.6;
	margin-bottom: 0.4em;
}

#customer-dashboard .stat-card .value {
	display: block;
	font-size: 1.4em;
	font-weight: 600;
	line-height: 1.2;
}

#customer-dashboard .stat-card .badge-repeat {
	display: inline-block;
	margin-top: 0.5em;
	padding: 0.15em 0.6em;
	border-radius: 999px;
	background: rgba(33, 162, 97, 0.15);
	color: var(--theme-color);
	font-size: 0.7em;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.05em;
}

#customer-dashboard .meta-card dl {
	display: grid;
	grid-template-columns: max-content 1fr;
	gap: 0.5em 1.25em;
	margin: 0;
}

#customer-dashboard .meta-card dt {
	font-size: 0.8em;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	opacity: 0.6;
}

#customer-dashboard .meta-card dd {
	margin: 0;
	font-size: 0.95em;
}

#customer-dashboard .empty-state {
	text-align: center;
	padding: 2em 1em;
	opacity: 0.55;
}

#customer-dashboard .empty-state .fonticon {
	font-size: 2em;
	display: block;
	margin-bottom: 0.4em;
}

#customer-dashboard .note-composer textarea {
	min-height: 80px;
	resize: vertical;
}

#customer-dashboard .note-journal-empty {
	text-align: center;
	font-size: 0.85em;
	opacity: 0.6;
	padding: 1em 0;
}

#customer-dashboard .note-entry {
	display: flex;
	align-items: flex-start;
	gap: 0.6em;
	padding: 0.55em 0.7em;
	background: var(--default-background);
	border-radius: var(--border-radius);
	border: 1px solid var(--default-border-color);
	margin-bottom: 1.5em;
}

#customer-dashboard .note-entry-avatar {
	flex: 0 0 auto;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: 50%;
	background: var(--theme-color);
	color: #fff;
	font-size: 0.85em;
	opacity: 0.85;
}

#customer-dashboard .note-entry-body {
	flex: 1 1 auto;
	min-width: 0;
}

#customer-dashboard .note-entry-meta {
	display: flex;
	gap: 0.6em;
	align-items: baseline;
	font-size: 0.78em;
	opacity: 0.75;
	margin-bottom: 0.15em;
}

#customer-dashboard .note-entry-author {
	font-weight: 600;
	opacity: 1;
}

#customer-dashboard .note-entry-text {
	white-space: pre-wrap;
	word-wrap: break-word;
	line-height: 1.4;
}

#customer-dashboard .note-entry-delete {
	flex: 0 0 auto;
	opacity: 0.4;
	transition: opacity 0.15s ease;
}

#customer-dashboard .note-entry:hover .note-entry-delete {
	opacity: 1;
}

#customer-dashboard .feed-filter {
	display: flex;
	gap: 0.4em;
	flex-wrap: wrap;
	margin-bottom: 1em;
}

#customer-dashboard .feed-filter button {
	background: transparent;
	border: 1px solid var(--default-border-color);
	color: inherit;
	padding: 0.35em 0.85em;
	border-radius: 999px;
	font-size: 0.85em;
	cursor: pointer;
	opacity: 0.7;
	transition: all 0.15s ease;
}

#customer-dashboard .feed-filter button:hover {
	opacity: 1;
}

#customer-dashboard .feed-filter button.active {
	background: var(--theme-color);
	color: #fff;
	border-color: var(--theme-color);
	opacity: 1;
}

#customer-dashboard .feed-day-header {
	position: sticky;
	top: 0;
	background: var(--default-background);
	font-size: 0.75em;
	text-transform: uppercase;
	letter-spacing: 0.08em;
	opacity: 0.55;
	padding: 0.75em 0 0.5em;
	margin: 1.25em 0 0.5em;
	border-bottom: 1px solid var(--default-border-color);
	z-index: 1;
}

#customer-dashboard .feed-day-header:first-child {
	margin-top: 0;
}

#customer-dashboard .feed-entry {
	display: grid;
	grid-template-columns: 36px 1fr auto;
	gap: 1em;
	padding: 0.85em 0;
	border-bottom: 1px solid var(--default-border-color);
	align-items: start;
}

#customer-dashboard .feed-entry:last-child {
	border-bottom: none;
}

#customer-dashboard .feed-entry .feed-icon {
	width: 36px;
	height: 36px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	background: rgba(33, 162, 97, 0.10);
	color: var(--theme-color);
	font-size: 1.05em;
}

#customer-dashboard .feed-entry .feed-body {
	min-width: 0;
}

#customer-dashboard .feed-entry .feed-title {
	font-weight: 600;
	font-size: 0.95em;
	margin: 0 0 0.15em;
}

#customer-dashboard .feed-entry .feed-body p {
	margin: 0;
	font-size: 0.85em;
	opacity: 0.75;
	overflow: hidden;
	text-overflow: ellipsis;
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
}

#customer-dashboard .feed-entry .feed-time {
	font-size: 0.8em;
	opacity: 0.6;
	white-space: nowrap;
}

#customer-dashboard .feed-entry .feed-meta {
	font-size: 0.75em;
	opacity: 0.55;
	margin-top: 0.15em;
}

#customer-dashboard .feed-entry.is-hidden {
	display: none;
}

@media (max-width: 768px) {
	#customer-dashboard .profile-header {
		padding: 1.25em 1em;
	}
	#customer-dashboard .profile-header .actions {
		justify-content: flex-start;
	}
	#customer-dashboard .feed-entry {
		grid-template-columns: 28px 1fr;
	}
	#customer-dashboard .feed-entry .feed-time {
		grid-column: 2;
	}
}
</style>

<div id="customer-dashboard">

<nav class="tabs">
	<a class="tab-item active" href="#tab-overview" data-toggle="tab">
		<?= t('title_overview', 'Overview') ?>
	</a>
	<a class="tab-item" href="#tab-activity" data-toggle="tab">
		<?= t('title_activity', 'Activity') ?>
		<?php if (count($feed)) { ?>(<?= count($feed) ?>)<?php } ?>
	</a>
</nav>

<div class="tab-contents">

	<div id="tab-overview" class="tab-contents">

		<div class="profile-header">

			<div class="grid" style="display: grid; grid-template-columns: 72px 1fr auto; gap: 1.5em; align-items: center;">
				<div class="avatar"><?= f::draw_fonticon('icon-user') ?></div>
				<div>
					<h1 class="name"><?= f::escape_html($_display_name) ?></h1>
					<p class="email"><?= f::escape_html($customer->data['email']) ?></p>
					<div class="meta-line">
						<?= t('title_customer_id', 'Customer ID') ?>: #<?= (int)$customer->data['id'] ?>
						<?php if ($group_name) { ?> · <?= f::escape_html($group_name) ?><?php } ?>
						<?php if (!empty($customer->data['status'])) { ?>
							<span style="margin-inline-start: 0.5em; padding: 0.15em 0.6em; border-radius: 999px; background: rgba(33, 162, 97, 0.15); color: #21a261; font-size: 0.75em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
								<?= f::draw_fonticon('icon-check') ?> <?= t('title_active', 'Active') ?>
							</span>
						<?php } else { ?>
							<span style="margin-inline-start: 0.5em; padding: 0.15em 0.6em; border-radius: 999px; background: rgba(151, 163, 181, 0.15); color: #97a3b5; font-size: 0.75em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
								<?= f::draw_fonticon('icon-pause') ?> <?= t('title_disabled', 'Disabled') ?>
							</span>
						<?php } ?>
						<?php if (!empty($customer->data['blocked_until']) && strtotime($customer->data['blocked_until']) > time()) { ?>
							<span style="margin-inline-start: 0.5em; padding: 0.15em 0.6em; border-radius: 999px; background: rgba(220, 53, 69, 0.15); color: #dc3545; font-size: 0.75em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
								<?= f::draw_fonticon('icon-lock') ?> <?= t('title_blocked', 'Blocked') ?> <?= f::datetime_when($customer->data['blocked_until']) ?>
							</span>
						<?php } ?>
					</div>
				</div>
				<div class="actions">
					<?= f::form_begin('sign_in_as_customer', 'post', document::href_ilink(__APP__.'/edit_customer', ['customer_id' => (int)$customer->data['id']]), false, ['style' => 'display: inline-block; margin: 0;']) ?>
					<?= f::form_button('sign_in', ['true', t('text_sign_in_as_customer', 'Sign in as customer')], 'submit', ['class' => 'btn btn-default'], 'icon-key') ?>
					<?= f::form_end() ?>
					<a class="btn btn-default" href="<?= document::href_ilink(__APP__.'/edit_customer', ['customer_id' => (int)$customer->data['id']]) ?>">
						<?= f::draw_fonticon('edit') ?> <?= t('title_edit_profile', 'Edit Profile') ?>
					</a>
				</div>
			</div>

			<div class="tags-row">
				<?php foreach (f::string_split((string)$customer->data['tags']) as $tag) { ?>
				<span class="tag-chip">
					<?= f::draw_fonticon('icon-tag') ?> <?= f::escape_html($tag) ?>
				</span>
				<?php } ?>

				<?php if (!empty($customer->data['tags'])) { ?>
				<span style="opacity: 0.55; font-size: 0.85em;"><?= t('text_no_tags', 'No tags') ?></span>
				<?php } ?>

				<details>
					<summary><?= f::draw_fonticon('icon-add') ?> <?= t('title_add_tags', 'Add tags') ?></summary>
					<?= f::form_begin('tags_form', 'post', '', false, ['class' => 'tag-form']) ?>
						<div style="flex: 1; min-width: 200px;">
							<?= f::form_input_tags('tags', $customer->data['tags'], [], $available_tags) ?>
						</div>
						<?= f::form_button('save_tags', t('title_save', 'Save'), 'submit', ['class' => 'btn btn-primary']) ?>
					<?= f::form_end() ?>
				</details>
			</div>

		</div>

		<div class="stat-strip">

			<div class="stat-card">
				<span class="stat-icon"><?= f::draw_fonticon('icon-orders') ?></span>
				<span class="label"><?= t('title_orders', 'Orders') ?></span>
				<span class="value"><?= (int)$orders_stats['total_count'] ?></span>
				<?php if ($orders_stats['is_repeat']) { ?>
					<span class="badge-repeat"><?= f::draw_fonticon('icon-check') ?> <?= t('text_repeat_customer', 'Repeat') ?></span>
				<?php } else { ?>
					<span class="badge-repeat" style="background: rgba(151, 163, 181, 0.15); color: #97a3b5;"><?= t('text_one_shot_customer', 'One-shot') ?></span>
				<?php } ?>
			</div>

			<div class="stat-card">
				<span class="stat-icon"><?= f::draw_fonticon('icon-currency') ?></span>
				<span class="label"><?= t('title_total_sales', 'Total Sales') ?></span>
				<span class="value"><?= currency::format($orders_stats['total_sales'], false, settings::get('store_currency_code')) ?></span>
			</div>

			<div class="stat-card">
				<span class="stat-icon"><?= f::draw_fonticon('icon-chart') ?></span>
				<span class="label"><?= t('title_average_order_value', 'Average Order Value') ?></span>
				<span class="value"><?= currency::format($orders_stats['aov'], false, settings::get('store_currency_code')) ?></span>
			</div>

			<div class="stat-card">
				<span class="stat-icon"><?= f::draw_fonticon('icon-clock') ?></span>
				<span class="label"><?= t('title_last_order', 'Last Order') ?></span>
				<span class="value" style="font-size: 1em;">
					<?= $orders_stats['last_order_at'] ? f::datetime_when($orders_stats['last_order_at']) : '<em>'. t('title_never', 'Never') .'</em>' ?>
				</span>
			</div>

		</div>

		<div class="grid">

			<div class="col-md-6">

				<div class="card meta-card">
					<div class="card-header">
						<div class="card-title">
							<?= t('title_details', 'Details') ?>
						</div>
					</div>
					<div class="card-body">
						<dl>
							<dt><?= t('title_customer_group', 'Customer Group') ?></dt>
							<dd><?= f::escape_html($group_name) ?: '—' ?></dd>

							<dt><?= t('title_language', 'Language') ?></dt>
							<dd><?= f::escape_html($customer->data['language_code'] ?: '—') ?></dd>

							<dt><?= t('title_newsletter', 'Newsletter') ?></dt>
							<dd>
								<?php if (!empty($customer->data['newsletter'])) { ?>
									<span style="color: #21a261;"><?= f::draw_fonticon('icon-check') ?> <?= t('title_subscribed', 'Subscribed') ?></span>
								<?php } else { ?>
									<span style="opacity: 0.6;"><?= t('title_not_subscribed', 'Not subscribed') ?></span>
								<?php } ?>
							</dd>

							<dt><?= t('title_two_factor_authentication', '2FA') ?></dt>
							<dd>
								<?php if (!empty($customer->data['two_factor_auth'])) { ?>
									<span style="color: #21a261;"><?= f::draw_fonticon('icon-lock') ?> <?= t('title_enabled', 'Enabled') ?></span>
								<?php } else { ?>
									<span style="opacity: 0.6;"><?= t('title_disabled', 'Disabled') ?></span>
								<?php } ?>
							</dd>

							<dt><?= t('title_last_login', 'Last Login') ?></dt>
							<dd>
								<?php if (!empty($customer->data['last_login'])) { ?>
									<?= f::datetime_when($customer->data['last_login']) ?>
									<?php if (!empty($customer->data['last_ip_address'])) { ?>
										<div style="font-size: 0.85em; opacity: 0.7;"><tt><?= f::escape_html($customer->data['last_ip_address']) ?></tt> <?= f::escape_html($customer->data['last_hostname']) ?></div>
									<?php } ?>
								<?php } else { ?>
									<em><?= t('title_never', 'Never') ?></em>
								<?php } ?>
							</dd>

							<dt><?= t('title_total_logins', 'Total Logins') ?></dt>
							<dd><?= (int)($customer->data['total_logins'] ?? 0) ?></dd>

							<dt><?= t('title_known_ips', 'Known IPs') ?></dt>
							<dd style="font-size: 0.85em; word-break: break-word;"><?= f::escape_html($customer->data['known_ips'] ?: '—') ?></dd>

							<dt><?= t('title_registered', 'Registered') ?></dt>
							<dd><?= f::datetime_when($customer->data['created_at']) ?></dd>

							<dt><?= t('title_updated', 'Updated') ?></dt>
							<dd><?= !empty($customer->data['updated_at']) ? f::datetime_when($customer->data['updated_at']) : '—' ?></dd>
						</dl>
					</div>
				</div>

				<div class="card">
					<div class="card-header">
						<div class="card-title">
							<?= t('title_favourites', 'Favourites') ?>
							<?php if (count($favorites)) { ?>
							<small style="opacity: 0.6; font-weight: 400;">(<?= count($favorites) ?>)</small>
							<?php } ?>
						</div>
					</div>

					<?php if (empty($favorites)) { ?>
						<div class="card-body empty-state">
							<?= f::draw_fonticon('icon-heart') ?>
							<div><?= t('text_no_favorites', 'No favourites yet.') ?></div>
						</div>
					<?php } else { ?>
						<div style="padding: 0.5em 1.5em;">
							<?php foreach (array_slice($favorites, 0, 5) as $fav) { ?>
								<div style="display: flex; justify-content: space-between; padding: 0.5em 0; border-bottom: 1px solid var(--default-border-color);">
									<a class="link" href="<?= document::href_ilink('catalog/edit_product', ['product_id' => (int)$fav['product_id']]) ?>">
										<?= f::escape_html(is_array($fav['name']) ? implode(' / ', array_filter((array)$fav['name'])) : $fav['name']) ?>
									</a>
									<small style="opacity: 0.55;"><?= f::datetime_when($fav['added_at']) ?></small>
								</div>
							<?php } ?>
						</div>
						<?php if (count($favorites) > 5) { ?>
							<details style="padding: 0 1.5em 1em;">
								<summary style="cursor: pointer; font-size: 0.85em; opacity: 0.7;"><?= t('title_show_all', 'Show all') .' ('. count($favorites) .')' ?></summary>
								<div style="margin-top: 0.5em;">
									<?php foreach (array_slice($favorites, 5) as $fav) { ?>
										<div style="display: flex; justify-content: space-between; padding: 0.5em 0; border-bottom: 1px solid var(--default-border-color);">
											<a class="link" href="<?= document::href_ilink('catalog/edit_product', ['product_id' => (int)$fav['product_id']]) ?>">
												<?= f::escape_html(is_array($fav['name']) ? implode(' / ', array_filter((array)$fav['name'])) : $fav['name']) ?>
											</a>
											<small style="opacity: 0.55;"><?= f::datetime_when($fav['added_at']) ?></small>
										</div>
									<?php } ?>
								</div>
							</details>
						<?php } ?>
					<?php } ?>
				</div>

				<div class="card">
					<div class="card-header">
						<div class="card-title"><?= t('title_recent_orders', 'Recent Orders') ?></div>
						<?php if (count($orders) > 10) { ?>
							<div class="card-action">
								<a class="btn btn-default btn-sm" href="<?= document::href_ilink('orders/orders', ['query' => $customer->data['email']]) ?>"><?= t('title_see_all', 'See all') ?></a>
							</div>
						<?php } ?>
					</div>

					<?php if (empty($orders)) { ?>
						<div class="card-body empty-state">
							<?= f::draw_fonticon('icon-orders') ?>
							<div><?= t('text_no_orders_yet', 'No orders yet.') ?></div>
						</div>
					<?php } else { ?>
						<table class="table data-table">
							<thead>
								<tr>
									<th class="main"><?= t('title_order_no', 'Order No') ?></th>
									<th><?= t('title_status', 'Status') ?></th>
									<th class="text-end"><?= t('title_total', 'Total') ?></th>
									<th class="text-end"><?= t('title_created', 'Created') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach (array_slice($orders, 0, 10) as $order) { ?>
								<tr>
									<td>
										<a class="link" href="<?= document::href_ilink('orders/order', ['order_id' => (int)$order['id']]) ?>">
											#<?= (int)$order['no'] ?>
										</a>
									</td>
									<td><?= !empty($order['order_status_id']) ? f::escape_html(reference::order_status((int)$order['order_status_id'])->name ?? '') : '-' ?></td>
									<td class="text-end"><?= currency::format($order['total'], false, $order['currency_code'] ?? settings::get('store_currency_code')) ?></td>
									<td><?= f::datetime_when($order['created_at']) ?></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					<?php } ?>
				</div>

			</div>

			<div class="col-md-6">

				<div class="card note-composer">
					<div class="card-header">
						<div class="card-title">
							<?= t('title_quick_note', 'Quick Note') ?>
						</div>
					</div>

					<div class="card-body">

						<div class="notes">
							<?php if (empty($notes)) { ?>
								<div class="note-journal-empty"><?= t('text_no_notes_yet', 'No notes yet. Add the first one below.') ?></div>
							<?php } else { ?>
								<?php foreach ($notes as $note) {
									$_author = match($note['author'] ?? 'staff') {
										'system' => t('title_system', 'System'),
										'customer' => t('title_customer', 'Customer'),
										default => $note['author_username'],
									};
								?>
								<div class="note-entry">
									<div class="note-entry-avatar"><?= f::draw_fonticon('icon-note') ?></div>
									<div class="note-entry-body">
										<div class="note-entry-meta">
											<span class="note-entry-author"><?= f::escape_html($_author) ?></span>
											<span class="note-entry-time"><?= f::datetime_when($note['created_at']) ?></span>
										</div>
										<div class="note-entry-text"><?= f::escape_html($note['text']) ?></div>
									</div>
									<?= f::form_begin('delete_note_form_'. (int)$note['id'], 'post', '', false, ['class' => 'note-entry-delete']) ?>
										<?= f::form_input_hidden('note_id', (int)$note['id']) ?>
										<?= f::form_button('delete_note', f::draw_fonticon('icon-trash'), 'submit', ['class' => 'btn btn-default btn-sm', 'title' => t('title_delete', 'Delete'), 'onclick' => "return confirm('". f::escape_js(t('text_confirm_delete_note', 'Delete this note?')) ."')"]) ?>
									<?= f::form_end() ?>
								</div>
								<?php } ?>
							<?php } ?>
						</div>

						<?= f::form_begin('note_form', 'post', '', false) ?>
							<label class="form-group">
								<?= f::form_textarea('note_text', false, ['rows' => 3, 'placeholder' => f::escape_html(t('text_enter_note', 'Enter a note about this customer'))]) ?>
							</label>
							<div class="text-end">
								<?= f::form_button('add_note', t('title_add_note', 'Add Note'), 'submit', ['class' => 'btn btn-primary']) ?>
							</div>
						<?= f::form_end() ?>
					</div>
				</div>

			</div>
		</div>

	</div>

	<div id="tab-activity" class="tab-contents">

		<div class="card">
			<div class="card-header">
				<div class="card-title"><?= f::draw_fonticon('icon-event') ?> <?= t('title_activity_timeline', 'Activity Timeline') ?></div>
			</div>
			<div class="card-body">

				<?php if (empty($feed)) { ?>
					<div class="empty-state">
						<?= f::draw_fonticon('icon-event') ?>
						<div><?= t('text_no_activity', 'No activity yet.') ?></div>
					</div>
				<?php } else { ?>

					<div class="feed-filter" id="feed-filter">
						<button type="button" data-filter="all" class="active"><?= t('title_all', 'All') ?></button>
						<button type="button" data-filter="note"><?= f::draw_fonticon('icon-note') ?> <?= t('title_notes', 'Notes') ?></button>
						<button type="button" data-filter="email"><?= f::draw_fonticon('icon-email') ?> <?= t('title_emails', 'Emails') ?></button>
						<button type="button" data-filter="order"><?= f::draw_fonticon('icon-orders') ?> <?= t('title_orders', 'Orders') ?></button>
						<button type="button" data-filter="login"><?= f::draw_fonticon('icon-login') ?> <?= t('title_logins', 'Logins') ?></button>
						<button type="button" data-filter="tag"><?= f::draw_fonticon('icon-tag') ?> <?= t('title_tags', 'Tags') ?></button>
					</div>

					<?php
						$_bucket_labels = [
							'today' => t('title_today', 'Today'),
							'yesterday' => t('title_yesterday', 'Yesterday'),
							'this_week' => t('title_this_week', 'This week'),
							'older' => t('title_older', 'Older'),
						];
					?>
					<?php foreach ($_buckets as $_bucket_key => $_bucket_items): ?>
						<?php if (empty($_bucket_items)) continue; ?>
						<div class="feed-day-header" data-bucket="<?= f::escape_attr($_bucket_key) ?>"><?= f::escape_html($_bucket_labels[$_bucket_key]) ?></div>
						<?php foreach ($_bucket_items as $item): ?>
							<div class="feed-entry" data-type="<?= f::escape_attr($item['type']) ?>">
								<div class="feed-icon"><?= f::draw_fonticon($item['icon']) ?></div>
								<div class="feed-body">
									<div class="feed-title"><?= f::escape_html($item['title']) ?></div>
									<?php if (!empty($item['body'])) { ?>
										<p><?= f::escape_html(mb_substr((string)$item['body'], 0, 200)) ?><?= mb_strlen((string)$item['body']) > 200 ? '…' : '' ?></p>
									<?php } ?>
									<?php if (!empty($item['meta'])) { ?>
										<div class="feed-meta"><?= f::escape_html($item['meta']) ?></div>
									<?php } ?>
								</div>
								<div class="feed-time"><?= f::datetime_when($item['timestamp']) ?></div>
							</div>
						<?php endforeach; ?>
					<?php endforeach; ?>

				<?php } ?>

			</div>
		</div>

	</div>

</div>

</div>

<script>
$('#feed-filter button').on('click', function() {
	var filter = $(this).data('filter');
	$(this).addClass('active').siblings().removeClass('active');
	if (filter === 'all') {
		$('.feed-entry').removeClass('is-hidden');
	} else {
		$('.feed-entry').addClass('is-hidden').filter('[data-type="' + filter + '"]').removeClass('is-hidden');
	}
	// Hide empty bucket headers
	$('.feed-day-header').each(function() {
		var $header = $(this);
		var $next = $header.nextUntil('.feed-day-header', '.feed-entry');
		var anyVisible = $next.filter(':not(.is-hidden)').length > 0;
		$header.toggle(anyVisible);
	});
});
</script>
