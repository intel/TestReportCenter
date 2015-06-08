<?php use_helper('Date') ?>

<?php include_partial("reports/navigation", array("projects" => $projects, "currentProject" => $currentProject)); ?>

<div id="page">
	<div id="index_page">
		<div id="breadcrumb">
			<li><a href="<?php echo url_for("project_builds", array("build" => $currentBuild["build_slug"], "project" => $currentProject["name_slug"], "filter" => "recent")); ?>" title="Home">Home</a></li>
			<li>> <a href="<?php echo url_for("product_builds", array("build" => $currentBuild["build_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"])); ?>" title="<?php echo $currentProduct["name"]; ?>"><?php echo $currentProduct["name"]; ?></a></li>
			<li>> <?php echo $currentBuild["build_id"]; ?></a></li>
		</div> <!-- /breadcrumb -->

		<table class="filtered" id="report_filtered_navigation">
			<thead>
				<tr>
					<th class="filtered" scope="col">
						<?php echo $currentProduct["name"]." / ".$currentBuild["build_id"]; ?>
						<a class="rss" href="<?php echo url_for("rss_build", array("build" => $currentBuild["build_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"])); ?>" title="RSS feed"></a>
					</th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td class="filtered">
						<div class="chart_actions stack_chart">
							<div id="canvas_wrapper" style="width:700px; height: 280px">
								<div class="bluff_wrapper" style="position: relative; border: none; padding: 0px; width: 700px; height: 250px;">
									<canvas id="trend_graph_abs" width="700" height="250"></canvas>
									<script type="text/javascript">
									    <?php $nbHistograms = count($resultsNumbers); ?>

										function draw_graph(abs_rel)
										{
											var g = new Bluff.<?php echo sfConfig::get('app_barchart_global_history'); ?>('trend_graph_abs', '700x250');
											g.tooltips = true;

											trend_abs_passed = [<?php foreach($resultsNumbers as $data) echo $data["pass"].","; for($i=0; $i<($totalNumberOfHistograms-$nbHistograms); $i++) echo ","; ?>];
											trend_abs_failed = [<?php foreach($resultsNumbers as $data) echo $data["fail"].","; for($i=0; $i<($totalNumberOfHistograms-$nbHistograms); $i++) echo ","; ?>];
											trend_abs_na = [<?php foreach($resultsNumbers as $data) echo $data["block"].","; for($i=0; $i<($totalNumberOfHistograms-$nbHistograms); $i++) echo ","; ?>];
											trend_rel_passed = [<?php
												foreach($resultsNumbers as $data)
													if($data["pass"]+$data["fail"]+$data["block"] > 0)
														echo ($data["pass"]/($data["pass"]+$data["fail"]+$data["block"]) *100).",";
													else
														echo ",";
												for($i=0; $i<($totalNumberOfHistograms-$nbHistograms); $i++)
													echo ","; ?>];
											trend_rel_failed = [<?php
												foreach($resultsNumbers as $data)
													if($data["pass"]+$data["fail"]+$data["block"] > 0)
														echo ($data["fail"]/($data["pass"]+$data["fail"]+$data["block"]) *100).",";
													else
														echo ",";
												for($i=0; $i<($totalNumberOfHistograms-$nbHistograms); $i++)
													echo ","; ?>];
											trend_rel_na = [<?php
												foreach($resultsNumbers as $data)
													if($data["pass"]+$data["fail"]+$data["block"] > 0)
														echo ($data["block"]/($data["pass"]+$data["fail"]+$data["block"]) *100).",";
													else
														echo ",";
												for($i=0; $i<($totalNumberOfHistograms-$nbHistograms); $i++)
													echo ","; ?>];

											if(abs_rel == "abs")
											{
												trend_passed = trend_abs_passed;
												trend_failed = trend_abs_failed;
												trend_na = trend_abs_na;
											}
											else if(abs_rel == "rel")
											{
												trend_passed = trend_rel_passed;
												trend_failed = trend_rel_failed;
												trend_na = trend_rel_na;
											}

											g.set_theme({
												colors: ['#bcd483', '#f36c6c', '#ddd'],
												marker_color: '#aea9a9',
												font_color: '#6f6f6f',
												background_colors: ['#ffffff', '#ffffff']
											});

										    g.hide_title = true;
										    g.tooltips = true;
										    g.sort = false;
										    g.bar_spacing = 0.6;
										    g.marker_font_size = 11;
										    g.legend_font_size = 14;

											g.data("pass", trend_passed);
											g.data("fail", trend_failed);
											g.data("block", trend_na);

											g.labels = { <?php
												$i = 0;
												foreach($resultsNumbers as $data)
												{
													echo $i.": '".$data["name"]."', ";
													$i++;
												}

												for($j=0; $j<($totalNumberOfHistograms-$nbHistograms); $j++)
												{
													echo $i.": '', ";
													$i++;
												}

												?> };

											g.draw();

											for(var i=$(".bluff-text").size(); i>($(".bluff-text").size() - <?php echo ($nbHistograms); ?>); i--)
											{
												$($(".bluff-text")[i-1]).addClass("axis");
											}
										}

										draw_graph("abs");

										function draw_abs_graph()
										{
											$("#abs_button").addClass("inactive");
											$("#rel_button").removeClass("inactive");
											draw_graph("abs");
										}

										function draw_rel_graph()
										{
											$("#abs_button").removeClass("inactive");
											$("#rel_button").addClass("inactive");
											draw_graph("rel");
										}
									</script>
								</div> <!-- /bluff-wrapper -->
							</div> <!-- /canvas_wrapper -->

							<a id="abs_button" class="ui_btn inactive" onClick="javascript:draw_abs_graph()" title="Absolute values">Absolute values</a>
							<a id="rel_button" class="ui_btn" onClick="javascript:draw_rel_graph()" title="Relative %">Relative %</a>
							<a id="csv_report_link" href="<?php echo url_for("export_build", array("build" => $currentBuild["build_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"])); ?>" title="Download as CSV">Download as CSV</a>
						</div>

						<div class="index_month" id="reports_by_month">
							<?php $previousImage = ""; ?>
							<?php $rowCount = 0; ?>
							<?php foreach($sessionsForImages as $data): ?>
								<?php if($previousImage != $data["i_name"]): ?>
									<table class="month">
										<thead>
											<tr>
												<td class="index_month_title" colspan="3">
													<strong class="name"><?php echo $data["i_name"]; ?></strong>
												</td>
											</tr>
										</thead>

										<tbody class="reports">
								<?php endif; ?>
											<?php $rowCount++; ?>

											<tr class="<?php echo ($rowCount%2 == 0) ? "even" : "odd"; ?>">
												<td class="date"><span title="<?php echo Labeler::getTestSessionStatusLabel($data["status"]); ?>" class="icon_status <?php echo "status_".MiscUtils::slugify(Labeler::getTestSessionStatusLabel($data["status"]), '_'); ?>"></span><?php echo format_datetime($data["created_at"], "y-MM-dd HH:mm"); ?></td>
												<td class="report_name">
													<a href="<?php echo url_for("build_session", array("build" => $currentBuild["build_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $data["te_slug"], "image" => $data["i_slug"], "id" => $data["id"], "display" => "basic")); ?>" title="See basic report" class="shortcut_link">Basic</a>
													<a href="<?php echo url_for("build_session", array("build" => $currentBuild["build_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $data["te_slug"], "image" => $data["i_slug"], "id" => $data["id"], "display" => "detailed")); ?>" title="See detailed report" class="shortcut_link">Detailed</a>
													<a href="<?php echo url_for("build_session", array("build" => $currentBuild["build_slug"], "project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $data["te_slug"], "image" => $data["i_slug"], "id" => $data["id"], "display" => "history")); ?>" title="See history report" class="shortcut_link">History</a>

													<?php echo "(".$data["id"].") ".$data["name"]; ?> <?php
													if($data["project_release"] != null || $data["project_release"] != "")
													{
														echo " Release: ".$data["project_release"];
														if($data["project_milestone"] != null || $data["project_milestone"] != "")
															echo " Milestone: ".$data["project_milestone"];
													} ?>
													<a class="compare" href="javascript:;" onclick="addToComparison('<?php echo $data["id"]; ?>');" title="Compare report">Compare</a>
												</td>

												<td class="graph">
													<div class="htmlgraph"> <?php
														$totalPass = $data["pass"];
														$totalFail = $data["fail"];
														$totalBlock = $data["block"];
														$totalTests = $data["total"];

														$proportional = $totalTests / $totalResults * 100; ?>

														<div class="passed" style="width: <?php echo (($totalPass / $totalTests * 100) * $proportional / 100); ?>%" title="passed <?php echo $totalPass; ?>">&nbsp;</div>
														<div class="failed" style="width: <?php echo (($totalFail / $totalTests * 100) * $proportional / 100); ?>%" title="failed <?php echo $totalFail; ?>">&nbsp;</div>
														<div class="na" style="width: <?php echo (($totalBlock / $totalTests * 100) * $proportional / 100); ?>%" title="blocked <?php echo $totalBlock; ?>">&nbsp;</div>
													</div> <!-- /htmlgraph -->
												</td>
											</tr>

								<?php $previousImage = $data["i_name"]; ?>
							<?php endforeach; ?>
						</div> <!-- /reports_by_month -->
					</td>
				</tr>
			</tbody>
		</table>
	</div> <!-- /index_page -->
</div> <!-- /page -->
