<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php include_http_metas() ?>
		<?php include_metas() ?>
		<?php include_title() ?>
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
		<?php include_stylesheets() ?>
		
		<?php
		use_javascript('jquery-1.8.3.min.js');
		use_javascript('jquery-ui.min.js');

		use_javascript('bluff/js-class.js');
		use_javascript('bluff/excanvas.js');
		use_javascript('bluff/bluff-min.js');

		use_javascript('application.js');
		?>

		<?php include_javascripts() ?>
		
		<script>
			$(function() {
				filterResults("tr.result_pass", "passing tests");
			});
		</script>
		
	</head>

	<body>

		<?php echo $sf_content ?>
		
		<?php include_partial('global/footer'); ?>

	</body>
</html>
