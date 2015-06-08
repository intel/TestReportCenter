<?php use_helper('I18N') ?>

<div id="page">
	<div class="page_content">

		<form method="POST" action="<?php echo url_for('apply') ?>" name="sf_apply_apply_form" id="sf_apply_apply_form">
			<h1>Apply for an Account</h1>

			<ul>
				<?php foreach ($form as $element): ?>
				<?php if ($element == $form["_csrf_token"]): ?>
				<?php echo $form["_csrf_token"]->render(); ?>
				<?php else: ?>
				<div class="field last">
					<?php echo $element->renderLabel(); ?>
					<?php echo $element->render(array("class" => "text", "size" => "30")); ?>
					<div class="formError"><?php echo $element->renderError(); ?></div>
				</div>
				<?php endif; ?>
				<?php endforeach; ?>

				<div id="wizard_actions">
					<div id="wizard_buttons">
						<input class="big_btn cancel" type="button" value="Cancel" onclick="location.href='<?php echo url_for(sfConfig::get('app_sfApplyPlugin_after', 'sf_guard_signin')); ?>'">
						<input class="big_btn next" type="submit" value="Create" name="commit">
					</div>
				</div>
			</ul>
		</form>

	</div> <!-- /page_content -->
</div> <!-- /page -->

