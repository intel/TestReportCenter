<form method="post" action="<?php echo url_for("update_image", array("id" => $image["id"])); ?>">
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
		<?php echo $form["os"]->renderLabel(); ?>
		<?php echo $form["os"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["os"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["distribution"]->renderLabel(); ?>
		<?php echo $form["distribution"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["distribution"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["version"]->renderLabel(); ?>
		<?php echo $form["version"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["version"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["kernel"]->renderLabel(); ?>
		<?php echo $form["kernel"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["kernel"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["architecture"]->renderLabel(); ?>
		<?php echo $form["architecture"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["architecture"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["other_fw"]->renderLabel(); ?>
		<?php echo $form["other_fw"]->render(); ?>
		<div class="formError"><?php echo $form["other_fw"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 10px;">
		<?php echo $form["binary_link"]->renderLabel(); ?>
		<?php echo $form["binary_link"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["binary_link"]->renderError(); ?></div>
	</div>

	<div style="margin-bottom: 30px;">
		<?php echo $form["source_link"]->renderLabel(); ?>
		<?php echo $form["source_link"]->render(array("class" => "text", "size" => "30")); ?>
		<div class="formError"><?php echo $form["source_link"]->renderError(); ?></div>
	</div>

	<div>
		<input class="small_btn cancel" type="button" value="Close" onclick="window.opener.refreshEnvironmentImage('<?php echo url_for("@homepage"); ?>')" />
		<input class="small_btn submit" type="submit" value="Save" />
	</div>
</form>
