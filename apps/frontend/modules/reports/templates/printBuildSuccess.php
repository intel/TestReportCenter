<?php use_helper('Date') ?>

<div class="page_content">

	<h1 class="clearfix report-title"><?php echo "(".$currentSession["id"].") ".$currentSession["name"]; ?></h1>

	<div class="report-backlink">
		<a href="<?php echo url_for("build_session", array("build" => $currentBuild["build_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"])); ?>">
			      Click here
			    </a>
			       to view this message in your browser or handheld device
			</div>
			<br></br>

			<div class="report_head">

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
					<dd class="status"><?php echo Labeler::getTestSessionStatusLabel($currentSession["status"]); ?></dd>
				</dl> <!-- /meta_info -->
	</div> <!-- /report_head -->

	<h2 id="test_objective">
		Test Objective
		<span class="heading_actions"></span>
	</h2>
	<div class="editcontent">
		<?php echo ($currentSession["test_objective"] != "" || $currentSession["test_objective"] != null) ? $currentSession["test_objective"] : "No objective filled yet"; ?>
	</div> <!-- /editcontent -->

	<h2 id="environment" style="margin-top: 20px">
		Test Environment
		<span class="heading_actions"></span>
	</h2>
	<div class="editcontent" style="margin-top: 20px">
		<p><strong>Name: </strong><?php echo $currentEnvironment["name"]; ?></p>
		<p><strong>Description: </strong><?php echo ($currentEnvironment["description"] != "" || $currentEnvironment["description"] != null) ? $currentEnvironment["description"] : "No description filled yet"; ?></p>
		<p><strong>CPU: </strong><?php echo ($currentEnvironment["cpu"] != "" || $currentEnvironment["cpu"] != null) ? $currentEnvironment["cpu"] : ""; ?></p>
		<p><strong>Board: </strong><?php echo ($currentEnvironment["board"] != "" || $currentEnvironment["board"] != null) ? $currentEnvironment["board"] : ""; ?></p>
		<p><strong>GPU: </strong><?php echo ($currentEnvironment["gpu"] != "" || $currentEnvironment["gpu"] != null) ? $currentEnvironment["gpu"] : ""; ?></p>
		<p><strong>Other hw: </strong><?php echo ($currentEnvironment["other_hardware"] != "" || $currentEnvironment["other_hardware"] != null) ? $currentEnvironment["other_hardware"] : ""; ?></p>
	</div> <!-- /editcontent -->

	<h2 id="build_image">
		Build (image)
		<span class="heading_actions"></span>
	</h2>
	<div class="editcontent" style="margin-top: 20px">
		<p><strong>Name: </strong><?php echo $currentImage["name"]; ?></p>
		<p><strong>Description: </strong><?php echo ($currentImage["description"] != "" || $currentImage["description"] != null) ? $currentImage["description"] : "No description filled yet"; ?></p>
		<p><strong>Operating system: </strong><?php echo $currentImage["os"]; ?></p>
		<p><strong>Distribution: </strong><?php echo $currentImage["distribution"]; ?></p>
		<p><strong>Version: </strong><?php echo $currentImage["version"]; ?></p>
		<p><strong>Kernel: </strong><?php echo ($currentImage["kernel"] != "" || $currentImage["kernel"] != null) ? $currentImage["kernel"] : ""; ?></p>
		<p><strong>Architecture: </strong><?php echo $currentImage["architecture"]; ?></p>
		<p><strong>Other fw: </strong><?php echo ($currentImage["other_fw"] != "" || $currentImage["other_fw"] != null) ? $currentImage["other_fw"] : ""; ?></p>
		<p><strong>Binary link: </strong><?php if($currentImage["binary_link"] != "" || $currentImage["binary_link"] != null): ?><a href="<?php echo $currentImage["binary_link"]; ?>" title=""><?php echo $currentImage["binary_link"]; ?></a><?php endif; ?></p>
		<p><strong>Source link: </strong><?php if($currentImage["source_link"] != "" || $currentImage["source_link"] != null): ?><a href="<?php echo $currentImage["source_link"]; ?>" title=""><?php echo $currentImage["source_link"]; ?></a><?php endif; ?></p>
	</div> <!-- /editcontent -->

	<h2 id="qa_summary">
		Quality Summary
		<span class="heading_actions"></span>
	</h2>
	<div class="editcontent">
		<?php echo ($currentSession["qa_summary"] != "" || $currentSession["qa_summary"] != null) ? $currentSession["qa_summary"] : "No quality summary filled yet"; ?>
	</div> <!-- /editcontent -->

	<h2 id="test_results">
		Test Results
		<span class="heading_actions"></span>
	</h2>
	<div class="section emphasized_section">
		<div class="container">

			<h3 class="first">Result Summary</h3>
			<div class="wrap"> <?php
				$totalResultsPassed = $currentSummaryNumbers["pass"];
				$totalResultsFailed = $currentSummaryNumbers["fail"];
				$totalResultsBlocked = $currentSummaryNumbers["block"];
				$totalTestResults = $currentSummaryNumbers["total"];

				$runRate = round(($totalResultsPassed + $totalResultsFailed) / $totalTestResults * 100);
				$totalPassRate = round($totalResultsPassed / $totalTestResults * 100);
				$executedPassRate = round($totalResultsPassed / ($totalResultsPassed + $totalResultsFailed) * 100);

				if(count($previousSummaryNumbers) >= 1)
				{
					$previousTotalResultsPassed = $previousSummaryNumbers[0]["pass"];
					$previousTotalResultsFailed = $previousSummaryNumbers[0]["fail"];
					$previousTotalResultsBlocked = $previousSummaryNumbers[0]["block"];
					$previousTotalTestResults = $previousSummaryNumbers[0]["total"];

					$previousRunRate = round(($previousTotalResultsPassed + $previousTotalResultsFailed) / $previousTotalTestResults * 100);
					$previousTotalPassRate = round($previousTotalResultsPassed / $previousTotalTestResults * 100);
					$previousExecutedPassRate = round($previousTotalResultsPassed / ($previousTotalResultsPassed + $previousTotalResultsFailed) * 100);
				}
				else
				{
					$previousTotalResultsPassed = 0;
					$previousTotalResultsFailed = 0;
					$previousTotalResultsBlocked = 0;
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
											var g = new Bluff.<?php echo sfConfig::get('app_barchart_global_history'); ?>('summary_graph_canvas', '410x210');
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

			<table id="test_results_by_feature" class="feature_detailed_results" style="display: table">
				<thead class="even">
					<tr>
						<th class="th_feature">Feature</th>
						<th class="th_total">Total</th>
						<th class="th_passed">Passed</th>
						<th class="th_failed">Failed</th>
						<th class="th_not_testable">Blocked</th>
						<th class="th_pass_rate">Pass%</th>
						<th class="th_graph">&nbsp;</th>
						<th class="th_notes">Comments</th>
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
							$totalResults = $feature["total"];

							$percentage = round($totalPassed / $totalResults * 100, 2);

							if($percentage < 50)
								$color = "red";
							else if($percentage < 90)
								$color = "yellow";
							else
								$color = "green";

							$proportionalSize = $totalResults / $totalFeaturesResults * 100;
						?>

							<td><?php echo $feature["label"]; ?></td>
							<td class="total"><?php echo $totalResults; ?></td>
							<td class="pass"><?php echo $totalPassed; ?></td>
							<td class="fail"><?php echo $totalFailed; ?></td>
							<td class="na"><?php echo $totalBlocked; ?></td>
							<td class="rate"><?php echo $percentage; ?>%</td>
							<td>
								<div class="htmlgraph">
									<div class="passed" style="width: <?php echo round(($totalPassed / $totalResults * 100) * $proportionalSize / 100); ?>%" title="<?php echo $totalPassed; ?> passed">&nbsp;</div>
									<div class="failed" style="width: <?php echo round(($totalFailed / $totalResults * 100) * $proportionalSize / 100); ?>%" title="<?php echo $totalFailed; ?> failed">&nbsp;</div>
									<div class="na" style="width: <?php echo round(($totalBlocked / $totalResults * 100) * $proportionalSize / 100); ?>%" title="<?php echo $totalBlocked; ?> blocked">&nbsp;</div>
								</div> <!-- /htmlgraph -->
							</td>
							<td class="feature_record_notes">
								<div class="content"></div>
							</td>
							<td class="feature_record_grading">
								<span class="content grading_<?php echo $color; ?>">&nbsp;&nbsp;</span>
							</td>
						</tr> <?php
					} ?>
				</tbody>
			</table>

		</div> <!-- /container -->
	</div> <!-- /section -->

	<div style="display: none"></div>
	<script src="" type="text/javascript"></script>

	<?php if(count($currentSession["results"]) > 0): ?>
		<h2 id="detailed_results">
			Detailed Test Results
			<small>
				<a href="<?php echo url_for("export_report", array("id" => $currentSession["id"])); ?>" title="Download as CSV">Download as CSV</a>
			</small>
			<span class="heading_actions"></span>
		</h2>

		<table class="detailed_results" id="detailed_functional_test_results" style="display: table;">
			<thead>
				<tr>
					<th id="th_test_case" colspan="2">
					</th>
					<th id="th_result">Result</th>
					<th id="th_notes">Notes</th>
				</tr>
			</thead>

			<?php $previousFeatureKey = ""; ?>
			<?php foreach($currentSession["results"] as $result): ?>
				<?php $featureKey = MiscUtils::slugify($result["label"]); ?>

				<?php if($featureKey != $previousFeatureKey): ?>
					</tbody>

					<tbody>
						<tr id="<?php echo MiscUtils::slugify($result["label"]); ?>" class="feature_name"> <?php
							$feature = $currentSession["features"]->getRaw($featureKey);
							$totalPassed = $feature["pass"]; ?>

							<td colspan="4">
								<?php echo $result["label"]; ?>
								<a class="see_all_toggle" href="#">+ see <?php echo $totalPassed; ?> passing tests</a>
							</td>
						</tr>
					</tbody>

					<tbody>
				<?php endif; ?>

				<?php $previousFeatureKey = $featureKey; ?>
					<tr class="testcase result_<?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?>" >
						<td class="testcase_case_id"><?php echo $result["name"]; ?></td>
						<td class="testcase_name"><?php echo $result["complement"]; ?></td>
						<td class="testcase_result <?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?>">
							<span class="content"><?php echo ucfirst(str_replace("_", " ", Labeler::decisionToText($result["decision_criteria_id"]))); ?></span>
						</td>
						<td class="testcase_notes"><div class="content"><?php echo $result["comment"]; ?></div></td>
					</tr>
			<?php endforeach; ?>
		</table>

	<?php endif; ?>

	<?php if(count($currentSession["measures"]) > 0): ?>
		<h2 id="detailed_nft_results">
			Non-functional Test Results
			<span class="heading_actions"></span>
		</h2>

		<table class="non-functional_results detailed_results" style="display: table;">
			<thead>
				<tr>
					<th id="th_test_case">
					</th>
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

					<tbody>
						<tr id="<?php echo MiscUtils::slugify($measure["label"]); ?>" class="feature_name"> <?php
							$feature = $currentSession["features"]->getRaw($featureKey);
							$totalPassed = $feature["pass"]; ?>

							<td colspan="8">
								<?php echo $measure["label"]; ?>
								<a class="see_all_toggle" href="#">+ see <?php echo $totalPassed; ?> passing tests</a>
							</td>
						</tr>
					</tbody>

					<tbody>
				<?php endif; ?>

				<?php $previousFeatureKey = $featureKey; ?>
					<tr class="testcase result_<?php echo Labeler::decisionToText($measure["decision_criteria_id"]); ?>" >
						<td class="testcase_name"><?php echo $measure["name"]; ?></td>
						<td class="testcase_measurement"><?php echo $measure["complement"]; ?></td>
						<td class="testcase_value">
							<a id="nft-trend-button-<?php echo $measure["id"]; ?>" class="nft_trend_button" title="See history">
							<?php echo $measure["measures"]["value"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["value"]["unit"]; ?></span>
							</a>
						</td>
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
						<td class="testcase_notes"><div class="content"><?php echo $measure["comment"]; ?></div></td>
					</tr>
			<?php endforeach; ?>
		</table>

	<?php endif; ?>

	<h2 id="issue_summary">
		Issue Summary
		<span class="heading_actions"></span>
	</h2>
	<div class="editcontent">
		<?php echo ($currentSession["issue_summary"] != "" || $currentSession["issue_summary"] != null) ? $currentSession["issue_summary"] : "No issue summary filled yet"; ?>
	</div>

	<h2 id="raw_result_files">Attachments
		<span class="heading_actions"></span>
	</h2>
	<div id="result_file_drag_drop_area">
		<ul class="file_list item_list attachment">
			<span class="file_list_ready">
			<?php if(isset($attachments) && count($attachments) > 0): ?>
				<?php $count = 0; ?>
				<?php foreach($attachments as $attachment): ?>
					<li id="attachment_<?php echo $count++; ?>">
						<a href="<?php echo $sf_request->getUriPrefix().$attachment["link"]; ?>" target="_blank" title=""><?php echo $attachment["name"]; ?></a>
					</li>
				<?php endforeach; ?>
			<?php else: ?>
				No attachments
			<?php endif; ?>
			</span>
		</ul>
	</div> <!-- /result_file_drag_drop_area -->

	<h2 id="raw_result_files">Original Result Files
		<span class="heading_actions"></span>
	</h2>
	<div id="result_file_drag_drop_area">
		<ul class="file_list item_list attachment">
			<span class="file_list_ready">
			<?php if(isset($resultFiles) && count($resultFiles) > 0): ?>
				<?php $count = 0; ?>
				<?php foreach($resultFiles as $resultFile): ?>
					<li id="result_file_<?php echo $count++; ?>">
						<a href="<?php echo $sf_request->getUriPrefix().$resultFile["link"]; ?>" target="_blank" title=""><?php echo $resultFile["name"]; ?></a>
					</li>
				<?php endforeach; ?>
			<?php else: ?>
				No result files
			<?php endif; ?>
			</span>
		</ul>
	</div> <!-- /result_file_drag_drop_area -->
</div> <!-- page_content -->
<script>
	function simple_confirmation(message, url)
	{
		if(confirm(message))
		{
			window.location = url;
		}
	}
</script>