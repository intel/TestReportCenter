<?php

/**
 * TestSession form.
 *
 * @package    trc
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TestSessionForm extends BaseTestSessionForm
{
	public function configure()
	{
		$this->setWidget("test_objective", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));
		$this->setWidget("qa_summary", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));
		$this->setWidget("issue_summary", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));
		$this->setWidget("notes", new sfWidgetFormTextarea(array(), array(
			"rows" => 3
		)));
	}
}
