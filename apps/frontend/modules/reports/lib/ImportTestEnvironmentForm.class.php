<?php

/**
 * TestEnvironment form.
 *
 * @package    trc
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ImportTestEnvironmentForm extends BaseTestEnvironmentForm
{
	public function configure()
	{
		// Retrieve environment values from options
		$values = $this->getOption("environment");

		// Unset non essential widgets
		unset(
			$this["name_slug"]
		);

		// Customize widgets
		$this->setWidget("description", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));
		$this->setWidget("other_hardware", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));

		// Set default values
		$this->setDefaults(array(
			"id" => $values["id"],
			"name" => $values["name"],
			"description" => $values["description"],
			"cpu" => $values["cpu"],
			"board" => $values["board"],
			"gpu" => $values["gpu"],
			"other_hardware" => $values["other_hardware"]
		));

		// Set widgets labels
		$this->widgetSchema->setLabels(array(
			"name"				=> "Test environment",
			"description"		=> "Description",
			"cpu"				=> "CPU",
			"board"				=> "Board",
			"gpu"				=> "GPU",
			"other_hardware"	=> "Other hardware"
		));
	}
}
