<?php
class ResultForm extends TestResultForm
{
	/**
	 * (non-PHPdoc)
	 * @see TestResultForm::configure()
	 */
	public function configure()
	{
		$this->useFields(array(
			"decision_criteria_id",
			"bugs",
			"comment"
		));

		$this->setWidget("decision_criteria_id", new sfWidgetFormSelect(array(
			"choices"  => array(-1 => "Pass", -2 => "Fail", -3 => "Block", -4 => "Deferred", -5 => "Not run"),
		)));
		$this->setWidget("bugs", new sfWidgetFormTextarea());
		$this->setWidget("comment", new sfWidgetFormTextarea());

		// Prefix all inputs
		$this->widgetSchema->setNameFormat('result[%s]');
	}
}