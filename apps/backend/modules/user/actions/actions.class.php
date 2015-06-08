<?php

require_once dirname(__FILE__).'/../lib/userGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/userGeneratorHelper.class.php';

/**
 * user actions.
 *
 * @package    trc
 * @subpackage user
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class userActions extends autoUserActions
{
	/**
	 * Delete users (batch action).
	 */
	public function executeBatchDelete(sfWebRequest $request)
	{
		$qa_core = sfConfig::get("app_table_qa_core");
		$query = Doctrine_Manager::getInstance()->getCurrentConnection();

		$ids = $request->getParameter('ids');

		foreach($ids as $id)
		{
			$sql = "UPDATE ".$qa_core.".sf_guard_user SET is_active = 0 WHERE id = ".$id;
			$result = $query->execute($sql);
		}

		$this->getUser()->setFlash('notice', 'The selected user accounts have been deactivated.');

		$this->redirect('sf_guard_user');
	}

	/**
	 * Delete user (list action).
	 */
	public function executeListDelete(sfWebRequest $request)
	{
		$user = $this->getRoute()->getObject();
		$user->delete_user();

		$this->getUser()->setFlash('notice', 'The selected user account have been deactivated successfully.');

		$this->redirect('sf_guard_user');
	}

	/**
	 * Delete user (object action).
	 */
	public function executeDelete(sfWebRequest $request)
	{
		$user = $this->getRoute()->getObject();
		$user->delete_user();

		$this->getUser()->setFlash('notice', 'The selected user account have been deactivated successfully.');

		$this->redirect('sf_guard_user');
	}

	public function executeNew(sfWebRequest $request)
	{
		parent::executeNew($request);

		// Generate unique token based on random time
		list($usec, $sec) = explode(" ", microtime());
		$rand_num = substr(sha1((int)($usec*1000000 * ($sec/1000000))), 0, 20);

		// Set default value into 'token' field
		$formProfile = $this->form->getDefault('Profile');
		$formProfile['token'] = $rand_num;
		$this->form->setDefault('Profile', $formProfile);

		// Set readonly mode onto 'token' field
		$formProfile = $this->form->getWidget('Profile');
		$formProfile['token']->setAttribute('readonly','readonly');
	}
}
