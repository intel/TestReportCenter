<?php use_helper('Date') ?>

<?php include_partial("reports/navigation", array("projects" => $projects, "currentProject" => $currentProject)); ?>

<div id="page">
	<div id="breadcrumb">
		<li><a href="<?php echo url_for("project_reports", array("project" => $currentProject["name_slug"], "filter" => "recent")); ?>" title="Home">Home</a></li>
		<li>> <a href="<?php echo url_for("product_reports", array("project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"])); ?>" title="<?php echo $currentProduct["name"]; ?>"><?php echo $currentProduct["name"]; ?></a></li>
		<li>> <a href="<?php echo url_for("environment_reports", array("project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"])); ?>" title="<?php echo $currentEnvironment["name"]; ?>"><?php echo $currentEnvironment["name"]; ?></a></li>
		<li>> <a href="<?php echo url_for("image_reports", array("project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"])); ?>" title="<?php echo $currentImage["name"]; ?>"><?php echo $currentImage["name"]; ?></a></li>
		<li>> <?php echo "(".$currentSession["id"].") ".$currentSession["name"]; ?></li>
	</div> <!-- /breadcrumb -->

	<div class="page_content">
		<div class="notification">Edit the report information</div> <!-- /notification -->

		<div class="field last">
			* Mandatory field
		</div>

		<form method="post" enctype="multipart/form-data" action="<?php echo url_for($currentRoute, array("project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"])) ?>">
			<div id="wizard_actions">
				<input class="big_btn cancel" type="button" value="Cancel" onclick="location.href='<?php echo url_for($cancelRoute, array("project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"])); ?>'">
				<input class="big_btn submit" type="submit" value="<?php echo $submitButton; ?>" />
			</div>

			<?php echo $form["project_group_id"]->render(array("value" => $projectGroupId)); ?>
			<?php echo $form["id"]->render(); ?>
			<?php echo $form["published"]->render(array("value" => 1)); ?>
			<?php echo $form["_csrf_token"]->render(); ?>

			<h2>Title
				<span class="heading_actions"><a href="#top">Back to top</a></span>
			</h2>

			<div class="editable_title" style="padding-bottom: 10px">
				<?php if($mandatoryBuildId): ?>
					<?php echo $form["build_id"]->renderLabel(null, array("class" => "mandatory")); ?>
				<?php else: ?>
					<?php echo $form["build_id"]->renderLabel(); ?>
				<?php endif; ?>
				<?php echo $form["build_id"]->render(array("value" => $currentSession["build_id"], "class" => "text", "size" => 50)); ?>
				<div class="formError"><?php echo $form["build_id"]->renderError(); ?></div>
			</div> <!-- /editable_title -->

			<div class="editable_title" style="padding-bottom: 10px">
				<?php if($mandatoryTestset): ?>
					<?php echo $form["testset"]->renderLabel(null, array("class" => "mandatory")); ?>
				<?php else: ?>
					<?php echo $form["testset"]->renderLabel(); ?>
				<?php endif; ?>
				<?php echo $form["testset"]->render(array("value" => $currentSession["testset"], "class" => "text", "size" => 50)); ?>
				<div class="formError"><?php echo $form["testset"]->renderError(); ?></div>
			</div> <!-- /editable_title -->

			<div class="editable_title" style="padding-bottom: 10px">
				<?php echo $form["name"]->renderLabel(null, array("class" => "mandatory")); ?>
				<?php echo $form["name"]->render(array("value" => $currentSession["name"], "class" => "text", "size" => 50)); ?>
				<div class="formError"><?php echo $form["name"]->renderError(); ?></div>
			</div> <!-- /editable_title -->

			<div class="editable_title" style="padding-bottom: 20px">
				<?php echo $form["status"]->renderLabel(); ?>
				<?php echo $form["status"]->render(); ?>
			</div> <!-- /editable_title -->

			<h2>Contents
				<span class="heading_actions"><a href="#top">Back to top</a></span>
			</h2>
			<div class="editable_text" style="padding-bottom: 20px">
				<?php echo $form["author_name"]->renderLabel(); ?>
				<?php echo $form["author_name"]->render(array("class" => "text", "size" => 50, "readonly" => true, "value" => $author["first_name"]." ".$author["last_name"])); ?>
				<?php echo $form["user_id"]->render(); ?>
				<div class="formError"><?php echo $form["user_id"]->renderError(); ?></div>
			</div> <!-- /editable_text -->

			<div class="editable_text" style="padding-bottom: 20px">
				<?php echo $form["created_at"]->renderLabel(null, array("class" => "mandatory")); ?>
				<?php echo $form["created_at"]->render(array("class" => "text", "size" => 50, "value" => $currentSession["created_at"])); ?>
				<div class="formError"><?php echo $form["created_at"]->renderError(); ?></div>
			</div> <!-- /editable_text -->

			<div class="editable_text" style="padding-bottom: 20px">
				<?php echo $form["editor_name"]->renderLabel(); ?>
				<?php echo $form["editor_name"]->render(array("class" => "text", "size" => 50, "readonly" => true, "value" => $sf_user->getGuardUser()->getFirstName()." ".$sf_user->getGuardUser()->getLastName())); ?>
				<?php echo $form["editor_id"]->render(array("value" => $sf_user->getGuardUser()->getId())); ?>
				<div class="formError"><?php echo $form["user_id"]->renderError(); ?></div>
			</div> <!-- /editable_text -->

			<div class="editable_text" style="padding-bottom: 20px">
				<?php echo $form["updated_at"]->renderLabel(); ?>
				<?php echo $form["updated_at"]->render(array("class" => "text", "size" => 50, "readonly" => true, "value" => date("Y-m-d H:i:s"))); ?>
				<div class="formError"><?php echo $form["updated_at"]->renderError(); ?></div>
			</div> <!-- /editable_text -->

			<div class="editable_text" style="padding-bottom: 20px">
				<?php echo $form["project"]->renderLabel(null, array("class" => "mandatory")); ?>
				<?php echo $form["project"]->render(array('onClick' => "selectProducts(this, ".$projectGroupId.")")); ?>
				<div class="formError"><?php echo $form["project"]->renderError(); ?></div>
			</div> <!-- /editable_text -->

			<div class="editable_text" style="padding-bottom: 20px">
				<?php echo $form["product"]->renderLabel(null, array("class" => "mandatory")); ?>
				<div id="productsList">
				    <?php echo $form["product"]->render(); ?>
				    <div class="formError"><?php echo $form["product"]->renderError(); ?></div>
				</div>
				<img id="loader" src="<?php echo image_path('ajax-loader.gif'); ?>" style="vertical-align: middle; display: none" />
			</div> <!-- /editable_text -->

			<h2>Test Objective
				<span class="heading_actions"><a href="#top">Back to top</a></span>
			</h2>
			<div class="editable_area" style="padding-bottom: 20px">
				<?php echo $form["test_objective"]->renderLabel(); ?>
				<?php echo $form["test_objective"]->render(array("rows" => 9)); ?>
				<div class="help">
					<strong>Markup reference</strong>
					<pre>== Header ==
=== Subheader ===
''italics''
'''bold'''
* bulleted list
[[http://foo.bar/ Link]]
[[KEY-1234]] (JIRA issue link)
					</pre>
				</div> <!-- /help -->
				<div class="formError"><?php echo $form["test_objective"]->renderError(); ?></div>
			</div> <!-- /editable_area -->

			<div>
				<h2>Test environment
					<span class="heading_actions"><a href="#top">Back to top</a></span>
				</h2>

				<div class="editable_text" style="margin-bottom: 10px;">
					<?php echo $form["environmentForm"]["name"]->renderLabel(null, array("class" => "mandatory")); ?>
					<?php echo $form["environmentForm"]["name"]->render(array("class" => "text", "size" => "30")); ?>
					<div class="formError"><?php echo $form["environmentForm"]["name"]->renderError(); ?></div>
				</div>

				<div id="additional_environment_fields">
					<div class="editable_area" style="margin-bottom: 10px;">
						<?php echo $form["environmentForm"]["description"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["description"]->render(); ?>
						<div class="formError"><?php echo $form["environmentForm"]["description"]->renderError(); ?></div>
					</div>

					<div class="editable_text" style="margin-bottom: 10px;">
						<?php echo $form["environmentForm"]["cpu"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["cpu"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["environmentForm"]["cpu"]->renderError(); ?></div>
					</div>

					<div class="editable_text" style="margin-bottom: 10px;">
						<?php echo $form["environmentForm"]["board"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["board"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["environmentForm"]["board"]->renderError(); ?></div>
					</div>

					<div class="editable_text" style="margin-bottom: 10px;">
						<?php echo $form["environmentForm"]["gpu"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["gpu"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["environmentForm"]["gpu"]->renderError(); ?></div>
					</div>

					<div class="editable_area">
						<?php echo $form["environmentForm"]["other_hardware"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["other_hardware"]->render(); ?>
						<div class="formError"><?php echo $form["environmentForm"]["other_hardware"]->renderError(); ?></div>
					</div>
				</div> <!-- /additional_environment_fields -->
			</div> <!-- /field -->

			<div>
				<h2>Image (build)
					<span class="heading_actions"><a href="#top">Back to top</a></span>
				</h2>

				<div class="editable_text" style="margin-bottom: 10px;">
					<?php echo $form["imageForm"]["name"]->renderLabel(null, array("class" => "mandatory")); ?>
					<?php echo $form["imageForm"]["name"]->render(array("class" => "text", "size" => "30")); ?>
					<div class="formError"><?php echo $form["imageForm"]["name"]->renderError(); ?></div>
				</div>

				<div id="additional_image_fields">
					<div class="editable_area" style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["description"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["description"]->render(); ?>
						<div class="formError"><?php echo $form["imageForm"]["description"]->renderError(); ?></div>
					</div>

					<div class="editable_text" style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["os"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["os"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["os"]->renderError(); ?></div>
					</div>

					<div class="editable_text" style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["distribution"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["distribution"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["distribution"]->renderError(); ?></div>
					</div>

					<div class="editable_text" style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["version"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["version"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["version"]->renderError(); ?></div>
					</div>

					<div class="editable_text" style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["kernel"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["kernel"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["kernel"]->renderError(); ?></div>
					</div>

					<div class="editable_text" style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["architecture"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["architecture"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["architecture"]->renderError(); ?></div>
					</div>

					<div class="editable_area" style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["other_fw"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["other_fw"]->render(); ?>
						<div class="formError"><?php echo $form["imageForm"]["other_fw"]->renderError(); ?></div>
					</div>

					<div class="editable_text" style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["binary_link"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["binary_link"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["binary_link"]->renderError(); ?></div>
					</div>

					<div class="editable_text">
						<?php echo $form["imageForm"]["source_link"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["source_link"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["source_link"]->renderError(); ?></div>
					</div>
				</div> <!-- /additional_environment_fields -->
			</div> <!-- /field -->

			<h2>Environment Summary
				<span class="heading_actions"><a href="#top">Back to top</a></span>
			</h2>
			<div class="editable_area" style="padding-bottom: 20px">
				<?php echo $form["notes"]->renderLabel(); ?>
				<?php echo $form["notes"]->render(array("rows" => 9)); ?>
				<div class="help">
					<strong>Markup reference</strong>
					<pre>== Header ==
=== Subheader ===
''italics''
'''bold'''
* bulleted list
[[http://foo.bar/ Link]]
[[KEY-1234]] (JIRA issue link)
					</pre>
				</div> <!-- /help -->
				<div class="formError"><?php echo $form["notes"]->renderError(); ?></div>
			</div> <!-- /editable_area -->

			<h2>Quality Summary
				<span class="heading_actions"><a href="#top">Back to top</a></span>
			</h2>
			<div class="editable_area" style="padding-bottom: 20px">
				<?php echo $form["qa_summary"]->renderLabel(); ?>
				<?php echo $form["qa_summary"]->render(array("rows" => 9)); ?>
				<div class="help">
					<strong>Markup reference</strong>
					<pre>== Header ==
=== Subheader ===
''italics''
'''bold'''
* bulleted list
[[http://foo.bar/ Link]]
[[KEY-1234]] (JIRA issue link)
					</pre>
				</div> <!-- /help -->
				<div class="formError"><?php echo $form["qa_summary"]->renderError(); ?></div>
			</div> <!-- /editable_area -->

			<?php if(count($currentSession["results"]) > 0): ?>
				<h2>Detailed Test Results
					<span class="heading_actions"><a href="#top">Back to top</a></span>
				</h2>
				<table class="detailed_results" id="detailed_functional_test_results">
					<thead>
						<tr>
							<th colspan="5">
								<span class="sort">
									<a id="detailed_case" class="see_only_failed_button sort_btn non_nft_button active" href="#" title="See only failed">See only failed</a>
									<a id="detailed_case" class="see_all_button sort_btn non_nft_button" href="#" title="See all">See all</a>
								</span>
							</th>
						</tr>

						<tr>
							<th id="th_test_case">Name</th>
							<th id="th_test_description">Description</th>
							<th id="th_result">Result</th>
							<th id="">Bugs</th>
							<th id="th_notes">Notes</th>
						</tr>
					</thead>

					<?php $previousFeatureKey = ""; ?>
					<?php foreach($currentSession["results"] as $result): ?>
						<?php $featureKey = MiscUtils::slugify($result["label"]); ?>

						<?php if($featureKey != $previousFeatureKey): ?>
							</tbody>

							<tbody>
								<tr id="<?php echo $featureKey; ?>" class="feature_name"> <?php
									$feature = $currentSession["features"]->getRaw($featureKey);
									$totalPassed = $feature["pass"]; ?>

									<td colspan="5">
										<?php echo $feature["label"]; ?>
										<a class="see_all_toggle" href="#">+ see <?php echo $totalPassed; ?> passing tests</a>
									</td>
								</tr>
							</tbody>

							<tbody>
						<?php endif; ?>

						<?php $previousFeatureKey = $featureKey; ?>
							<tr id="js_result_<?php echo $result["id"]; ?>" class="testcase result_<?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?>" <?php if(Labeler::decisionToText($result["decision_criteria_id"]) == "pass") echo "style='display: none'"; ?>>
								<td class="testcase_case_id">
									<a class="edit_list_item toggle_testcase" href="javascript:;" onclick="updateResult('js_result_<?php echo $result["id"]; ?>', '<?php echo url_for("update_result", array("id" => $result["id"])); ?>')" title="Edit">Edit</a>
									<a class="remove_list_item toggle_testcase" href="javascript:;" onclick="clear_html('js_result_<?php echo $result["id"]; ?>');delete_result('<?php echo url_for('delete_result', array("id" => $result["id"])); ?>')" title="Remove">Remove</a>

									<?php echo $result["name"]; ?>
								</td>
								<td class="testcase_name"><?php echo $result["complement"]; ?></td>
								<td class="testcase_result <?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?>">
									<span class="content"><?php echo ucfirst(str_replace("_", " ", Labeler::decisionToText($result["decision_criteria_id"]))); ?></span>
								</td>
								<td class=""><?php echo MiscUtils::formatWikimarkups($result["bugs"]); ?></td>
								<td class="testcase_notes"><div class="content"><?php echo nl2br(MiscUtils::formatWikimarkups($result["comment"])); ?></div></td>
							</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>

			<?php if(count($currentSession["measures"]) > 0): ?>
				<h2 id="detailed_nft_results">Non-functional Test Results
					<span class="heading_actions"><a href="#top">Back to top</a></span>
				</h2>

				<table class="non-functional_results detailed_results">
					<thead>
						<tr>
							<th id="th_nft_test_case">Name</th>
							<th class="th_name">Description</th>
							<th class="th_measured">Value</th>
							<th class="th_target">Target</th>
							<th class="th_limit">Fail limit</th>
							<th class="th_to_target">% of target</th>
							<th class="th_result">Result</th>
							<th class="">Bugs</th>
							<th id="th_notes">Notes</th>
						</tr>
					</thead>

					<?php $previousFeatureKey = ""; ?>
					<?php foreach($currentSession["measures"] as $measure): ?>
						<?php $slug = MiscUtils::slugify($measure["label"]); ?>
							<?php $featureKey = MiscUtils::slugify($measure["label"]); ?>

							<?php if($featureKey != $previousFeatureKey): ?>
								</tbody>

								<tbody>
									<tr id="<?php echo MiscUtils::slugify($measure["label"]); ?>" class="feature_name"> <?php
										$feature = $currentSession["features"]->getRaw($featureKey);
										$totalPassed = $feature["pass"]; ?>

										<td colspan="9">
											<?php echo $measure["label"]; ?>
											<a class="see_all_toggle" href="#">+ see <?php echo $totalPassed; ?> passing tests</a>
										</td>
									</tr>
								</tbody>

								<tbody>
							<?php endif; ?>

							<?php $previousFeatureKey = $featureKey; ?>

							<tr id="js_measure_<?php echo $measure["id"]; ?>" class="testcase result_<?php echo Labeler::decisionToText($measure["decision_criteria_id"]); ?>" <?php if(Labeler::decisionToText($measure["decision_criteria_id"]) == "pass") echo "style='display: none'"; ?>>
								<td class="testcase_name">
									<a class="edit_list_item toggle_testcase" href="javascript:;" onclick="updateResult('js_measure_<?php echo $measure["id"]; ?>', '<?php echo url_for("update_measure", array("id" => $measure["id"])); ?>')" title="Edit">Edit</a>
									<a class="remove_list_item toggle_testcase" href="javascript:;" onclick="clear_html('js_measure_<?php echo $measure["id"]; ?>');delete_result('<?php echo url_for('delete_measure', array("id" => $measure["id"])); ?>') " title="Remove">Remove</a>
									<?php echo $measure["name"]; ?>
								</td>
								<td class="testcase_measurement"><?php echo $measure["complement"]; ?></td>
								<td class="testcase_value"><?php echo $measure["measures"]["value"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["value"]["unit"]; ?></span></td>
								<td class="testcase_target"><?php echo $measure["measures"]["target"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["target"]["unit"]; ?></span></td>
								<td class="testcase_limit"><?php echo $measure["measures"]["limit"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["limit"]["unit"]; ?></span></td>
								<td class="testcase_to_target"><?php echo round($measure["measures"]["value"]["value"] / $measure["measures"]["target"]["value"] * 100, 1); ?> %</td>
								<td class="testcase_result <?php echo Labeler::decisionToText($measure["decision_criteria_id"]); ?>">
									<span class="content"><?php echo ucfirst(Labeler::decisionToText($measure["decision_criteria_id"])); ?></span>
								</td>
								<td class=""><?php echo MiscUtils::formatWikimarkups($measure["bugs"]); ?></td>
								<td class="testcase_notes"><div class="content"><?php echo nl2br(MiscUtils::formatWikimarkups($measure["comment"])); ?></div></td>
							</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>

			<h2>Issue Summary
				<span class="heading_actions"><a href="#top">Back to top</a></span>
			</h2>
			<div class="editable_area" style="padding-bottom: 20px">
				<?php echo $form["issue_summary"]->renderLabel(); ?>
				<?php echo $form["issue_summary"]->render(array("rows" => 9)); ?>
				<div class="help">
					<strong>Markup reference</strong>
					<pre>== Header ==
=== Subheader ===
''italics''
'''bold'''
* bulleted list
[[http://foo.bar/ Link]]
[[KEY-1234]] (JIRA issue link)
					</pre>
				</div> <!-- /help -->
				<div class="formError"><?php echo $form["issue_summary"]->renderError(); ?></div>
			</div> <!-- /editable_area -->

			<h2 id="raw_result_files">Attachments
				<span class="heading_actions"><a href="#top" title="Back to top">Back to top</a></span>
			</h2>
			<div id="result_file_drag_drop_area">
				<ul class="file_list item_list attachment">
					<span class="file_list_ready">
					<?php if(isset($attachments) && count($attachments) > 0): ?>
						<?php $count = 0; ?>
						<?php foreach($attachments as $attachment): ?>
							<li id="attachment_<?php echo ++$count; ?>">
								<a class="remove_list_item" title="Remove attachment" href="javascript:;" onclick="clear_html('attachment_<?php echo $count; ?>');delete_result('<?php echo url_for('delete_attachment', array("id" => $attachment["id"])); ?>')">Remove</a>
								<a href="<?php echo $sf_request->getUriPrefix().$sf_request->getRelativeUrlRoot().$attachment["link"]; ?>" target="_blank" title=""><?php echo $attachment["name"]; ?></a>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>

					<input style="margin: 10px 0;" type="file" id="uploadInput" value="" name="attachments[]" onChange="listUploads('uploadInput', 'uploadList')" multiple>
					<ul id="uploadList" class="file_list item_list attachment"><li></li></ul>
					</span>
				</ul>
			</div> <!-- /result_file_drag_drop_area -->

			<h2 id="raw_result_files">Original Result Files
				<span class="heading_actions"><a href="#top" title="Back to top">Back to top</a></span>
			</h2>
			<div id="result_file_drag_drop_area">
				<ul class="file_list item_list attachment">
					<span class="file_list_ready">
					<?php if(isset($resultFiles) && count($resultFiles) > 0): ?>
						<?php $count = 0; ?>
						<?php foreach($resultFiles as $resultFile): ?>
							<li id="result_file_<?php echo $count++; ?>">
								<a href="<?php echo $sf_request->getUriPrefix().$sf_request->getRelativeUrlRoot().$resultFile["link"]; ?>" target="_blank" title=""><?php echo $resultFile["name"]; ?></a>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>

					<input style="margin: 10px 0;" id="uploadInput2" type="file" value="" name="result_files[]" onChange="listUploads('uploadInput2', 'uploadList2')" multiple>
					<ul id="uploadList2" class="file_list item_list attachment"><li></li></ul>
					</span>
				</ul>
			</div> <!-- /result_file_drag_drop_area -->

			<div id="wizard_actions">
				<input class="big_btn cancel" type="button" value="Cancel" onclick="location.href='<?php echo url_for($cancelRoute, array("project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"], "image" => $currentImage["name_slug"], "id" => $currentSession["id"])); ?>'">
				<input class="big_btn submit" type="submit" value="<?php echo $submitButton; ?>" />
			</div>
		</form>
	</div> <!-- /page_content -->
</div> <!-- /page -->

<script>
	$(document).ready()
	{
		filterResults("tr.result_pass", "passing tests");
	}

	function clear_html(id)
	{
	    var myElement = document.getElementById(id);
	    myElement.innerHTML = "";
	}

	function delete_result(url)
	{
		$.post(url, {});
	}

	function listUploads(inputId, listId)
	{
		var input = document.getElementById(inputId);
		var ul = document.getElementById(listId);
		while (ul.hasChildNodes()) {
			ul.removeChild(ul.firstChild);
		}
		for (var i = 0; i < input.files.length; i++) {
			var li = document.createElement("li");
			li.innerHTML = input.files[i].name;
			ul.appendChild(li);
		}
		if(!ul.hasChildNodes()) {
			var li = document.createElement("li");
			li.innerHTML = 'No Files Selected';
			ul.appendChild(li);
		}
	}

	var popup = null;

	function updateResult(containerId, url)
	{
		popup = window.open(url);
	}

	function refreshResult(containerId, url)
	{
		$('#'+containerId).load(url, {}, function() {});
		popup.close();
	}

	function selectProducts(element, projectGroupId)
	{
	    var url = "<?php echo url_for("search_products", array("projectGroupId" => $projectGroupId, "projectId" => "0")); ?>";
	    var id = element.id.match(/[0-9]*$/);
	    url = url.replace(/[0-9]*$/g, '');

	    $('#loader').show();
	    $('#productsList').hide();
        $('#productsList').load(
	        url + id,
	        { 'projectGroupId': projectGroupId, 'productId': element.value },
	        function() { $('#loader').hide(); $('#productsList').show(); }
        );
	}
</script>
