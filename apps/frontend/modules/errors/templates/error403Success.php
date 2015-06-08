<div id="page">
	<div id="index_page">
		<div class="page_content error_page">

			<h1>403</h1>
			<h3>Oops! Access forbidden</h3>
			<p class="message"><?php echo $sf_user->getFlash("403message"); ?></p>

			<p><a href="javascript:history.back()" title="Back to previous page">Back to previous page</a></p>
			<p><a href="<?php echo url_for("@homepage"); ?>" title="Go to homepage">Go to homepage</a></p>

		</div> <!-- /page_content -->
	</div> <!-- /index_page -->
</div> <!-- /page -->