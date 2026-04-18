<style>
body {
	padding: 60px 15px;
}

#box-maintenance-mode {
	display: block;
	text-align: center;
	padding: 30px;
	border-radius: 0 25px;
	background: #fff;
	box-shadow: 0 0 30px rgb(0 0 0 / 25%), 0 0 5px rgb(0 0 0 / 15%);
	margin: 0 auto;
	max-width: 640px;
}

#box-maintenance-mode img {
	max-width: 250px;
	max-height: 60px;
}
</style>

<div class="fourteen-forty">
	<main id="content">
<section id="box-maintenance-mode">
	<img src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>">
	<hr>
	<h1><?php echo t('maintenance_mode:title', 'Maintenance Mode'); ?></h1>
	<p><?php echo t('maintenance_mode:description', 'This site is currently in maintenance mode. Please check back later.'); ?></p>
		</section>
	</main>
</div>
