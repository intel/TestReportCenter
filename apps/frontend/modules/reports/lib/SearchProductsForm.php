<?php
class SearchProductsForm extends sfForm
{
    /**
     * (non-PHPdoc)
     * @see sfForm::configure()
     */
    public function configure()
    {
		$projectGroupId = $this->getOption("projectGroupId");
		$projectId = $this->getOption("projectId");

		// Get list of products
		$products = Doctrine_Core::getTable("Project")->getBasicProducts($projectGroupId, $projectId);
		// Convert products to an associative array with product id as key
		$productOptions = array();
		foreach($products as $product)
		{
		    $productOptions[$product["id"]] = $product["name"];
		}

		// Set default selected product
		$defaultProduct = $products[0]["id"];

		$this->setWidget("product", new sfWidgetFormSelectRadio(array(
			"formatter" => array($this, "formatRadioLine"),
			"choices"	=> $productOptions,
			"default" => $defaultProduct
		)));

		// Set widgets labels
		$this->widgetSchema->setLabels(array(
			"product"		=> "Product type"
		));

		// Set validators
		$this->setValidator("product", new sfValidatorInteger());

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