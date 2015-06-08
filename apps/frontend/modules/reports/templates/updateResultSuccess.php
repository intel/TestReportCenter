<form method="post" enctype="multipart/form-data" action="<?php echo url_for("update_result", array("id" => $result["id"])); ?>">
	<?php echo $form["_csrf_token"]->render(); ?>

	<table class="detailed_results">
		<thead>
			<tr>
				<th id="th_test_case">Name</th>
				<th id="th_result">Result</th>
				<th id="th_bugs">Bugs</th>
				<th id="th_notes">Notes</th>
			</tr>
		</thead>

		<tbody>
			<tr class="testcase result_<?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?>">
				<td class="testcase_name"><?php echo $result["name"]; ?></td>
				<td class="testcase_result">
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
   window.opener.refreshResult('js_result_<?php echo $result["id"]; ?>', '<?php echo url_for("refresh_result", array("id" => $result["id"])); ?>');
}
</script>