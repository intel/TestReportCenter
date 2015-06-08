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
		?>

		<?php include_javascripts() ?>
	</head>

	<body>
    	<div class="container">
			<?php include_partial('global/top'); ?>

			<div id="wrapper">
				<?php include_partial('global/header'); ?>

				<?php echo $sf_content ?>

				<?php include_partial('global/footer'); ?>

				<?php include_partial('global/bottom'); ?>
			</div> <!-- /wrapper -->

        </div> <!-- /container -->

	<script type="text/javascript">
		$(document).ready(function() {
			$('#sf_admin_bar').hover(function() {
			    var elem = document.getElementById("sf_admin_bar");
			    elem.classList.toggle("show");
			});
		});
	</script>

	</body>
</html>
