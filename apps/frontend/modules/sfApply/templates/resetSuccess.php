<?php use_helper('I18N') ?>
<?php slot('sf_apply_login') ?>
<?php end_slot() ?>
<div class="sf_apply sf_apply_reset">
	<form method="POST" action="<?php echo url_for("sfApply/reset") ?>" name="sf_apply_reset_form" id="sf_apply_reset_form">
		<p>
		<?php echo __(<<<EOM
Thanks for confirming your email address. You may now change your
password using the form below.
EOM
		) ?>
		</p>
		<ul>
			<?php foreach ($form->getGlobalErrors() as $name => $error): ?>
			<?php echo $name.': '.$error ?>
			<?php endforeach; ?>

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
					<input class="big_btn next" type="submit" value="Reset" name="commit">
				</div>
			</div>
		</ul>
	</form>
</div>
