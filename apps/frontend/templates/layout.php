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
		use_javascript('tiny_mce/tiny_mce.js');
		use_javascript('tiny_mce/jquery.tinymce.js');

		use_javascript('bluff/js-class.js');
		use_javascript('bluff/excanvas.js');
		use_javascript('bluff/bluff-min.js');

		use_javascript('application.js');
		?>

		<?php include_javascripts() ?>
	</head>

	<body>
    	<div class="container">
			<?php include_partial('global/top'); ?>

			<div id="wrapper">
				<?php include_partial('global/header'); ?>

				<?php if ($sf_user->hasFlash('notice')): ?>
					<div class="flash_notice">
						<?php echo $sf_user->getFlash('notice'); ?>
					</div>
				<?php endif ?>

				<?php if ($sf_user->hasFlash('error')): ?>
					<div class="flash_error">
						<?php echo $sf_user->getFlash('error'); ?>
					</div>
				<?php endif ?>

				<?php echo $sf_content ?>

				<?php include_partial('global/footer'); ?>

				<?php include_partial('global/bottom'); ?>
			</div> <!-- /wrapper -->

        </div> <!-- /container -->

<script type="text/javascript">
	function addToComparison(id)
	{
	    var url = "<?php echo url_for("compare_add", array("id" => "0")); ?>";
	    url = url.replace(/0/g, id);

	    console.log(url);

        $('#header_comparison_box').load(
	        url,
	        {},
	        function() {}
        );
	}

	function hideShow(link, content, visibility)
	{
		var link = document.getElementById(link);
		var content = document.getElementById(content);

		// Force display to "show"
		if(visibility == 1)
		{
			link.innerHTML = "Hide";
			link.classList.remove("hidden", "displayed");
			link.classList.add("displayed");
			content.classList.toggle("hide", false);
		}
		// Force display to "hide"
		else if(visibility == 0)
		{
			link.innerHTML = "Show";
			link.classList.remove("hidden", "displayed");
			link.classList.add("hidden");
			content.classList.toggle("hide", true);
		}
		else
		{
			link.innerHTML = content.classList.contains("hide") ? "Hide" : "Show";
			link.classList.toggle("hidden");
			link.classList.toggle("displayed");
			content.classList.toggle("hide");
		}
	}
</script>
	</body>
</html>
