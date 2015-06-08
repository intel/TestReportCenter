<form method="post" action="<?php echo url_for("update_measure", array("id" => $measure["id"])); ?>">
	<?php echo $form["_csrf_token"]->render(); ?>

	<table>
		<thead>
			<tr>
				<th id="th_test_case">Name</th>
				<th class="th_name">Measurement</th>
				<th class="th_measured">Value</th>
				<th class="th_target">Target</th>
				<th class="th_limit">Fail limit</th>
				<th class="th_to_target">% of target</th>
				<th class="th_result">Result</th>
				<th id="th_bugs">Bugs</th>
				<th id="th_notes">Notes</th>
			</tr>
		</thead>

		<tbody>
			<tr id="js_measure_<?php echo $measure["id"]; ?>" class="testcase result_<?php echo Labeler::decisionToText($measure["decision_criteria_id"]); ?>">
				<td class="testcase_name"><?php echo $measure["name"]; ?></td>
				<td class="testcase_measurement"><?php echo $measure["complement"]; ?></td>
				<td class="testcase_value"><?php echo $measure["measures"]["value"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["value"]["unit"]; ?></span></td>
				<td class="testcase_target"><?php echo $measure["measures"]["target"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["target"]["unit"]; ?></span></td>
				<td class="testcase_limit"><?php echo $measure["measures"]["limit"]["value"]; ?>&nbsp;<span class="unit"><?php echo $measure["measures"]["limit"]["unit"]; ?></span></td>
				<td class="testcase_to_target"><?php echo round($measure["measures"]["value"]["value"] / $measure["measures"]["target"]["value"] * 100, 1); ?> %</td>
				<td class="testcase_result <?php echo Labeler::decisionToText($measure["decision_criteria_id"]); ?>">
					<span class="content"><?php echo $form["decision_criteria_id"]; ?></span>
				</td>
				<td class="testcase_bugs"><div class="content"><?php echo $form["bugs"]->render(array("class" => "comment_field")); ?></div></td>
				<td class="testcase_notes"><div class="content"><?php echo $form["comment"]->render(array("class" => "comment_field")); ?></div></td>
			</tr>
		</tbody>
	</table>

	<div>
		<input class="small_btn cancel" type="button" value="Cancel" onclick="window.close()" />
		<input class="small_btn submit" type="submit" value="Save" />
	</div>
</form>

<script>
window.onunload = function(){
   window.opener.refreshResult('js_measure_<?php echo $measure["id"]; ?>', '<?php echo url_for("refresh_measure", array("id" => $measure["id"])); ?>');
}
</script>