<?php

/**
 * profile actions.
 *
 * @package    trc
 * @subpackage profile
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class profileActions extends sfActions
{
	/**
	 * Executes edit action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeEdit(sfWebRequest $request)
	{
		// Get user profile from database
		$user = $this->getUser()->getGuardUser();
		$profile = Doctrine_Core::getTable("SfGuardUserProfile")->findOneByUserId($user->getId());

		$this->ldapAuthentication = (sfConfig::get("app_authentication_method", "symfony") == "ldap") ? true : false ;

		// If user has no profile, create a new one for him
		if(empty($profile))
		{
			$profile = new sfGuardUserProfile();
			$profile->setUserId($user->getId());
			$profile->setToken(MiscUtils::generateToken());
			$profile->setSecurityLevel(sfConfig::get("app_security_level_new_user", 0));
			$profile->save();
		}

		$this->form = new ProfileForm(array(
			'first_name' => $user->getFirstName(),
			'last_name' => $user->getLastName(),
			'email' => $user->getEmailAddress(),));
		$this->token = $profile->getToken();
		$this->securityLevel = Labeler::getSecurityLevelLabel($profile->getSecurityLevel());

		// Process form
		if($request->isMethod("post"))
		{
			$this->processEdit($request, $this->form);
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 * @param ProfileForm $form
	 */
	protected function processEdit(sfWebRequest $request, ProfileForm $form)
	{
		$form->bind($request->getParameter('profile'));

		if($form->isValid())
		{
			$values = $form->getValues();

			$user = $this->getUser()->getGuardUser();
			if($user->checkPassword($values['current_password']))
			{
				// Set new password into sfGuardUser table
				if((!empty($values['new_password'])) && ($values['new_password'] == $values['confirm_new_password']))
					$user->setPassword($values['new_password']);

				$user->setFirstName($values['first_name']);
				$user->setLastName($values['last_name']);
				$user->setEmailAddress($values['email']);
				$user->save();

				// Set referer
				$this->getUser()->setReferer($request->getUri());

				// Redirect to referer
				$this->redirect($this->getUser()->getReferer());
			}
		}
	}
}
