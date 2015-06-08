<?php

/**
 * apply actions.
 *
 * @package    trc
 * @subpackage apply
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class sfApplyActions extends BasesfApplyActions
{
	/**
	 * Executes apply action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeApply(sfRequest $request)
	{
		$this->form = $this->newForm('sfApplyApplyForm');
		if ($request->isMethod('post'))
		{
			$parameter = $request->getParameter('sfApplyApply');
			$this->form->bind($request->getParameter('sfApplyApply'));
			if ($this->form->isValid())
			{
				$guid = "n" . self::createGuid();
				$this->form->setValidate($guid);
				$this->form->save();

				// Generate unique token based on random time
				list($usec, $sec) = explode(" ", microtime());
				$rand_num = substr(sha1((int)($usec*1000000 * ($sec/1000000))), 0, 20);

				// Retrieve current user
				$user = $this->form->getObject();
				
				$now = date("Y-m-d H:i:s");

				// Create new entry into sfGuardUserProfile table
				$profileObject = new sfGuardUserProfile();
				$profileObject->setUserId($user->getId());
				$profileObject->setToken($rand_num);
				$profileObject->setSecurityLevel(sfConfig::get('app_security_level_new_user'));
				
				$userPermission = Doctrine_Core::getTable("sfGuardPermission")->findOneByName(sfConfig::get('app_permission_new_user'));
				if (empty($userPermission))
				{
					return;
				}
				
				// Create new entry into sfGuardUserPermission table
				$permissionObject = new sfGuardUserPermission();
				$permissionObject->setUserId($user->getId());
				$permissionObject->setPermissionId($userPermission->getId());
				$permissionObject->setCreatedAt($now);
				$permissionObject->setUpdatedAt($now);
				
				$userGroup = Doctrine_Core::getTable("sfGuardGroup")->findOneByName(sfConfig::get('app_project_group'));
				if (empty($userGroup))
				{
					return;
				}
				
				// Create new entry into sfGuardUserGroup table
				$groupObject = new sfGuardUserGroup();
				$groupObject->setUserId($user->getId());
				$groupObject->setGroupId($userGroup->getId());
				$groupObject->setCreatedAt($now);
				$groupObject->setUpdatedAt($now);

				try
				{
					// Send mail
					$this->sendVerificationMail($user);
					// Save tables entries
					$profileObject->save();
					$permissionObject->save();
					$groupObject->save();
					return 'After';
				}
				catch (Exception $e)
				{
					$groupObject->delete();
					$permissionObject->delete();
					$profileObject->delete();
					$user->delete();
					throw $e;
					// You could re-throw $e here if you want to
					// make it available for debugging purposes
					return 'MailerError';
				}
			}
		}
	}
}
