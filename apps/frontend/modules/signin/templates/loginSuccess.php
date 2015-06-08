<div id="page">
	<div class="page_content">

		<?php echo $form->renderFormTag(url_for('signin/login')) ?>
			<h1>Login to <?php echo $projectGroup; ?> <?php echo sfConfig::get('app_project_name'); ?></h1>

			<div class="field last">
				<?php echo $form["username"]->renderLabel() ?>
				<?php echo $form["username"]->render(array("class" => "text", "size" => 30)) ?>
				<div class="formError"><?php echo $form["username"]->renderError(); ?></div>
			</div> <!-- /field -->

			<div class="field last">
				<?php echo $form["password"]->renderLabel() ?>
				<?php echo $form["password"]->render(array("class" => "text short", "size" => 30)) ?>
				<?php if(!$ldapAuthentication):?>
					<small class="suggestions"><a href="<?php echo url_for("resetRequest"); ?>" title="Reset my password">Reset my password</a></small>
				<?php endif; ?>
				<div class="formError"><?php echo $form["password"]->renderError(); ?></div>
			</div> <!-- /field -->

			<?php echo $form["_csrf_token"]->render(); ?>

			<div class="field last no_label">
				<?php echo $form["remember"]->render(array("id" => "user_remember_me")) ?>
				<?php echo $form["remember"]->renderLabel(null, array("class" => "inline_label small")) ?>
			</div> <!-- /field -->

			<?php if(!$ldapAuthentication):?>
				<div class="field last no_label">
					<a href="<?php echo url_for("apply"); ?>" title="Create a new account">Create a new account</a>
				</div> <!-- /field -->
			<?php endif; ?>

			<div id="wizard_actions">
				<input class="big_btn login" id="user_submit" name="commit" type="submit" value="Login" />
			</div>  <!-- /wizard_actions -->
		</form>

	</div> <!-- /page_content -->
</div> <!-- /page -->
