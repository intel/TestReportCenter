<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php include_http_metas() ?>
		<?php include_metas() ?>
		<?php include_title() ?>
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
		<?php include_stylesheets() ?>
	</head>

	<body>
    	<div class="container">
			<?php include_partial('global/top'); ?>

    		<div id="wrapper">
	    		<div id="header">
					<div id="logo">
						<h3>
							<a href="/" title="<?php echo sfConfig::get('app_project_name'); ?>"><?php echo sfConfig::get('app_project_name'); ?></a>
							<a class="hover" href="<?php echo url_for("@homepage"); ?>" title="<?php echo sfConfig::get('app_project_name'); ?>"></a>
						</h3>
					</div> <!-- /logo -->
				</div> <!-- /header -->

				<?php echo $sf_content ?>

				<?php include_partial('global/footer'); ?>

				<?php include_partial('global/bottom'); ?>
			</div> <!-- /wrapper -->
		</div> <!-- /container -->
	</body>
</html>
