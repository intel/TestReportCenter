<?php
	$projectToProductId = $configuration->getProjectToProductId();
	$projectToProductObj = Doctrine_Core::getTable("ProjectToProduct")->findOneById($projectToProductId);
	$productTypeObj = Doctrine_Core::getTable("ProductType")->findOneById($projectToProductObj->getProductId());
?>

(<?php echo $configuration->getProjectToProductId(); ?>) <a href="<?php echo url_for("edit_project", array("id" => $projectToProductObj->getProjectId())); ?>"><?php echo Doctrine_Core::getTable("Project")->findOneById($projectToProductObj->getProjectId())->getName(); ?></a> - <a href="<?php echo url_for("edit_product", array("id" => $projectToProductObj->getProductId())); ?>"><?php echo !empty($productTypeObj) ? $productTypeObj->getName() : "<?>"; ?></a>