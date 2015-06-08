<rss version="2.0">
<?php use_helper("Date") ?>
	<channel>
		<title><?php echo sfConfig::get('app_project_name'); ?> - <?php echo $currentProject["name"]."/".$currentProduct["name"]."/".$currentBuild["build_id"]."/".$currentEnvironment["name"]; ?></title>
		<link><?php echo url_for("@homepage", true); ?></link>
		<description><?php echo sfConfig::get("app_project_group")." ".sfConfig::get('app_project_name')." for ".$currentProject["name"]."/".$currentProduct["name"]."/".$currentBuild["build_id"]."/".$currentEnvironment["name"]; ?></description>
		<generator><?php echo sfConfig::get("app_project_group"); ?> <?php echo sfConfig::get('app_project_name'); ?></generator>
		<language>en-us</language>
		<pubDate><?php echo date(DATE_RSS); ?></pubDate>

		<?php foreach($sessionsForImages as $data): ?>
			<item>
				<title><?php echo $data["name"]; ?></title>
				<pubDate><?php echo date(DATE_RSS, strtotime(format_datetime($data["created_at"], "y-MM-dd HH:mm"))); ?></pubDate>
				<description>
Total test cases: <?php echo $data["total"]; ?><br/>
Passed: <?php echo $data["pass"]; ?><br/>
Failed: <?php echo $data["fail"]; ?><br/>
Blocked: <?php echo $data["block"]; ?><br/>
Run rate: <?php echo round(($data["pass"] + $data["fail"]) / $data["total"] * 100); ?><br/>
Pass rate of total: <?php echo round($data["pass"] / $data["total"] * 100); ?><br/>
Pass rate of executed: <?php echo round($data["pass"] / ($data["pass"] + $data["fail"]) * 100); ?>
				</description>
				<link><?php echo url_for("build_session", array("build" => $currentBuild["build_id"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $data["i_slug"], "id" => $data["id"]), true); ?></link>
				<guid><?php echo url_for("build_session", array("build" => $currentBuild["build_id"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $data["i_slug"], "id" => $data["id"]), true); ?></guid>
			</item>
		<?php endforeach; ?>
	</channel>
</rss>
