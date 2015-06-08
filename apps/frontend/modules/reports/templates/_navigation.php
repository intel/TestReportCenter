<div id="version_navi">
	<ul class="clearfix tabs" id="release_filters">
		<?php foreach($projects as $project): ?>
			<?php
				if(sfContext::getInstance()->getRequest()->hasParameter("build"))
				{
					if(isset($currentFilter))
						$urlFor = url_for("project_builds", array("project" => $project["name_slug"], "filter" => $currentFilter));
					else
						$urlFor = url_for("project_builds", array("project" => $project["name_slug"], "filter" => "recent"));
				}
				else if(sfContext::getInstance()->getRequest()->hasParameter("testset"))
				{
					if(isset($currentFilter))
						$urlFor = url_for("project_testsets", array("project" => $project["name_slug"], "filter" => $currentFilter));
					else
						$urlFor = url_for("project_testsets", array("project" => $project["name_slug"], "filter" => "recent"));
				}
				else
				{
					if(isset($currentFilter))
						$urlFor = url_for("project_reports", array("project" => $project["name_slug"], "filter" => $currentFilter));
					else
						$urlFor = url_for("project_reports", array("project" => $project["name_slug"], "filter" => "recent"));
				}
			?>
			<li <?php if($currentProject["id"]==$project["id"]) echo 'class="current"'; ?>><a href="<?php echo $urlFor; ?>" title="<?php echo $project["name"]; ?>"><?php echo $project["name"];; ?></a></li>
		<?php endforeach; ?>
	</ul> <!-- /release_filters -->

	<?php $currentRouteName = sfContext::getInstance()->getRouting()->getCurrentRouteName(); ?>

	<?php if(isset($currentFilter)): ?>
		<ul class="tabs" id="report_filters">
			<li <?php if($currentFilter=="recent") echo 'class="current"'; ?>><a href="<?php echo url_for($currentRouteName, array("project" => $currentProject["name_slug"], "filter" => "recent")); ?>" title="Recent">Recent</a></li>
			<li <?php if($currentFilter=="all") echo 'class="current"'; ?>><a href="<?php echo url_for($currentRouteName, array("project" => $currentProject["name_slug"], "filter" => "all")); ?>" title="All">All</a></li>
		</ul> <!-- /reports_filters -->
	<?php endif; ?>
</div> <!-- /version_navi -->
