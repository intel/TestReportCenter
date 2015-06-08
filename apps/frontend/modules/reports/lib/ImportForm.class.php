<?php
class ImportForm extends sfForm
{
	/**
	 * (non-PHPdoc)
	 * @see sfForm::configure()
	 */
	public function configure()
	{
		// Retrieve form's options
		$projectGroupId = $this->getOption("projectGroupId");
		$securityLevel = $this->getOption("securityLevel");
		$projectSlug = $this->getOption("projectSlug");
		$productSlug = $this->getOption("productSlug");
		$environmentSlug = $this->getOption("environmentSlug");
		$imageSlug = $this->getOption("imageSlug");
		$buildSlug = $this->getOption("buildSlug");
		$testsetSlug = $this->getOption("testsetSlug");
		$mandatoryBuildId = $this->getOption("mandatoryBuildId");
		$mandatoryTestset = $this->getOption("mandatoryTestset");

		// Get list of projects
		$projects = Doctrine_Core::getTable("Project")->getBasicProjects($projectGroupId, $securityLevel);
		// Convert projects to an associative array with project id as key
		$projectOptions = array();
		foreach($projects as $project)
		{
			$projectOptions[$project["id"]] = $project["name"];
		}

		// Set default selected project
		$currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($projectSlug);
		if($currentProject != null && in_array($currentProject, $projects))
			$defaultProject = $currentProject["id"];
		else
			$defaultProject = $projects[0]["id"];

		// Get list of products
		$products = Doctrine_Core::getTable("Project")->getBasicProducts($projectGroupId, $defaultProject);
		// Convert products to an associative array with product id as key
		$productOptions = array();
		foreach($products as $product)
		{
			$productOptions[$product["id"]] = $product["name"];
		}

		// Set default selected product
		$currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($productSlug);
		if($currentProduct != null && in_array($currentProduct, $products))
			$defaultProduct = $currentProduct["id"];
		else
			$defaultProduct = $products[0]["id"];

		// Set default environment
		$currentEnvironment = Doctrine_Core::getTable("TestEnvironment")->getEnvironmentBySlug($environmentSlug);

		// Set default image
		$currentImage = Doctrine_Core::getTable("Image")->getImageBySlug($imageSlug);

		// Set build id and testset
		$currentBuild = Doctrine_Core::getTable("TestSession")->getBasicBuildBySlug($buildSlug);
		$currentTestset = Doctrine_Core::getTable("TestSession")->getBasicTestsetBySlug($testsetSlug);

		// Set all widgets
		$this->setWidget("project_group_id", new sfWidgetFormInputHidden(array(), array("value" => $projectGroupId)));

		$this->setWidget("name", new sfWidgetFormInputText());
		$this->setWidget("build_id", new sfWidgetFormInputText());
		$this->setWidget("testset", new sfWidgetFormInputText());

		$this->setWidget("project", new sfWidgetFormSelectRadio(array(
			"formatter" => array($this, "formatRadioLine"),
			"choices"	=> $projectOptions,
			"default" => $defaultProject
		)));

		$this->setWidget("product", new sfWidgetFormSelectRadio(array(
			"formatter" => array($this, "formatRadioLine"),
			"choices"	=> $productOptions,
			"default" => $defaultProduct
		)));

		$this->setWidget("date", new sfWidgetFormJQueryDate(array(
			"image" => "/images/calendar_icon.png",
			"config" => "{}"
		)));

		// Set widgets labels
		$this->widgetSchema->setLabels(array(
			"project"		=> "Project",
			"product"		=> "Product type",
			"build_id"		=> "Build ID",
			"testset"		=> "Test type",
			"name"			=> "Report title",
			"date"			=> "Test execution date",
		));

		// Set default values
		$this->setDefaults(array(
			"date" => date("Y-m-d"),
			"build_id" => $currentBuild["build_id"],
			"testset" => $currentTestset["testset"]
		));

		// Set validators
		$this->setValidator("project_group_id", new sfValidatorInteger());
		$this->setValidator("project", new sfValidatorInteger());
		$this->setValidator("product", new sfValidatorInteger());
		$this->setValidator("build_id", new sfValidatorString(array("required" => $mandatoryBuildId)));
		$this->setValidator("testset", new sfValidatorString(array("required" => $mandatoryTestset)));
		$this->setValidator("name", new sfValidatorString(array("required" => false)));
		$this->setValidator("date", new sfValidatorDate());

		// Use custom FormSchemaFormatter
		$custom_decorator = new sfWidgetFormSchemaFormatterCustom($this->getWidgetSchema());
		$this->widgetSchema->addFormFormatter("custom", $custom_decorator);
		$this->widgetSchema->setFormFormatterName("custom");

		$environmentForm = new ImportTestEnvironmentForm(array(), array("environment" => $currentEnvironment));
		$this->embedForm("environmentForm", $environmentForm);

		$imageForm = new ImportImageForm(array(), array("image" => $currentImage));
		$this->embedForm("imageForm", $imageForm);

		// Prefix all inputs
		$this->widgetSchema->setNameFormat("test_session[%s]");
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
