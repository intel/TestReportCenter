<?php

/**
 * signin actions.
 *
 * @package    trc
 * @subpackage signin
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class signinActions extends sfActions
{
	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeLogin(sfWebRequest $request)
	{
		// Redirect user to its referer if he is already authenticated
		if ($this->getUser()->isAuthenticated())
		{
			return $this->redirect($this->getUser()->getReferer());
		}

		$this->projectGroup = sfConfig::get("app_project_group");
		$this->ldapAuthentication = (sfConfig::get("app_authentication_method", "symfony") == "ldap") ? true : false ;

		if($this->ldapAuthentication)
		{
			$this->form = new LdapForm();
			if($request->isMethod("post"))
			{
				$this->processLdap($request, $this->form);
			}
		}
		else
		{
			$this->form = new LoginForm();
			if($request->isMethod("post"))
			{
				$this->processLogin($request, $this->form);
			}
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 * @param LoginForm $form
	 */
	protected function processLogin(sfWebRequest $request, LoginForm $form)
	{
		$form->bind($request->getParameter('signin'));
		if($form->isValid())
		{
			$values = $form->getValues();
			$this->getUser()->signIn($values['user'], array_key_exists('remember', $values) ? $values['remember'] : false);

			// Set the tow previous referer to the same value for:
			// 1) redirect to previous user's location
			// 2) avoid redirect loop in signin
			$this->getUser()->setReferer($this->getUser()->getReferer());

			// Redirect to referer
			return $this->redirect($this->getUser()->getReferer());
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 * @param LdapForm $form
	 */
	protected function processLdap(sfWebRequest $request, LdapForm $form)
	{
		$form->bind($request->getParameter('signin'));
		if($form->isValid())
		{
			$values = $form->getValues();

			// Check if user already exists in the DB
			$user = Doctrine::getTable('sfGuardUser')->findOneByUsername($values["username"]);

			// If not, create an account for him
			if(empty($user))
			{
				$datetime = date("Y-m-d H:i:s");

				// Create entry in sfGuardUser
				$sfGuardUser = new sfGuardUser();
				$sfGuardUser->setEmailAddress($values["username"]);
				$sfGuardUser->setUsername($values["username"]);
				$sfGuardUser->setFirstName($values["firstname"]);
				$sfGuardUser->setLastName($values["lastname"]);
				$sfGuardUser->setCreatedAt($datetime);
				$sfGuardUser->setUpdatedAt($datetime);
				$sfGuardUser->save();

				// Additional informations for user's profile
				$sfGuardUserProfile = new sfGuardUserProfile();
				$sfGuardUserProfile->setUserId($sfGuardUser->getId());
				$sfGuardUserProfile->setToken(MiscUtils::generateToken());
				$sfGuardUserProfile->setSecurityLevel(sfConfig::get("app_security_level_new_user", 0));
				$sfGuardUserProfile->save();

				$permission = Doctrine_Core::getTable("sfGuardPermission")->findOneByName(sfConfig::get("app_permission_new_user", "User"));
				if(!$permission)
				{
					$this->getUser()->setFlash("error", "Unable to set permissions for this account! Contact your administrator.");
					$sfGuardUserProfile->delete();
					$sfGuardUser->delete();
					return;
				}

				// Give basic permissions for user
				$sfGuardPermission = new sfGuardUserPermission();
				$sfGuardPermission->setUserId($sfGuardUser->getId());
				$sfGuardPermission->setPermissionId($permission->getId());
				$sfGuardPermission->setCreatedAt($datetime);
				$sfGuardPermission->setUpdatedAt($datetime);
				$sfGuardPermission->save();

				$userGroup = Doctrine_Core::getTable("sfGuardGroup")->findOneByName(sfConfig::get("app_project_group"));
				if(!$userGroup)
				{
					$this->getUser()->setFlash("error", "Unable to set project group for this account! Contact your administrator.");
					$sfGuardUserProfile->delete();
					$sfGuardUser->delete();
					$sfGuardPermission->delete();
					return;
				}

				// Create new entry into sfGuardUserGroup table
				$sfGuardGroup = new sfGuardUserGroup();
				$sfGuardGroup->setUserId($sfGuardUser->getId());
				$sfGuardGroup->setGroupId($userGroup->getId());
				$sfGuardGroup->setCreatedAt($datetime);
				$sfGuardGroup->setUpdatedAt($datetime);
				$sfGuardGroup->save();

				$user = $sfGuardUser;
			}

			$this->getUser()->signIn($user, array_key_exists('remember', $values) ? $values['remember'] : false);

			// Set the tow previous referer to the same value for:
			// 1) redirect to previous user's location
			// 2) avoid redirect loop in signin
			$this->getUser()->setReferer($this->getUser()->getReferer());

			// Redirect to referer
			return $this->redirect($this->getUser()->getReferer());
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeLogout(sfWebRequest $request)
	{
		$this->getUser()->signOut();
	    $this->redirect('@homepage');
	}
}
