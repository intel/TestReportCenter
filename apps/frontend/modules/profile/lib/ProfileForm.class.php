<?php
class ProfileForm extends sfForm
{
	/**
	 * (non-PHPdoc)
	 * @see sfForm::configure()
	 */
	public function configure()
	{
		$this->setWidgets(array(
			'first_name' => new sfWidgetFormInputText(),
			'last_name' => new sfWidgetFormInputText(),
			'email' => new sfWidgetFormInputText(array(), array('readonly' => 'readonly')),
			'new_password' => new sfWidgetFormInputPassword(array('type' => 'password')),
			'confirm_new_password' => new sfWidgetFormInputPassword(array('type' => 'password')),
			'current_password' => new sfWidgetFormInputPassword(array('type' => 'password')),
		));

		$this->setValidators(array(
			'first_name' => new sfValidatorString(),
			'last_name' => new sfValidatorString(),
			'email' => new sfValidatorEmail(),
			'new_password' => new sfValidatorString(),
			'confirm_new_password' => new sfValidatorString(),
			'current_password' => new sfValidatorString(),
		));

		$this->getValidator('new_password')->setOption('required', false);
		$this->getValidator('confirm_new_password')->setOption('required', false);

		$emailLabel = (sfConfig::get('app_views_method') == "ldap") ? "Username" : "Email";

		$this->widgetSchema->setLabels(array(
			'first_name' => "First name",
			'last_name' => "Last name",
			'email'	=> $emailLabel,
			'new_password' => "Password",
			'confirm_new_password' => "Password confirmation",
			'current_password' => "Current password"
		));

		$this->widgetSchema->setNameFormat('profile[%s]');
	}
}