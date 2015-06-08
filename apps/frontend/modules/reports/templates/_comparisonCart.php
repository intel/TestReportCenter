 <?php
	if($sf_user->hasAttribute('session_comparison'))
	{
		// Get session ids from user session
		$sessionComparisons = $sf_user->getAttribute('session_comparison', array()); ?>

	<p><b>Comparison:</b>
		<span class="emphasis"><a href="<?php echo url_for("see_report", array("id" => $sessionComparisons[0])); ?>" title="See report">#<?php echo $sessionComparisons[0];?></a></span>
		to
		<?php if(count($sessionComparisons) > 1): ?>
			<span class="emphasis"><a href="<?php echo url_for("see_report", array("id" => $sessionComparisons[1])); ?>" title="See report">#<?php echo $sessionComparisons[1];?></a></span>
		<?php else: ?>
			<span style="font-style: italic">Undefined</span>
		<?php endif; ?>&nbsp;
		<a class="ui_btn" href="<?php if(count($sessionComparisons) > 1) echo url_for("compare_to", array("id1" => $sessionComparisons[1], "id2" => $sessionComparisons[0])); else echo "#"; ?>" title="Compare reports" >Compare</a></p> <?php
	}
