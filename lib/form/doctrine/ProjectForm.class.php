<?php

/**
 * Project form.
 *
 * @package    trc
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectForm extends BaseProjectForm
{
	public function configure()
	{
		$this->setWidget("description", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));

		$this->setValidator('name_slug', new sfValidatorString(array('required' => false)));
	}

	public function updateNameSlugColumn($slug)
	{
		if(empty($slug))
			$slug = MiscUtils::slugify($this->values["name"]);

		// Slugify name, and check if slug generated does not already exist and generate a new one if needed
		$size = 1;
		while(Doctrine_Core::getTable("Project")->checkSlug($slug, $this->values["id"]))
		{
			$slug = MiscUtils::slugify($this->values["name"]).substr(microtime(), -$size);
			$size++;
		}

		return $slug;
	}
}
