<?php use_helper('Date') ?>

<div id="page">
	<div id="index_page">
		<div id="breadcrumb">
			<li><a href="<?php echo url_for("project_reports", array("project" => $currentProject["name_slug"], "filter" => "recent")); ?>" title="Home">Home</a></li>
			<li>> <a href="<?php echo url_for("product_reports", array("project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"])); ?>" title="<?php echo $currentProduct["name"]; ?>"><?php echo $currentProduct["name"]; ?></a></li>
			<li>> <a href="<?php echo url_for("environment_reports", array("project" => $currentProject["name_slug"], "product" => $currentProduct["name_slug"], "environment" => $currentEnvironment["name_slug"])); ?>" title="<?php echo $currentEnvironment["name"]; ?>"><?php echo $currentEnvironment["name"]; ?></a></li>
			<li>> <?php echo $currentImage["name"]; ?></li>
		</div> <!-- /breadcrumb -->
		<h1>
			<em> <?php echo $lastSessions[1]['name'] ?> vs. <?php echo $lastSessions[0]['name'] ?> </em>
		</h1>

		<div class="clearfix">
			<h2></h2>

			<div class="result_caption big_number">
				<h3>Progress</h3>
				<strong id="changed_to_pass" class="pass"><?php echo $progressCount; ?>
				</strong>
				<div class="result_drilldown">
					<span class="pass changed_result changed_from_fail"><?php echo $failToPassCount; ?>
					</span> <br /> <span class="pass changed_result changed_from_na"><?php echo $naToPassCount; ?>
					</span>
				</div>
				<!-- /result_drilldown -->
				<p>
					Existing tests<br /> <em>changed to fixed</em>
				</p>
			</div>
			<!-- /result_caption -->

			<div class="result_caption big_number">
				<h3>Regression</h3>
				<strong id="changed_from_pass" class="fail"><?php echo $regressionCount; ?>
				</strong>
				<div class="result_drilldown">
					<span class="fail changed_result changed_from_pass"><?php echo $passToFailCount; ?>
					</span> <br /> <span class="na changed_result changed_from_pass"><?php echo $passToNaCount; ?>
					</span>
				</div>
				<!-- /result_drilldown -->
				<p>
					Existing tests<br /> <em>not passing anymore</em>
				</p>
			</div>
			<!-- /result_caption -->

			<div class="new_tests result_caption">
				<div class="result_caption_header">
					<strong>New tests:</strong>
				</div>
				<div class="result_caption medium_number">
					<strong id="new_passing" class="pass"><?php echo $newPassCount; ?>
					</strong>Passing
				</div>
				<div class="result_caption medium_number">
					<strong id="new_failing" class="fail"><?php echo $newFailCount; ?>
					</strong>Failing
				</div>
				<div class="result_caption medium_number">
					<strong id="new_na"><?php echo $newNaCount; ?> </strong>Block
				</div>
			</div>
			<!-- /new_tests -->
		</div>
		<!-- /clearfix -->

		<table id="tableOfResults" class="detailed_results" id="compare_details" style="display: table;">
			<thead>
				<tr>
					<th id="th_test_case">
						<span class="sort">
						    <a id="detailed_case_see_none" class="see_only_failed_button sort_btn non_nft_button active" href="javascript:;" onClick="hideShowAll('tableOfResults', '1', 'hide');" title="Changed">Changed</a>
							<a id="detailed_case_see_all" class="see_all_button sort_btn non_nft_button" href="javascript:; " onClick="hideShowAll('tableOfResults', '0', 'hide');" title="See all">See all</a>
					    </span>
				    </th>
				    <th class="column_head_1">
				      <?php echo format_datetime($lastSessions[1]["created_at"], "y-MM-dd HH:mm"); ?>
				    </th>
				    <th class="column_head_separate"></th><th class="column_head_1">
				      <?php echo format_datetime($lastSessions[0]["created_at"], "y-MM-dd HH:mm"); ?>
				    </th>
			    </tr>
		    </thead>

			<?php $previousFeatureKey = ""; ?>
		    <?php $line = 0; ?>
			<?php foreach($currentSession["results"] as $resultKey => $result): ?>
				<?php $featureKey = MiscUtils::slugify($result["label"]); ?>

				<?php if($featureKey != $previousFeatureKey): ?>
				<?php $line = 0; ?>
					</tbody>

					<tbody id="<?php echo $featureKey; ?>">
						<tr id="feature-<?php echo $featureKey; ?>" class="feature_name"> <?php
							$feature = $currentSession["features"]->getRaw($featureKey);?>
							<?php if(isset($unchangedFeatures[$result["label"]])): ?>
							<?php $totalUnchanged = $unchangedFeatures[$result["label"]]; ?>
							<?php else: ?>
							<?php $totalUnchanged = 0; ?>
							<?php endif; ?>

							<td colspan="4">
								<?php echo $result["label"]; ?>
								<a class="see_all_toggle" href="javascript:;" onClick="hideShowFeature('<?php echo $featureKey; ?>', '2', 'hide');">Hide/show <?php echo $totalUnchanged; ?> unchanged tests</a>
    						</td>
						</tr>
				<?php endif; ?>

				<?php $differentResultsFlag = false; ?>
				<?php $previousFeatureKey = $featureKey; ?>

					<?php if(array_key_exists($resultKey, $previousSession->getRaw("results"))): ?>
						<?php $previousResult = $previousSession->getRaw("results"); ?>
						<?php if($previousResult[$resultKey]["decision_criteria_id"] != $result["decision_criteria_id"]): ?>
							<?php $differentResultsFlag = true; ?>
						<?php endif; ?>
						<tr <?php if(!$differentResultsFlag): ?>id="result-<?php echo $result["id"]; ?>"<?php else: ?>id="test_case_<?php echo $line; ?>"<?php endif; ?> class="testcase has_changes <?php echo ($line % 2 == 0) ? "odd" : "even"; ?> <?php if(!$differentResultsFlag) echo "hide"; ?>">
							<td class="testcase_name"><?php echo $result["name"]; ?></td>
							<td class="testcase_result <?php echo Labeler::decisionToText($previousResult[$resultKey]["decision_criteria_id"]); ?> column_0"><?php echo ucfirst(Labeler::decisionToText($previousResult[$resultKey]["decision_criteria_id"])); ?></td>
					<?php else: ?>
						<tr id="test_case_<?php echo $line; ?>" class="testcase has_changes <?php echo ($line % 2 == 0) ? "odd" : "even"; ?>" >
							<td class="testcase_name"><?php echo $result["name"]; ?></td>
							<td class="testcase_result column_0">-</td>
					<?php endif; ?>

					<td class="change_indicator <?php echo ($differentResultsFlag) ? "changed_result" : ""; ?>"></td>
					<td class="testcase_result <?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?> column_1">
						<?php echo ucfirst(str_replace("_", " ", Labeler::decisionToText($result["decision_criteria_id"]))); ?>
					</td>
				</tr>
				<?php $line++; ?>
			<?php endforeach; ?>
	    </table>

	</div>
	<!-- /index_page -->
</div>
<!-- /page -->

<script>
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
