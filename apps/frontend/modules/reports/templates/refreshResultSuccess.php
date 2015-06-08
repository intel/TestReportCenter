<td class="testcase_case_id">
	<a class="edit_list_item toggle_testcase" href="javascript:;" onclick="updateResult('js_result_<?php echo $result["id"]; ?>', '<?php echo url_for("update_result", array("id" => $result["id"])); ?>')" title="Edit">Edit</a>
	<a class="remove_list_item toggle_testcase" href="javascript:;" onclick="clear_html('js_result_<?php echo $result["id"]; ?>');delete_result('<?php echo url_for('delete_result', array("id" => $result["id"])); ?>')" title="Remove">Remove</a>

	<?php echo $result["name"]; ?>
</td>
<td class="testcase_name"><?php echo $result["complement"]; ?></td>
<td class="testcase_result <?php echo Labeler::decisionToText($result["decision_criteria_id"]); ?>">
	<span class="content"><?php echo ucfirst(str_replace("_", " ", Labeler::decisionToText($result["decision_criteria_id"]))); ?></span>
</td>
<td class="testcase_bugs"><div class="content"><?php echo MiscUtils::formatWikimarkups($result["bugs"]); ?></div></td>
<td class="testcase_notes"><div class="content"><?php echo nl2br(MiscUtils::formatWikimarkups($result["comment"])); ?></div></td>