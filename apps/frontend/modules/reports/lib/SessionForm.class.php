<?php
class SessionForm extends TestSessionForm
{
	/**
	 * (non-PHPdoc)
	 * @see TestSessionForm::configure()
	 */
	public function configure()
	{
		// Retrieve form's options
		$projectGroupId = $this->getOption("projectGroupId");
		$securityLevel = $this->getOption("securityLevel");
		$projectId = $this->getOption("projectId");
		$productId = $this->getOption("productId");
		$environment = $this->getOption("environment");
		$image = $this->getOption("image");
		$session = $this->getOption("session");
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
		$currentProject = Doctrine_Core::getTable("Project")->getBasicProjectById($projectId);
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
		$currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductById($productId);
		if($currentProduct != null && in_array($currentProduct, $products))
			$defaultProduct = $currentProduct["id"];
		else
			$defaultProduct = $products[0]["id"];

		// Unset non essential widgets
		unset(
			$this["configuration_id"]
		);

		// Update extended widgets
		$this->setWidget("user_id", new sfWidgetFormInputHidden());
		$this->setWidget("editor_id", new sfWidgetFormInputHidden());

		// Set additional widgets
		$this->setWidget("project_group_id", new sfWidgetFormInputHidden(
			array(),
			array("value" => $projectGroupId)
		));

		$this->setWidget("status", new sfWidgetFormSelect(array(
			"choices"  => array(
				0 => "Not started",
				1 => "In progress",
				2 => "Done",
				3 => "Stopped",
				4 => "Go",
				5 => "No go"),
		)));

		$this->setWidget("author_name", new sfWidgetFormInputText());
		$this->setWidget("editor_name", new sfWidgetFormInputText());

		$this->setWidget("created_at", new sfWidgetFormInputText());
		$this->setWidget("updated_at", new sfWidgetFormInputText());

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

		$this->setWidget("test_objective", new sfWidgetFormTextarea());
		$this->setWidget("qa_summary", new sfWidgetFormTextarea());
		$this->setWidget("issue_summary", new sfWidgetFormTextarea());
		$this->setWidget("notes", new sfWidgetFormTextarea());
		$this->setWidget("published", new sfWidgetFormInputHidden());

		// Set default values
		$this->setDefaults(array(
			"id" => $session["id"],
			"name" => $session["name"],
			"test_objective" => $session["test_objective"],
			"qa_summary" => $session["qa_summary"],
			"user_id" => $session["user_id"],
			"created_at" => $session["created_at"],
			"editor_id" => $session["editor_id"],
			"updated_at" => $session["updated_at"],
			"project_release" => $session["project_release"],
			"project_milestone" => $session["project_milestone"],
			"issue_summary" => $session["issue_summary"],
			"notes" => $session["notes"],
			"status" => $session["status"],
			"published" => $session["published"],
			"configuration_id" => $session["configuration_id"],
			"campaign_checksum" => $session["campaign_checksum"]
		));

		// Set labels
		$this->widgetSchema->setLabels(array(
			"name" 					=> "Title",
			"author_name" 			=> "Author",
			"editor_name"			=> "Editor",
			"created_at"			=> "Test execution date",
			"updated_at"			=> "Updated at",
			"build_id"				=> "Build ID",
			"testset"				=> "Test type",
			"project"				=> "Project",
			"product"				=> "Product",
			"status"				=> "Status",
			"notes"					=> "Environment Summary"
		));

		// Set validators
		$this->setValidator('id', new sfValidatorInteger());
		$this->setValidator('project_group_id', new sfValidatorInteger());
		$this->setValidator('project', new sfValidatorInteger());
		$this->setValidator('product', new sfValidatorInteger());
		$this->setValidator("build_id", new sfValidatorString(array("required" => $mandatoryBuildId)));
		$this->setValidator("testset", new sfValidatorString(array("required" => $mandatoryTestset)));
		$this->setValidator('author_name', new sfValidatorString());
		$this->setValidator('editor_name', new sfValidatorString());

		// Set embedded forms
		$environmentForm = new ImportTestEnvironmentForm(array(), array("environment" => $environment));
		$this->embedForm("environmentForm", $environmentForm);

		$imageForm = new ImportImageForm(array(), array("image" => $image));
		$this->embedForm("imageForm", $imageForm);

		// Prefix all inputs
		$this->widgetSchema->setNameFormat('test_session[%s]');
	}

	public function formatRadioLine($widget, $inputs)
	{
		$rows = array();
		foreach ($inputs as $input)
		{
			$mygt = strpos($input['label'], '>');
			$myrow = substr($input['label'], 0, $mygt)." class=\"inline_label\">";
			$myrow .= $input['input'].$widget->getOption('label_separator').substr($input['label'], $mygt+1);
			$rows[] = $myrow;
		}
		return implode($widget->getOption('separator'), $rows);
	}

	public function debug()
	{
		if (sfConfig::get('sf_environment') != 'dev')
		{
			return;
		}
		foreach($this->getErrorSchema()->getErrors() as $key => $error)
		{
			echo '<p>' . $key . ': ' . $error . '</p>';
		}
	}
}
