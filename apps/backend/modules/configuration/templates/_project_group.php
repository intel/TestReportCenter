<?php
	$projectToProductId = $configuration->getProjectToProductId();
	$projectToProductObj = Doctrine_Core::getTable("ProjectToProduct")->findOneById($projectToProductId);
?>

<a href="<?php echo url_for("edit_group", array("id" => $projectToProductObj->getProjectGroupId())); ?>"><?php echo Doctrine_Core::getTable("sfGuardGroup")->findOneById($projectToProductObj->getProjectGroupId())->getName(); ?></a>