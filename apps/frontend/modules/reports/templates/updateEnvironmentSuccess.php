<form method="post" action="<?php echo url_for("update_environment", array("id" => $environment["id"])); ?>">
	<?php echo $form["_csrf_token"]->render(); ?>

	<?php echo $form["id"]->render(); ?>

	<div style="margin-bottom: 10px;">
		<?php echo $form["name"]->renderLabel(); ?>
		<?php echo $form["name"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["name"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["description"]->renderLabel(); ?>
		<?php echo $form["description"]->render(); ?>
		<div class="formError"><?php echo $form["description"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["cpu"]->renderLabel(); ?>
		<?php echo $form["cpu"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["cpu"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["board"]->renderLabel(); ?>
		<?php echo $form["board"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["board"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["gpu"]->renderLabel(); ?>
		<?php echo $form["gpu"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["gpu"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 30px;">
		<?php echo $form["other_hardware"]->renderLabel(); ?>
		<?php echo $form["other_hardware"]->render(); ?>
		<div class="formError"><?php echo $form["other_hardware"]->renderError(); ?></div>
	</div>

	<div>
		<input class="small_btn cancel" type="button" value="Close" onclick="window.opener.refreshEnvironmentImage('<?php echo url_for("@homepage"); ?>')" />
		<input class="small_btn submit" type="submit" value="Save" />
	</div>
</form>
