<?php
class ProjectFormCustom extends ProjectForm
{
	public function configure()
	{
		// Retrieve form's options
		$defaultProjectGroupId = (is_null($this->getOption("projectGroupId"))) ? "0" : $this->getOption("projectGroupId");
		$defaultProductsIds = (is_null($this->getOption("productsIds"))) ? "0" : $this->getOption("productsIds");

		// Get list of products
		$products = Doctrine_Core::getTable("ProductType")->getAllBasicProducts();
		// Convert products to an associative array with product id as key
		$productList = array();
		foreach($products as $product)
		{
			$productList[$product["id"]] = $product["name"];
		}
		// Get list of groups
		$groups = Doctrine_Core::getTable("SfGuardGroup")->getBasicProjectGroups();
		// Convert products to an associative array with group id as key
		$groupList = array();
		foreach($groups as $group)
		{
			$groupList[$group["id"]] = $group["name"];
		}

		// Get list of users id
		$users = Doctrine_Core::getTable("SfGuardUser")->getIdList();
		// Convert products to an associative array with group id as key
		$userIdList = array();
		foreach($users as $user)
		{
			$userIdList[$user["id"]] = $user["username"];
		}

		$this->setWidget("description", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));
		$this->setWidget("user_id", new sfWidgetFormSelect(array(
				"choices"  => $userIdList,
		)));
		$this->setWidget("status", new sfWidgetFormSelect(array(
				"choices"  => array(0 => "Inactive", 1 => "Active", 2 => "Read-only"),
		)));
		$this->setWidget("security_level", new sfWidgetFormSelect(array(
				"choices"  => array(0 => "0", 1 => "1"),
		)));
		$this->setWidget('group', new sfWidgetFormSelect(array(
				'choices' => $groupList,
				'default' => $defaultProjectGroupId,
		)));
		$this->setWidget('product', new sfWidgetFormChoice(array(
				'multiple' => true,
				'expanded' => true,
				'renderer_options' => array('formatter' => array($this, 'formatRadioLine')),
				'choices' => $productList,
				'default' => $defaultProductsIds,
		)));

		// Set validators
		$this->setValidator('group', new sfValidatorChoice(array(
				'multiple' => false,
				'choices' => array_keys($groupList)
		)));
		$this->setValidator('product', new sfValidatorChoice(array(
				'multiple' => true,
				'choices' => array_keys($productList)
		)));
		$this->setValidator('name_slug', new sfValidatorString(array(
				'required' => false
		)));

		// Set labels
		$this->widgetSchema->setLabels(array(
				"group" 	=> "Group",
				"product" 	=> "Products",
		));

		// Prefix all inputs
		$this->widgetSchema->setNameFormat("project[%s]");
	}

	// Custom formatter so as to include inline_label css class as label attribute
	public function formatRadioLine($widget, $inputs)
	{
		$rows = array();
		foreach ($inputs as $input)
		{
			$mygt = strpos($input["label"], ">");
			$myrow = substr($input["label"], 0, $mygt)." class=\"inline_label\">";
			$myrow .= $input["input"].$widget->getOption("label_separator").substr($input["label"], $mygt+1);
			$rows[] = $myrow;
		}
		return implode($widget->getOption("separator"), $rows);
	}
}