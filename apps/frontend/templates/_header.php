<div id="header">
	<div id="logo">
		<a href="<?php echo url_for('homepage'); ?>" title="<?php echo sfConfig::get('app_project_name'); ?>">
			<img src="<?php echo image_path('logo.png'); ?>" alt="Logo" />
			<h1><?php echo sfConfig::get('app_project_name'); ?></h1>
		</a>
	</div> <!-- /logo -->

	<div id="action">
		<?php if($sf_user->isAuthenticated()): ?>
			<a class="small_btn add" href="<?php echo url_for("add_report"); ?>">Add report +</a>
		<?php endif; ?>
	</div> <!-- /action -->
</div> <!-- /header -->
