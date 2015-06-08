<?php

/**
 * Image form.
 *
 * @package    trc
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ImportImageForm extends BaseImageForm
{
	public function configure()
	{
		// Retrieve image values from options
		$values = $this->getOption("image");

		// Unset non essential widgets
		unset(
			$this["name_slug"]
		);

		// Customize widgets
		$this->setWidget("description", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));
		$this->setWidget("other_fw", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));

		// Set default values
		$this->setDefaults(array(
			"id" => $values["id"],
			"name" => $values["name"],
			"description" => $values["description"],
			"os" => $values["os"],
			"distribution" => $values["distribution"],
			"version" => $values["version"],
			"kernel" => $values["kernel"],
			"architecture" => $values["architecture"],
			"other_fw" => $values["other_fw"],
			"binary_link" => $values["binary_link"],
			"source_link" => $values["source_link"]
		));

		// Set widgets labels
		$this->widgetSchema->setLabels(array(
			"name"				=> "Image",
			"description"		=> "Description",
			"os"				=> "Operating system",
			"distribution"		=> "Distribution",
			"version"			=> "Version",
			"kernel"			=> "Kernel",
			"architecture"		=> "Architecture",
			"other_fw"			=> "Other",
			"binary_link"		=> "Binary link",
			"source_link"		=> "Source link"
		));
	}
}
