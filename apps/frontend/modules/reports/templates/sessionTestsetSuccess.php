<?php use_helper('Date') ?>

<?php include_partial("reports/navigation", array("projects" => $projects, "currentProject" => $currentProject)); ?>

<?php
	$totalResultsPassed = $currentSummaryNumbers["pass"];
	$totalResultsFailed = $currentSummaryNumbers["fail"];
	$totalResultsBlocked = $currentSummaryNumbers["block"];
	$totalResultsDeferred = $currentSummaryNumbers["deferred"];
	$totalResultsNotRun = $currentSummaryNumbers["not_run"];
	$totalTestResults = $currentSummaryNumbers["total"];

	$runRate = round(($totalResultsPassed + $totalResultsFailed) / $totalTestResults * 100);
	$totalPassRate = round($totalResultsPassed / $totalTestResults * 100);
	$executedPassRate = ($totalResultsPassed + $totalResultsFailed) > 0 ? round($totalResultsPassed / ($totalResultsPassed + $totalResultsFailed) * 100) : 0;

	if(count($previousSummaryNumbers) >= 1)
	{
		$previousTotalResultsPassed = $previousSummaryNumbers[0]["pass"];
		$previousTotalResultsFailed = $previousSummaryNumbers[0]["fail"];
		$previousTotalResultsBlocked = $previousSummaryNumbers[0]["block"];
		$previousTotalResultsDeferred = $previousSummaryNumbers[0]["deferred"];
		$previousTotalResultsNotRun = $previousSummaryNumbers[0]["not_run"];
		$previousTotalTestResults = $previousSummaryNumbers[0]["total"];

		$previousRunRate = round(($previousTotalResultsPassed + $previousTotalResultsFailed) / $previousTotalTestResults * 100);
		$previousTotalPassRate = round($previousTotalResultsPassed / $previousTotalTestResults * 100);
		$previousExecutedPassRate = ($previousTotalResultsPassed + $previousTotalResultsFailed > 0) ? round($previousTotalResultsPassed / ($previousTotalResultsPassed + $previousTotalResultsFailed) * 100) : 0;
	}
	else
	{
		$previousTotalResultsPassed = 0;
		$previousTotalResultsFailed = 0;
		$previousTotalResultsBlocked = 0;
		$previousTotalResultsDeferred = 0;
		$previousTotalResultsNotRun = 0;
		$previousTotalTestResults = 0;
		$previousRunRate = 0;
		$previousTotalPassRate = 0;
		$previousExecutedPassRate = 0;
	}

	function getArrow($value)
	{
		if($value < 0)
			return "dec";
		else if($value > 0)
			return "inc";
		else
			return "unchanged";
	}
?>

<div id="page">
	<div id="index_page">
		<div id="breadcrumb">
			<li><a href="<?php echo url_for("project_testsets", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "filter" => "recent")); ?>" title="Home">Home</a></li>
			<li>> <a href="<?php echo url_for("product_testsets", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"])); ?>" title="<?php echo $currentProduct["name"]; ?>"><?php echo $currentProduct["name"]; ?></a></li>
			<li>> <a href="<?php echo url_for("testset_reports", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"])); ?>" title="<?php echo $currentProduct["name"]; ?>"><?php echo $currentTestset["testset"]; ?></a></li>
			<li>> <a href="<?php echo url_for("environment_testsets", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"])); ?>" title="<?php echo $currentEnvironment["name"]; ?>"><?php echo $currentEnvironment["name"]; ?></a></li>
			<li>> <a href="<?php echo url_for("image_testsets", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"])); ?>" title="<?php echo $currentImage["name"]; ?>"><?php echo $currentImage["name"]; ?></a></li>
			<li>> <?php echo "(".$currentSession["id"].") ".$currentSession["name"]; ?></li>
		</div> <!-- /breadcrumb -->

		<div class="page_content">
				<form id="edit_report">
			<?php if($sf_user->isAuthenticated()): ?>
					<a id="delete-button" class="small_btn cancel" href="javascript:simple_confirmation('Do you really want to delete this test session?', '<?php echo url_for("delete_report", array("id" => $currentSession["id"])); ?>')" title="Delete report">Delete</a>
					<a id="edit-button" class="small_btn" href="<?php echo url_for("edit_report", array("project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"])); ?>" title="Edit report">Edit</a>
					<a id="print-button" class="small_btn" href="<?php echo url_for("print_testset", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"])); ?>" title="Print report">Print</a>
			<?php endif; ?>
					<a id="print-button" class="small_btn" href="javascript:;" onclick="addToComparison('<?php echo $currentSession["id"]; ?>');" title="Compare report">Compare</a>
				</form>

			<h1 class="clearfix report-title"><?php echo "(".$currentSession["id"].") ".$currentSession["name"]; ?></h1>

			<div class="report_head">
				<h2 id="contents_title">Contents</h2>

				<ol class="toc">
					<li><a href="#report_summaries" onclick="hideShow('report_summaries_link', 'report_summaries_content', 1);" title="Report Information">Report Information</a></li>
					<ol>
						<li><a href="#test_objective" onclick="hideShow('report_summaries_link', 'report_summaries_content', 1); hideShow('test_objective_link', 'test_objective_content', 1);" title="Test Objective">Test Objective</a></li>
						<li><a href="#environment" onclick="hideShow('report_summaries_link', 'report_summaries_content', 1); hideShow('environment_link', 'environment_content', 1);" title="Test Environment">Test Environment</a></li>
						<li><a href="#build_image" onclick="hideShow('report_summaries_link', 'report_summaries_content', 1); hideShow('build_image_link', 'build_image_content', 1);" title="Build (image)">Build (image)</a></li>
						<li><a href="#notes_summary" onclick="hideShow('report_summaries_link', 'report_summaries_content', 1); hideShow('notes_summary_link', 'notes_summary_content', 1);" title="Environment Summary">Environment Summary</a></li>
						<li><a href="#qa_summary" onclick="hideShow('report_summaries_link', 'report_summaries_content', 1); hideShow('qa_summary_link', 'qa_summary_content', 1);" title="Quality Summary">Quality Summary</a></li>
						<li><a href="#issue_summary" onclick="hideShow('report_summaries_link', 'report_summaries_content', 1); hideShow('issue_summary_link', 'issue_summary_content', 1);" title="Issue Summary">Issue Summary</a></li>
					</ol>
					<li><a href="#test_results" onclick="hideShow('test_results_link', 'test_results_content', 1);" title="Test Results">Test Results</a></li>
					<ol>
						<li><a href="#test_results" onclick="hideShow('test_results_link', 'test_results_content', 1);" title="Result Summary">Result Summary</a></li>
						<li><a href="#test_features" onclick="hideShow('test_results_link', 'test_results_content', 1);" title="Test Results by Feature">Test Results by Feature</a></li>
					</ol>
					<?php if($currentDisplay != "basic"): ?>
						<?php if(count($currentSession["results"]) > 0): ?><li><a href="#detailed_results" onclick="hideShow('detailed_results_link', 'detailed_results_content', 1);" title="Detailed Test Results">Detailed Test Results</a></li><?php endif; ?>
						<?php if(count($currentSession["measures"]) > 0): ?><li><a href="#detailed_nft_results" onclick="hideShow('detailed_nft_results_link', 'detailed_nft_results_content', 1);" title="Non-Functional Test Results">Non-Functional Test Results</a></li><?php endif; ?>
					<?php endif; ?>
					<li><a href="#result_files" onclick="hideShow('result_files_link', 'result_files_content', 1);" title="Result Files">Result Files</a></li>
					<ol>
						<li><a href="#attachments" onclick="hideShow('result_files_link', 'result_files_content', 1); hideShow('attachments_link', 'attachments_content', 1);" title="Attachments">Attachments</a></li>
						<li><a href="#raw_result_files" onclick="hideShow('result_files_link', 'result_files_content', 1); hideShow('raw_result_files_link', 'raw_result_files_content', 1);" title="Original Result Files">Original Result Files</a></li>
					</ol>
				</ol> <!-- /toc -->

				<dl class="meta_info">
					<dt id="report_author">Created by:</dt>
					<dd>
						<?php echo $author["first_name"]." ".$author["last_name"]; ?><br/>
						<span class="un"><?php echo format_datetime($currentSession["created_at"], "d MMMM y HH:mm"); ?></span>
					</dd>

					<dt id="report_author">Last modified by:</dt>
					<dd>
						<?php echo $editor["first_name"]." ".$editor["last_name"]; ?><br/>
						<span class="un"><?php echo format_datetime(($currentSession["updated_at"] != null) ? $currentSession["updated_at"] : $currentSession["created_at"], "d MMMM y HH:mm"); ?></span>
					</dd>

					<dt>Category:</dt>
					<dd class="category"><?php echo $currentProject["name"]." > ".$currentProduct["name"]." > ".$currentEnvironment["name"]." > ".$currentImage["name"]; ?></dd>

					<dt>Build id and testset:</dt>
					<dd class="build-id">
						<?php if($currentSession["project_release"] != ""): ?>
							Project release: <?php echo $currentSession["project_release"]; ?>
							Project milestone: <?php echo $currentSession["project_milestone"]; ?>
						<?php endif; ?>
						<?php if($currentSession["build_id"] != '') echo $currentSession["build_id"]; ?><br/>
						<?php if($currentSession["testset"] != '') echo $currentSession["testset"]; ?>
					</dd>
					<dt>Status:</dt>
					<dd class="status"><span title="<?php echo Labeler::getTestSessionStatusLabel($currentSession["status"]); ?>" class="icon_status <?php echo "status_".MiscUtils::slugify(Labeler::getTestSessionStatusLabel($currentSession["status"]), '_'); ?>"></span><?php echo Labeler::getTestSessionStatusLabel($currentSession["status"]); ?></dd>
				</dl> <!-- /meta_info -->
			</div> <!-- /report_head -->

			<h2 id="report_summaries">
				Report Information
				<span class="heading_actions">
					<a id="report_summaries_link" class="hide_show hidden" href="javascript:;" onclick="hideShow(this.id, 'report_summaries_content');" title="Hide/show">Show</a>
					<a class="top" href="#top" title="Back to top">Back to top</a>
				</span>
			</h2>

			<div id="report_summaries_content" class="global_section hide">
				<h2 id="test_objective">
					Test Objective
					<span class="heading_actions">
						<a id="test_objective_link" class="hide_show hidden" href="javascript:;" onclick="hideShow(this.id, 'test_objective_content');" title="Hide/show">Show</a>
						<a class="top" href="#top" title="Back to top">Back to top</a>
					</span>
				</h2>
				<div id="test_objective_content" class="editcontent hide">
					<?php echo ($currentSession["test_objective"] != "" || $currentSession["test_objective"] != null) ? nl2br(MiscUtils::formatWikimarkups($currentSession["test_objective"])) : "No objective filled yet"; ?>
				</div> <!-- /editcontent -->

				<h2 id="environment" style="margin-top: 20px">
					Test Environment: <span class="emphasis"><?php echo $currentEnvironment["name"]; ?></span>
					<span class="heading_actions">
						<a id="environment_link" class="hide_show hidden" href="javascript:;" onclick="hideShow(this.id, 'environment_content');" title="Hide/show">Show</a>
						<a class="top" href="#top" title="Back to top">Back to top</a>
					</span>
				</h2>
				<div id="environment_content" class="editcontent hide" style="margin-top: 20px">
					<p><strong>Name: </strong><?php echo $currentEnvironment["name"]; ?></p>
					<p><strong>Description: </strong><?php echo ($currentEnvironment["description"] != "" || $currentEnvironment["description"] != null) ? nl2br(MiscUtils::formatWikimarkups($currentEnvironment["description"])) : "No description filled yet"; ?></p>
					<p><strong>CPU: </strong><?php echo ($currentEnvironment["cpu"] != "" || $currentEnvironment["cpu"] != null) ? $currentEnvironment["cpu"] : ""; ?></p>
					<p><strong>Board: </strong><?php echo ($currentEnvironment["board"] != "" || $currentEnvironment["board"] != null) ? $currentEnvironment["board"] : ""; ?></p>
					<p><strong>GPU: </strong><?php echo ($currentEnvironment["gpu"] != "" || $currentEnvironment["gpu"] != null) ? $currentEnvironment["gpu"] : ""; ?></p>
					<p><strong>Other hw: </strong><?php echo ($currentEnvironment["other_hardware"] != "" || $currentEnvironment["other_hardware"] != null) ? nl2br($currentEnvironment["other_hardware"]) : ""; ?></p>
				</div> <!-- /editcontent -->

				<h2 id="build_image">
					Build (image): <span class="emphasis"><?php echo $currentImage["name"]; ?></span>
					<span class="heading_actions">
						<a id="build_image_link" class="hide_show hidden" href="javascript:;" onclick="hideShow(this.id, 'build_image_content');" title="Hide/show">Show</a>
						<a class="top" href="#top" title="Back to top">Back to top</a>
					</span>
				</h2>
				<div id="build_image_content" class="editcontent hide" style="margin-top: 20px">
					<p><strong>Name: </strong><?php echo $currentImage["name"]; ?></p>
					<p><strong>Description: </strong><?php echo ($currentImage["description"] != "" || $currentImage["description"] != null) ? nl2br(MiscUtils::formatWikimarkups($currentImage["description"])) : "No description filled yet"; ?></p>
					<p><strong>Operating system: </strong><?php echo $currentImage["os"]; ?></p>
					<p><strong>Distribution: </strong><?php echo $currentImage["distribution"]; ?></p>
					<p><strong>Version: </strong><?php echo $currentImage["version"]; ?></p>
					<p><strong>Kernel: </strong><?php echo ($currentImage["kernel"] != "" || $currentImage["kernel"] != null) ? $currentImage["kernel"] : ""; ?></p>
					<p><strong>Architecture: </strong><?php echo $currentImage["architecture"]; ?></p>
					<p><strong>Other fw: </strong><?php echo ($currentImage["other_fw"] != "" || $currentImage["other_fw"] != null) ? nl2br($currentImage["other_fw"]) : ""; ?></p>
					<p><strong>Binary link: </strong><?php if($currentImage["binary_link"] != "" || $currentImage["binary_link"] != null): ?><a href="<?php echo $currentImage["binary_link"]; ?>" title=""><?php echo $currentImage["binary_link"]; ?></a><?php endif; ?></p>
					<p><strong>Source link: </strong><?php if($currentImage["source_link"] != "" || $currentImage["source_link"] != null): ?><a href="<?php echo $currentImage["source_link"]; ?>" title=""><?php echo $currentImage["source_link"]; ?></a><?php endif; ?></p>
				</div> <!-- /editcontent -->

				<h2 id="notes_summary">
					Environment Summary
					<span class="heading_actions">
						<a id="notes_summary_link" class="hide_show hidden" href="javascript:;" onclick="hideShow(this.id, 'notes_summary_content');" title="Hide/show">Show</a>
						<a class="top" href="#top" title="Back to top">Back to top</a>
					</span>
				</h2>
				<div id="notes_summary_content" class="editcontent hide">
					<?php echo ($currentSession["notes"] != "" || $currentSession["notes"] != null) ? nl2br(MiscUtils::formatWikimarkups($currentSession["notes"])) : "No notes filled yet"; ?>
				</div> <!-- /editcontent -->

				<h2 id="qa_summary">
					Quality Summary
					<span class="heading_actions">
						<a id="qa_summary_link" class="hide_show hidden" href="javascript:;" onclick="hideShow(this.id, 'qa_summary_content');" title="Hide/show">Show</a>
						<a class="top" href="#top" title="Back to top">Back to top</a>
					</span>
				</h2>
				<div id="qa_summary_content" class="editcontent hide">
					<?php echo ($currentSession["qa_summary"] != "" || $currentSession["qa_summary"] != null) ? nl2br(MiscUtils::formatWikimarkups($currentSession["qa_summary"])) : "No quality summary filled yet"; ?>
				</div> <!-- /editcontent -->

				<h2 id="issue_summary">
					Issue Summary
					<span class="heading_actions">
						<a id="issue_summary_link" class="hide_show hidden" href="javascript:;" onclick="hideShow(this.id, 'issue_summary_content');" title="Hide/show">Show</a>
						<a class="top" href="#top" title="Back to top">Back to top</a>
					</span>
				</h2>
				<div id="issue_summary_content" class="editcontent hide">
					<?php echo ($currentSession["issue_summary"] != "" || $currentSession["issue_summary"] != null) ? nl2br(MiscUtils::formatWikimarkups($currentSession["issue_summary"])) : "No issue summary filled yet"; ?>
				</div>
			</div>

			<h2 id="test_results">
				Test Results
				<span class="heading_actions">
					<a id="test_results_link" class="hide_show displayed" href="javascript:;" onclick="hideShow(this.id, 'test_results_content');" title="Hide/show">Hide</a>
					<a class="top" href="#top" title="Back to top">Back to top</a>
				</span>
			</h2>
			<div id="test_results_content" class="section emphasized_section">
				<div class="container">
					<div id="test_results_navi">
						<?php if(isset($previousSession)): ?>
							<a class="go_to_previous" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $previousSession["id"], "display" => $currentDisplay)); ?>" title="Previous report">Previous report</a>
						<?php endif; ?>

						<?php if(isset($nextSession)): ?>
							<a class="go_to_next" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $nextSession["id"], "display" => $currentDisplay)); ?>" title="Next report">Next report</a>
						<?php endif; ?>
					</div> <!-- /test_results_navi -->

					<h3 class="first">Result Summary</h3>
					<div class="wrap">
						<table id="test_result_overview">
							<tbody>
								<tr class="even">
									<td class="title">Total test cases</td>
									<td class="value"><strong><?php echo $totalTestResults; ?></strong></td>
									<?php if(isset($previousSession)): ?>
									<td class="change <?php echo getArrow($totalTestResults-$previousTotalTestResults); ?>"><em><?php echo (($totalTestResults-$previousTotalTestResults) >= 0 ? "+" : "").($totalTestResults - $previousTotalTestResults); ?></em></td>
									<?php else: ?>
									<td class="change unchanged"></td>
									<?php endif; ?>
									<td rowspan="8" style="background-color: white">
										<div id="summary_graph_wrapper">
											<div class="bluff_wrapper" style="position: relative; border: none; padding: 0px; height: 210px;">
												<canvas id="summary_graph_canvas" height="210"></canvas>
												<script type="text/javascript">
													var g = new Bluff.<?php echo sfConfig::get('app_barchart_report_summary'); ?>('summary_graph_canvas', '410x210');
													g.tooltips = true;

													g.set_theme({
														colors: ['#bcd483', '#f36c6c', '#ddd'],
														marker_color: '#aea9a9',
														font_color: '#6f6f6f',
														background_colors: ['#ffffff', '#ffffff']
													});

												    g.hide_title = true;
												    g.tooltips = true;
												    g.sort = false;
												    g.bar_spacing = 0.7;
												    g.marker_font_size = 18;
												    g.legend_font_size = 24;

													g.data("pass", [<?php foreach($reversedPreviousSummaryNumbers as $data) echo $data["pass"].","; echo $currentSummaryNumbers["pass"].","; ?>]);
													g.data("fail", [<?php foreach($reversedPreviousSummaryNumbers as $data) echo $data["fail"].","; echo $currentSummaryNumbers["fail"].","; ?>]);
													g.data("block", [<?php foreach($reversedPreviousSummaryNumbers as $data) echo $data["block"].","; echo $currentSummaryNumbers["block"].","; ?>]);

													g.labels = { <?php
														$i = 0;
														foreach($reversedPreviousSummaryNumbers as $data)
														{
															echo $i.": '".format_datetime($data["created_at"], "y-MM-dd HH:mm")."',";
															$i++;
														}
														echo $i.": 'Current',";
														?> };

													g.draw();
												</script>
											</div>
										</div> <!-- /summary_graph_wrapper -->
									</td>
								</tr>
								<tr class="odd">
									<td>Passed</td>
									<td><strong class="pass"><?php echo $totalResultsPassed; ?></strong></td>
									<?php if(isset($previousSession)): ?>
									<td class="<?php echo getArrow($totalResultsPassed-$previousTotalResultsPassed); ?>"><em><?php echo (($totalResultsPassed-$previousTotalResultsPassed) >= 0 ? "+" : "").($totalResultsPassed - $previousTotalResultsPassed); ?></em></td>
									<?php else: ?>
									<td class="unchanged"></td>
									<?php endif; ?>
								</tr>
								<tr class="even">
									<td>Failed</td>
									<td><strong class="fail"><?php echo $totalResultsFailed; ?></strong></td>
									<?php if(isset($previousSession)): ?>
									<td class="<?php echo getArrow($totalResultsFailed-$previousTotalResultsFailed); ?>"><em><?php echo (($totalResultsFailed-$previousTotalResultsFailed) >= 0 ? "+" : "").($totalResultsFailed - $previousTotalResultsFailed); ?></em></td>
									<?php else: ?>
									<td class="unchanged"></td>
									<?php endif; ?>
								</tr>
								<tr class="odd">
									<td>Blocked</td>
									<td><strong><?php echo $totalResultsBlocked; ?></strong></td>
									<?php if(isset($previousSession)): ?>
									<td class="<?php echo getArrow($totalResultsBlocked-$previousTotalResultsBlocked); ?>"><em><?php echo (($totalResultsBlocked-$previousTotalResultsBlocked) >= 0 ? "+" : "").($totalResultsBlocked - $previousTotalResultsBlocked); ?></em></td>
									<?php else: ?>
									<td class="unchanged"></td>
									<?php endif; ?>
								</tr>
								<tr class="even">
									<td>Deferred</td>
									<td><strong class="deferred"><?php echo $totalResultsDeferred; ?></strong></td>
									<?php if(isset($previousSession)): ?>
									<td class="<?php echo getArrow($totalResultsDeferred-$previousTotalResultsDeferred); ?>"><em><?php echo (($totalResultsBlocked-$previousTotalResultsDeferred) >= 0 ? "+" : "").($totalResultsDeferred - $previousTotalResultsDeferred); ?></em></td>
									<?php else: ?>
									<td class="unchanged"></td>
									<?php endif; ?>
								</tr>
								<tr class="odd">
									<td>Not run</td>
									<td><strong class="not_run"><?php echo $totalResultsNotRun; ?></strong></td>
									<?php if(isset($previousSession)): ?>
									<td class="<?php echo getArrow($totalResultsNotRun-$previousTotalResultsNotRun); ?>"><em><?php echo (($totalResultsBlocked-$previousTotalResultsNotRun) >= 0 ? "+" : "").($totalResultsNotRun - $previousTotalResultsNotRun); ?></em></td>
									<?php else: ?>
									<td class="unchanged"></td>
									<?php endif; ?>
								</tr>
								<tr class="even">
									<td>Run rate</td>
									<td><strong><?php echo $runRate; ?>%</strong></td>
									<?php if(isset($previousSession)): ?>
									<td class="<?php echo getArrow($runRate-$previousRunRate); ?>"><em><?php echo (($runRate-$previousRunRate) >= 0 ? "+" : "").($runRate - $previousRunRate); ?>%</em></td>
									<?php else: ?>
									<td class="unchanged"></td>
									<?php endif; ?>
								</tr>
								<tr class="odd">
									<td>Pass rate of total</td>
									<td><strong><?php echo $totalPassRate; ?>%</strong></td>
									<?php if(isset($previousSession)): ?>
									<td class="<?php echo getArrow($totalPassRate-$previousTotalPassRate); ?>"><em><?php echo (($totalPassRate-$previousTotalPassRate) >= 0 ? "+" : "").($totalPassRate - $previousTotalPassRate); ?>%</em></td>
									<?php else: ?>
									<td class="unchanged"></td>
									<?php endif; ?>
								</tr>
								<tr class="even">
									<td>Pass rate of executed</td>
									<td><strong><?php echo $executedPassRate; ?>%</strong></td>
									<?php if(isset($previousSession)): ?>
									<td class="<?php echo getArrow($executedPassRate-$previousExecutedPassRate); ?>"><em><?php echo (($executedPassRate-$previousExecutedPassRate) >= 0 ? "+" : "").($executedPassRate - $previousExecutedPassRate); ?>%</em></td>
									<?php else: ?>
									<td class="unchanged"></td>
									<?php endif; ?>
								</tr>
								<?php if(isset($nftIndex)): ?>
									<tr class="odd">
										<td>NFT index</td>
										<td><strong><?php echo $nftIndex; ?>%</strong></td>
										<?php if(isset($previousNftIndex)): ?>
										<td class="<?php echo getArrow($nftIndex-$previousNftIndex); ?>"><em><?php echo (($nftIndex-$previousNftIndex) >= 0 ? "+" : "").($nftIndex - $previousNftIndex); ?>%</em></td>
										<?php else: ?>
										<td class="unchanged"></td>
										<?php endif; ?>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div> <!-- /wrap -->

					<h3 id="test_features">Test Results by Feature</h3>
					<span class="sort">
						<a id="detailed_feature" class="see_feature_comment_button sort_btn active" href="#" title="See comment and grading">See comment and grading</a>
						<a id="detailed_feature" class="see_feature_history_button sort_btn" href="#" title="See history pass%">See history pass%</a>
						<!-- <a id="detailed_feature" class="see_feature_testset_button sort_btn" href="#" title="See testset pass%">See testset pass%</a> -->
					</span>
					<span style="float: right">
						Switch to:
						<a class="shortcut_link_big<?php if($currentDisplay == "basic") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "basic")); ?>#test_features" title="Basic">Basic</a>
						<a class="shortcut_link_big<?php if($currentDisplay == "detailed") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "detailed")); ?>#test_features" title="Detailed">Detailed</a>
						<a class="shortcut_link_big<?php if($currentDisplay == "history") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "history")); ?>#test_features" title="History">History</a>
					</span>

					<table id="test_results_by_feature" class="feature_detailed_results" style="display: table">
						<thead class="even">
							<tr>
								<th class="th_feature">Feature</th>
								<th class="th_total">Total</th>
								<th class="th_passed">Passed</th>
								<th class="th_failed">Failed</th>
								<th class="th_not_testable">Blocked</th>
								<th class="th_not_testable">Deferred</th>
								<th class="th_not_testable">Not run</th>
								<th class="th_pass_rate">Pass%</th>
								<th class="th_graph">&nbsp;</th>
								<th class="th_grading">Grading</th>
							</tr>
						</thead>

						<tbody> <?php
							$line = 0;
							foreach($currentSession["features"] as $feature)
							{
								$line++; ?>

								<tr class="feature_record <?php echo ($line % 2 == 0) ? "odd" : "even"; ?>"> <?php
									$totalPassed = $feature["pass"];
									$totalFailed = $feature["fail"];
									$totalBlocked = $feature["block"];
									$totalDeferred = $feature["deferred"];
									$totalNotRun = $feature["not_run"];
									$totalResults = $feature["total"];

									$percentage = round($totalPassed / $totalResults * 100, 2);

									if($percentage < 50)
										$color = "red";
									else if($percentage < 90)
										$color = "yellow";
									else
										$color = "green";

									$proportionalSize = $totalResults / $totalFeaturesResults * 100;

									$urlFeature = ($currentDisplay != "detailed") ? url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "detailed")) : "";
								?>

									<td><a href="<?php echo $urlFeature."#".MiscUtils::slugify($feature["label"]); ?>" title="<?php echo $feature["label"]; ?>"><?php echo $feature["label"]; ?></a></td>
									<td class="total"><?php echo $totalResults; ?></td>
									<td class="pass"><?php echo $totalPassed; ?></td>
									<td class="fail"><?php echo $totalFailed; ?></td>
									<td class="na"><?php echo $totalBlocked; ?></td>
									<td class="deferred"><?php echo $totalDeferred; ?></td>
									<td class="not_run"><?php echo $totalNotRun; ?></td>
									<td class="rate"><?php echo $percentage; ?>%</td>
									<td>
										<div class="htmlgraph">
											<div class="passed" style="width: <?php echo round(($totalPassed / $totalResults * 100) * $proportionalSize / 100); ?>%" title="<?php echo $totalPassed; ?> passed">&nbsp;</div>
											<div class="failed" style="width: <?php echo round(($totalFailed / $totalResults * 100) * $proportionalSize / 100); ?>%" title="<?php echo $totalFailed; ?> failed">&nbsp;</div>
											<div class="na" style="width: <?php echo round(($totalBlocked / $totalResults * 100) * $proportionalSize / 100); ?>%" title="<?php echo $totalBlocked; ?> blocked">&nbsp;</div>
										</div> <!-- /htmlgraph -->
									</td>
									<td class="feature_record_grading">
										<span class="content grading_<?php echo $color; ?>">&nbsp;&nbsp;</span>
									</td>
								</tr> <?php
							} ?>
						</tbody>
					</table>

					<table id="test_feature_history_results" class="feature_detailed_results_with_passrate_history" style="display: none;">
						<thead class="even">
							<tr>
								<th class="th_feature">Feature</th>
								<th class="th_total">Total</th>
								<th class="th_passed">Passed</th>
								<th class="th_failed">Failed</th>
								<th class="th_not_testable">Blocked</th>
								<?php for($i=count($previousSessions)-1; $i>=0; $i--): ?>
									<th class="th_history_result"><a href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $previousSessions[$i]["id"])); ?>" title="<?php echo "(".$previousSessions[$i]["id"].") ".$previousSessions[$i]["name"]; ?>"><?php echo format_datetime($previousSessions[$i]["created_at"], "y-MM-dd HH:mm")." Testset ID: ".$currentSession["testset"]; ?></a></th>
								<?php endfor; ?>
								<th class="th_result current_feature_history_result">Current</th>
							</tr>
						</thead>

						<tbody> <?php
							$line = 0;
							foreach($currentSession["features"] as $feature)
							{
								$line++; ?>

								<tr class="feature_record <?php echo ($line % 2 == 0) ? "odd" : "even"; ?>"> <?php
									$totalPassed = $feature["pass"];
									$totalFailed = $feature["fail"];
									$totalBlocked = $feature["block"];
									$totalResults = $feature["total"];

									$percentage = round($totalPassed / $totalResults * 100, 2);

									if($percentage < 50)
										$color = "red";
									else if($percentage < 90)
										$color = "yellow";
									else
										$color = "green";

									$proportionalSize = $totalResults / $totalTestResults * 100;

									$urlFeature = ($currentDisplay != "history") ? url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "history")) : "";
								?>

									<td><a href="<?php echo $urlFeature."#".MiscUtils::slugify($feature["label"]); ?>" title="<?php echo $feature["label"]; ?>"><?php echo $feature["label"]; ?></a></td>
									<td class="total"><?php echo $totalResults; ?></td>
									<td class="pass"><?php echo $totalPassed; ?></td>
									<td class="fail"><?php echo $totalFailed; ?></td>
									<td class="na"><?php echo $totalBlocked; ?></td>
									<?php for($i=count($previousSessions)-1; $i>=0; $i--): ?>
										<?php $featureKey = MiscUtils::slugify($feature["label"]); ?>

										<?php if(array_key_exists($featureKey, $previousSessions[$i]->getRaw("features"))): ?>
											<?php $previousFeature = $previousSessions[$i]["features"]->getRaw($featureKey); ?>
											<td class="test_feature_pass_rate feature_history_result"><?php echo ($previousFeature["percentage"] > 0) ? round($previousFeature["percentage"], 2)."%" : "0%"; ?></td>
										<?php else: ?>
											<td class="test_feature_pass_rate feature_history_result">-</td>
										<?php endif; ?>
									<?php endfor; ?>
									<td class="rate current_feature_history_result"><?php echo $percentage; ?>%</td>
								</tr> <?php
							} ?>
						</tbody>
					</table>

					<!-- <table id="test_feature_build_results" class="feature_detailed_results_with_build_id" style="display: none;"></table> -->
				</div> <!-- /container -->
			</div> <!-- /section -->

			<?php if($currentDisplay != "basic"): ?>

				<?php if(count($currentSession["results"]) > 0): ?>
					<h2 id="detailed_results">
						Detailed Test Results
						<small>
							<a href="<?php echo url_for("export_report", array("id" => $currentSession["id"])); ?>" title="Download as CSV">Download as CSV</a>
						</small>
						<span class="heading_actions">
							<a id="detailed_results_link" class="hide_show displayed" href="javascript:;" onclick="hideShow(this.id, 'detailed_results_content');" title="Hide/show">Hide</a>
							<a class="top" href="#top" title="Back to top">Back to top</a>
						</span>
					</h2>

					<div id="detailed_results_content" class="">
					<?php if($currentDisplay == "detailed"): ?>
						<table id="tableOfResults" class="detailed_results" id="detailed_functional_test_results" style="display: table;">
							<thead>
								<tr>
									<th colspan="5">
										<span class="sort">
											<a id="detailed_case_see_none" class="see_only_failed_button sort_btn non_nft_button active" href="javascript:;" onClick="hideShowAll('tableOfResults', '1', 'hide');" title="See only failed">See only failed</a>
											<a id="detailed_case_see_all" class="see_all_button sort_btn non_nft_button" href="javascript:; " onClick="hideShowAll('tableOfResults', '0', 'hide');" title="See all">See all</a>
										</span>
										<span style="float: right">
											Switch to:
											<a class="shortcut_link_big<?php if($currentDisplay == "basic") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "basic")); ?>#detailed_results" title="Basic">Basic</a>
											<a class="shortcut_link_big<?php if($currentDisplay == "detailed") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "detailed")); ?>#detailed_results" title="Detailed">Detailed</a>
											<a class="shortcut_link_big<?php if($currentDisplay == "history") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "history")); ?>#detailed_results" title="History">History</a>
										</span>
									</th>
								</tr>

								<tr>
									<th id="th_case_id">Name</th>
									<th id="th_test_case">Description</th>
									<th id="th_result">Result</th>
									<th id="th_bugs">Bugs</th>
									<th id="th_notes">Notes</th>
								</tr>
							</thead>

							<?php $previousFeatureKey = ""; ?>
							<?php foreach($currentSession["results"] as $result): ?>
								<?php $featureKey = MiscUtils::slugify($result["label"]); ?>

								<?php if($featureKey != $previousFeatureKey): ?>
									</tbody>

									<tbody id="<?php echo $featureKey; ?>">
										<tr id="feature-<?php echo $featureKey; ?>" class="feature_name"> <?php
											$feature = $currentSession["features"]->getRaw($featureKey);
											$totalPassed = $feature["pass"]; ?>

											<td colspan="5">
												<?php echo $result["label"]; ?>
												<a class="see_all_toggle" href="javascript:;" onClick="hideShowFeature('<?php echo $featureKey; ?>', '2', 'hide');">Hide/show <?php echo $totalPassed; ?> passing tests</a>
											</td>
										</tr>

								<?php endif; ?>

								<?php $previousFeatureKey = $featureKey; ?>
									<tr <?php if(Labeler::decisionToText($result["decision_criteria_id"]) == "pass"): ?>id="result-<?php echo $result["id"]; ?>"<?php endif; ?> class="testcase result_<?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?> <?php if(Labeler::decisionToText($result["decision_criteria_id"]) == "pass") echo "hide"; ?>">
										<td class="testcase_case_id"><?php echo $result["name"]; ?></td>
										<td class="testcase_name"><?php echo $result["complement"]; ?></td>
										<td class="testcase_result <?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?>">
											<span class="content"><?php echo ucfirst(str_replace("_", " ", Labeler::decisionToText($result["decision_criteria_id"]))); ?></span>
										</td>
										<td class="testcase_bugs"><div class="content"><?php echo MiscUtils::formatWikimarkups($result["bugs"]); ?></div></td>
										<td class="testcase_notes"><div class="content"><?php echo nl2br(MiscUtils::formatWikimarkups($result["comment"])); ?></div></td>
									</tr>
							<?php endforeach; ?>
						</table>
					<?php endif; ?>

					<?php if($currentDisplay == "history"): ?>
						<table class="detailed_results history">
							<thead>
								<tr>
									<th colspan="<?php echo 5 + count($previousSessions); ?>">
										<span class="sort">
											<a id="detailed_case" class="see_history_button sort_btn non_nft_button active" href="#" title="See history">See history</a>
										</span>
										<span style="float: right">
											Switch to:
											<a class="shortcut_link_big<?php if($currentDisplay == "basic") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "basic")); ?>#detailed_results" title="Basic">Basic</a>
											<a class="shortcut_link_big<?php if($currentDisplay == "detailed") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "detailed")); ?>#detailed_results" title="Detailed">Detailed</a>
											<a class="shortcut_link_big<?php if($currentDisplay == "history") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "history")); ?>#detailed_results" title="History">History</a>
										</span>
									</th>
								</tr>

								<tr>
									<th id="th_history_case_id">Name</th>
									<th id="th_history_test_case">Description</th>
									<?php for($i=count($previousSessions)-1; $i>=0; $i--): ?>
										<th class="th_history_result"><a href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $previousSessions[$i]["id"])); ?>" title="<?php echo "(".$previousSessions[$i]["id"].") ".$previousSessions[$i]["name"]; ?>"><?php echo format_datetime($previousSessions[$i]["created_at"], "y-MM-dd HH:mm")." Testset ID: ".$currentSession["testset"]; ?></a></th>
									<?php endfor; ?>
									<th id="th_result">Current</th>
									<th id="th_notes">Latest Bugs</th>
									<th id="th_notes">Latest Notes</th>
								</tr>
							</thead>

							<?php $previousFeatureKey = ""; ?>
							<?php foreach($currentSession["results"] as $resultKey => $result): ?>
								<?php $featureKey = MiscUtils::slugify($result["label"]); ?>

								<?php if($featureKey != $previousFeatureKey): ?>
									</tbody>

									<tbody>
										<tr id="<?php echo MiscUtils::slugify($result["label"]); ?>" class="feature_name"> <?php
											$feature = $currentSession["features"]->getRaw($featureKey);
											$totalPassed = $feature["pass"]; ?>

											<td colspan="<?php echo 5 + count($previousSessions); ?>"><?php echo $result["label"]; ?></td>
										</tr>
									</tbody>

									<tbody>
								<?php endif; ?>

								<?php $previousFeatureKey = $featureKey; ?>
									<tr class="testcase result_<?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?>">
										<td class="testcase_case_id"><?php echo $result["name"]; ?></td>
										<td class="testcase_name"><?php echo $result["complement"]; ?></td>
										<?php for($i=count($previousSessions)-1; $i>=0; $i--): ?>
											<?php if(array_key_exists($resultKey, $previousSessions[$i]->getRaw("results"))): ?>
												<?php $previousResult = $previousSessions[$i]->getRaw("results"); ?>
												<td class="testcase_result history_result <?php echo Labeler::decisionToText($previousResult[$resultKey]["decision_criteria_id"]); ?>"><?php echo ucfirst(Labeler::decisionToText($previousResult[$resultKey]["decision_criteria_id"])); ?></td>
											<?php else: ?>
												<td class="testcase_result history_result">-</td>
											<?php endif; ?>
										<?php endfor; ?>
										<td class="testcase_result <?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?>">
											<span class="content"><?php echo ucfirst(str_replace("_", " ", Labeler::decisionToText($result["decision_criteria_id"]))); ?></span>
										</td>
										<td class="testcase_bugs"><div class="content"><?php echo MiscUtils::formatWikimarkups($result["bugs"]); ?></div></td>
										<td class="testcase_notes"><div class="content"><?php echo nl2br(MiscUtils::formatWikimarkups($result["comment"])); ?></div></td>
									</tr>
							<?php endforeach; ?>
						</table>
					<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if(count($currentSession["measures"]) > 0): ?>
					<h2 id="detailed_nft_results">
						Non-functional Test Results
						<span class="heading_actions">
							<a id="detailed_nft_results_link" class="hide_show hidden" href="javascript:;" onclick="hideShow(this.id, 'detailed_nft_results_content');" title="Hide/show">Show</a>
							<a class="top" href="#top" title="Back to top">Back to top</a>
						</span>
					</h2>

					<div id="detailed_nft_results_content" class="">
					<?php if($currentDisplay == "detailed"): ?>
						<table id="tableOfMeasures" class="non-functional_results detailed_results" style="display: table;">
							<thead>
								<tr>
									<th colspan="8">
										<span style="float: right">
											Switch to:
											<a class="shortcut_link_big<?php if($currentDisplay == "basic") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "basic")); ?>#detailed_nft_results" title="Basic">Basic</a>
											<a class="shortcut_link_big<?php if($currentDisplay == "detailed") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "detailed")); ?>#detailed_nft_results" title="Detailed">Detailed</a>
											<a class="shortcut_link_big<?php if($currentDisplay == "history") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "history")); ?>#detailed_nft_results" title="History">History</a>
										</span>
									</th>
								</tr>

								<tr>
									<th id="th_test_case">Name</th>
									<th class="th_name">Measurement</th>
									<th class="th_measured">Value</th>
									<th class="th_target">Target</th>
									<th class="th_limit">Fail limit</th>
									<th class="th_to_target">% of target</th>
									<th class="th_result">Result</th>
									<th id="th_notes">Notes</th>
								</tr>
							</thead>

							<?php $previousFeatureKey = ""; ?>
							<?php foreach($currentSession["measures"] as $measure): ?>
								<?php $featureKey = MiscUtils::slugify($measure["label"]); ?>

								<?php if($featureKey != $previousFeatureKey): ?>
									</tbody>

									<tbody id="<?php echo $featureKey; ?>">
										<tr id="feature-<?php echo MiscUtils::slugify($measure["label"]); ?>" class="feature_name"> <?php
											$feature = $currentSession["features"]->getRaw($featureKey);
											$totalPassed = $feature["pass"]; ?>

											<td colspan="8">
												<?php echo $measure["label"]; ?>
												<a class="see_all_toggle" href="javascript:;" onClick="hideShowFeature('<?php echo $featureKey; ?>', '2', 'hide');">Hide/show <?php echo $totalPassed; ?> passing tests</a>
											</td>
										</tr>

								<?php endif; ?>

								<?php $previousFeatureKey = $featureKey; ?>
									<tr <?php if(Labeler::decisionToText($measure["decision_criteria_id"]) == "pass"): ?>id="measure-<?php echo $measure["id"]; ?>"<?php endif; ?> class="testcase result_<?php echo Labeler::decisionToText($measure["decision_criteria_id"]); ?> <?php if(Labeler::decisionToText($measure["decision_criteria_id"]) == "pass") echo "hide"; ?>">
										<td class="testcase_name"><?php echo $measure["name"]; ?></td>
										<td class="testcase_measurement"><?php echo $measure["complement"]; ?></td>
										<td class="testcase_value"><?php echo $measure["measures"]["value"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["value"]["unit"]; ?></span></td>
										<td class="testcase_target"><?php echo $measure["measures"]["target"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["target"]["unit"]; ?></span></td>
										<td class="testcase_limit"><?php echo $measure["measures"]["limit"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["limit"]["unit"]; ?></span></td>
										<td class="testcase_to_target"><?php
											$value = $measure["measures"]["value"]["value"];
											$target = $measure["measures"]["target"]["value"];
											$failLimit = $measure["measures"]["limit"]["value"];

											if($failLimit > $target)
											{
												$targetPercentage = (round($target / $value * 100, 1) > 100) ? 100 : round($target / $value * 100, 1);
											}
											else if($failLimit < $target)
												$targetPercentage = (round($value / $target * 100, 1) > 100) ? 100 : round($value / $target * 100, 1);
											else
												$targetPercentage = 100;
											echo $targetPercentage;
										?> %</td>
										<td class="testcase_result <?php echo Labeler::decisionToText($measure["decision_criteria_id"]); ?>">
											<span class="content"><?php echo ucfirst(Labeler::decisionToText($measure["decision_criteria_id"])); ?></span>
										</td>
										<td class="testcase_notes"><div class="content"><?php echo nl2br(MiscUtils::formatWikimarkups($measure["comment"])); ?></div></td>
									</tr>
							<?php endforeach; ?>
						</table>
					<?php endif; ?>

					<?php if($currentDisplay == "history"): ?>
						<table class="non-functional_results detailed_results history">
							<thead>
								<tr>
									<th colspan="8">
										<span class="sort">
											<a id="detailed_case" class="see_history_button sort_btn active" href="#" title="See history">See history</a>
										</span>
										<span style="float: right">
											Switch to:
											<a class="shortcut_link_big<?php if($currentDisplay == "basic") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "basic")); ?>#detailed_nft_results" title="Basic">Basic</a>
											<a class="shortcut_link_big<?php if($currentDisplay == "detailed") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "detailed")); ?>#detailed_nft_results" title="Detailed">Detailed</a>
											<a class="shortcut_link_big<?php if($currentDisplay == "history") echo "_active"; ?>" href="<?php echo url_for("testset_session", array("testset" => $currentTestset["testset_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"], "display" => "history")); ?>#detailed_nft_results" title="History">History</a>
										</span>
									</th>
								</tr>

								<tr>
									<th id="th_test_case">Name</th>
									<th class="th_name">Measurement</th>
									<?php if(count($previousSessions) > 0): ?>
									<th class="th_measured"><?php echo format_datetime($previousSessions[0]["created_at"], "y-MM-dd HH:mm")." - current"; ?></th>
									<?php else: ?>
									<th class="th_measured">Current</th>
									<?php endif; ?>
								</tr>
							</thead>

							<?php $previousFeatureKey = ""; ?>
							<?php foreach($currentSession["measures"] as $resultKey => $measure): ?>
								<?php $featureKey = MiscUtils::slugify($measure["label"]); ?>

								<?php if($featureKey != $previousFeatureKey): ?>
									</tbody>

									<tbody>
										<tr id="<?php echo MiscUtils::slugify($measure["label"]); ?>" class="feature_name"> <?php
											$feature = $currentSession["features"]->getRaw($featureKey); ?>

											<td colspan="8">
												<?php echo $measure["label"]; ?>
											</td>
										</tr>
									</tbody>

									<tbody>
								<?php endif; ?>

								<?php $previousFeatureKey = $featureKey; ?>
									<tr class="testcase result_<?php echo Labeler::decisionToText($measure["decision_criteria_id"]); ?>">
										<td class="testcase_name"><?php echo $measure["name"]; ?></td>
										<td class="testcase_measurement"><?php echo $measure["complement"]; ?></td>

										<?php
											// Prepare an array to store all measures' values
											$measureValues = array();
											for($i=count($previousSessions)-1; $i>=0; $i--)
											{
												if(array_key_exists($resultKey, $previousSessions[$i]->getRaw("measures")))
												{
													$previousMeasures = $previousSessions[$i]->getRaw("measures");
													array_push($measureValues, $previousMeasures[$resultKey]["measures"]["value"]["value"]);
												}
											}
											// Push current measure into the array
											array_push($measureValues, $measure["measures"]["value"]["value"]);

											// If there is only current measure, push it another time to get a flat graph
											if(count($measureValues) == 1)
												array_push($measureValues, $measure["measures"]["value"]["value"]);

											// Compute min, max, average and median
											$min = min($measureValues);
											$max = max($measureValues);
											$avg = array_sum($measureValues) / count($measureValues);
											$med = MiscUtils::median($measureValues);
										?>

										<td class="testcase_graph">
											<div class="bluff-wrapper2" style="">
												<canvas id="nft-history-graph-<?php echo $measure["id"]; ?>" width="300" height="45"></canvas>
												<script type="text/javascript">
													var g = new Bluff.Line('nft-history-graph-<?php echo $measure["id"]; ?>', '300x45');
													g.tooltips = true;

													g.set_theme({
														colors: ['#08298A'],
														marker_color: '#aea9a9',
														font_color: '#6f6f6f',
														background_colors: ['#ffffff', '#ffffff']
													});

												    g.hide_title = true;
												    g.hide_legend = true;
												    g.hide_mini_legend = true;
												    g.hide_line_numbers = true;
												    g.hide_line_markers = true;
												    g.hide_dots = true;
												    g.tooltips = true;
												    g.sort = false;

												    g.line_width = 0.5;

												    g.data("", [<?php echo implode(",", $measureValues); ?>]);

													g.draw();
												</script>
											</div>

											<table class="nft_graph_key_figures">
												<tbody>
													<tr>
														<td class="unit"><?php echo $measure["measures"]["value"]["unit"]; ?></td>
														<td><span class="unit">min.</span><?php echo $min; ?></td>
														<td><span class="unit">avg.</span><?php echo $avg; ?></td>
														<td><span class="unit">max.</span><?php echo $max; ?></td>
														<td><span class="unit">med.</span><?php echo $med; ?></td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
							<?php endforeach; ?>
						</table>
					<?php endif; ?>
					</div>
				<?php endif; ?>

			<?php endif; ?>

			<h2 id="result_files">
				Result files
				<span class="heading_actions">
					<a id="result_files_link" class="hide_show displayed" href="javascript:;" onclick="hideShow(this.id, 'result_files_content');" title="Hide/show">Hide</a>
					<a class="top" href="#top" title="Back to top">Back to top</a>
				</span>
			</h2>

			<div id="result_files_content" class="global_section">
				<h2 id="attachments">Attachments
					<span class="heading_actions">
						<a id="attachments_link" class="hide_show displayed" href="javascript:;" onclick="hideShow(this.id, 'attachments_content');" title="Hide/show">Hide</a>
						<a class="top" href="#top" title="Back to top">Back to top</a>
					</span>
				</h2>
				<div id="attachments_content" class="">
					<ul class="file_list item_list attachment">
						<span class="file_list_ready">
						<?php if(isset($attachments) && count($attachments) > 0): ?>
							<?php $count = 0; ?>
							<?php foreach($attachments as $attachment): ?>
								<li id="attachment_<?php echo $count++; ?>">
									<a href="<?php echo $sf_request->getUriPrefix().$sf_request->getRelativeUrlRoot().$attachment["link"]; ?>" target="_blank" title=""><?php echo $attachment["name"]; ?></a>
								</li>
							<?php endforeach; ?>
						<?php else: ?>
							No attachments
						<?php endif; ?>
						</span>
					</ul>
				</div> <!-- /result_file_drag_drop_area -->

				<h2 id="raw_result_files">Original Result Files
					<span class="heading_actions">
						<a id="raw_result_files_link" class="hide_show displayed" href="javascript:;" onclick="hideShow(this.id, 'raw_result_files_content');" title="Hide/show">Hide</a>
						<a class="top" href="#top" title="Back to top">Back to top</a>
					</span>
				</h2>
				<div id="raw_result_files_content" class="">
					<ul class="file_list item_list attachment">
						<span class="file_list_ready">
						<?php if(isset($resultFiles) && count($resultFiles) > 0): ?>
							<?php $count = 0; ?>
							<?php foreach($resultFiles as $resultFile): ?>
								<li id="result_file_<?php echo $count++; ?>">
									<a href="<?php echo $sf_request->getUriPrefix().$sf_request->getRelativeUrlRoot().$resultFile["link"]; ?>" target="_blank" title=""><?php echo $resultFile["name"]; ?></a>
								</li>
							<?php endforeach; ?>
						<?php else: ?>
							No result files
						<?php endif; ?>
						</span>
					</ul>
				</div> <!-- /result_file_drag_drop_area -->
			</div>
		</div> <!-- page_content -->
	</div> <!-- /index_page -->
</div> <!-- /page -->

<script>
	function simple_confirmation(message, url)
	{
		if(confirm(message))
		{
			window.location = url;
		}
	}

	function hideShowAll(container, visibility, mClass)
	{
		var rows = document.getElementById(container).getElementsByTagName('tr');
		var regex = null;

		if(visibility == '0')
		{
			$("#detailed_case_see_none").removeClass('active');
			$("#detailed_case_see_all").addClass('active');
		}
		else
		{
			$("#detailed_case_see_all").removeClass('active');
			$("#detailed_case_see_none").addClass('active');
		}

		for (var i = 0; i < rows.length; i++)
		{
			if((rows[i].id).substring(0, 7) == "result-" || (rows[i].id).substring(0, 8) == "measure-")
				hide_show(rows[i].id, visibility, mClass);
		}
	}

	function hideShowFeature(container, visibility, mClass)
	{
		var rows = document.getElementById(container).getElementsByTagName('tr');

		for (var i = 0; i < rows.length; i++)
		{
			if((rows[i].id).substring(0, 7) == "result-" || (rows[i].id).substring(0, 8) == "measure-")
				hide_show(rows[i].id, visibility, mClass);
		}
	}

	/**
	 * Change the class of an element to make it visible or invisible via CSS.
	 *
	 * @param id
	 *            The identifier of the element to hide/unhide.
	 * @param visibility
	 */
	function hide_show(id, visibility, mClass)
	{
		element = document.getElementById(id);

		if (element)
		{
			// Workaround to get the class attribute (IE vs The World)
			if (navigator.appName == "Microsoft Internet Explorer")
				workaround = "className";
			else
				workaround = "class";

			elementClass = element.getAttribute(workaround);

			switch (visibility)
			{
				// Make the element visible
				case '0':
					var regex = new RegExp(mClass, "g");
					elementClass = elementClass.replace(regex, '');
					element.setAttribute(workaround, trim(elementClass));
					break;

				// Make the element invisible
				case '1':
					if (elementClass == '')
						elementClass = mClass;
					else if (elementClass.search(mClass) == -1)
						elementClass = trim(elementClass) + ' ' + mClass;
					element.setAttribute(workaround, elementClass);
					break;

				// Invert the visibility of the element
				case '2':
					// If element is visible
					if (elementClass == '' || elementClass.search(mClass) == -1)
					{
						if (elementClass == '')
							elementClass = mClass;
						else
							elementClass = trim(elementClass) + ' ' + mClass;
						element.setAttribute(workaround, elementClass);
					}
					// Otherwise if element is invisible
					else
					{
						var regex = new RegExp(mClass, "g");
						elementClass = elementClass.replace(regex, '');
						element.setAttribute(workaround, trim(elementClass));
					}
					break;
			}
		}
	}

	/**
	 * Trim a string from both sides.
	 *
	 * @param stringToTrim
	 *            The string to trim.
	 *
	 * @return The string trimmed.
	 */
	function trim(stringToTrim)
	{
		return stringToTrim.replace(/^\s+|\s+$/g, "");
	}
</script>
