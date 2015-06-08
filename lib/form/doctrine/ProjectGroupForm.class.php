<?php

/**
 * ProjectGroup form.
 *
 * @package    trc
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectGroupForm extends BaseProjectGroupForm
{
	public function configure()
	{
		$this->setWidget("description", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));
	}
}
