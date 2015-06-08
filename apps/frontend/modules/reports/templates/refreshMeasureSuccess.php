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
<td class="testcase_bugs"><div class="content"><?php echo MiscUtils::formatWikimarkups($measure["bugs"]); ?></div></td>
<td class="testcase_notes"><div class="content"><?php echo nl2br(MiscUtils::formatWikimarkups($measure["comment"])); ?></div></td>