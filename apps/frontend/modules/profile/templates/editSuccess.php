<div id="page">
	<div class="page_content">
		<?php echo $form->renderFormTag(url_for('profile/edit')) ?>
			<h1>User Profile</h1>

			<div class="field last">
				<?php echo $form["first_name"]->renderLabel() ?>
				<?php echo $form["first_name"]->render(array("class" => "text", "size" => 30)) ?>
				<div class="formError"><?php echo $form["first_name"]->renderError(); ?></div>
			</div> <!-- /field -->

			<div class="field last">
				<?php echo $form["last_name"]->renderLabel() ?>
				<?php echo $form["last_name"]->render(array("class" => "text", "size" => 30)) ?>
				<div class="formError"><?php echo $form["last_name"]->renderError(); ?></div>
			</div> <!-- /field -->

			<div class="field last">
				<?php echo $form["email"]->renderLabel() ?>
				<?php echo $form["email"]->render(array("class" => "text", "size" => 30)) ?>
				<div class="formError"><?php echo $form["email"]->renderError(); ?></div>
			</div> <!-- /field -->

			<?php if(!$ldapAuthentication): ?>
			<div class="field last">
				<?php echo $form["new_password"]->renderLabel() ?>
				<?php echo $form["new_password"]->render(array("class" => "text short", "size" => 30)) ?>
				<small class="suggestions">leave blank if you don't want to change it</small>
				<div class="formError"><?php echo $form["new_password"]->renderError(); ?></div>
			</div> <!-- /field -->

			<div class="field last">
				<?php echo $form["confirm_new_password"]->renderLabel() ?>
				<?php echo $form["confirm_new_password"]->render(array("class" => "text short", "size" => 30)) ?>
				<div class="formError"><?php echo $form["confirm_new_password"]->renderError(); ?></div>
			</div> <!-- /field -->

			<div class="field last">
				<?php echo $form["current_password"]->renderLabel() ?>
				<?php echo $form["current_password"]->render(array("class" => "text short", "size" => 30)) ?>
				<small class="suggestions">we need your current password to confirm your changes</small>
				<div class="formError"><?php echo $form["current_password"]->renderError(); ?></div>
			</div> <!-- /field -->
			<?php endif; ?>

			<?php echo $form["_csrf_token"]->render(); ?>

			<div class="field last">
				<label>Security level</label>
				<input class="text" style="width:200px" type="text" name="" value="<?php echo $securityLevel; ?>" readonly="readonly" />
				<!-- <small class="suggestions"><a href="" title="Request a higher security level">Request a higher security level</a></small> -->
			</div> <!-- /field -->

			<div class="field last">
				<label>API token </label>
				<span class="static_text"><?php echo $token; ?></span>
			</div> <!-- /field -->

			<div id="wizard_actions">
				<div id="wizard_buttons">
					<input class="big_btn cancel" type="button" value="Cancel" onclick="history.back();" style="margin-right: 20px;">
					<?php if(!$ldapAuthentication): ?>
					<input class="big_btn login" id="user_submit" name="commit" type="submit" value="Update" />
					<?php endif; ?>
				</div>
			</div>  <!-- /wizard_actions -->
		</form>
	</div> <!-- /page_content -->
</div> <!-- /page -->
