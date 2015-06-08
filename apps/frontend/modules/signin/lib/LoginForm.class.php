<?php
class LoginForm extends sfGuardUserForm
{
	/**
	 * (non-PHPdoc)
	 * @see sfGuardUserForm::configure()
	 */
	public function configure()
	{
		$this->setWidgets(array(
			'username' => new sfWidgetFormInputText(),
			'password' => new sfWidgetFormInputPassword(array('type' => 'password')),
			'remember' => new sfWidgetFormInputCheckbox(),
		));

		$this->setValidators(array(
			'username' => new sfValidatorString(),
			'password' => new sfValidatorString(),
			'remember' => new sfValidatorBoolean(),
		));

		$this->widgetSchema->setLabels(array(
			"username"	=> "Email",
			"remember"	=> "Remember me for 2 weeks"
		));

		$this->validatorSchema->setPostValidator(new sfGuardValidatorUser());

		$this->widgetSchema->setNameFormat('signin[%s]');
	}
}