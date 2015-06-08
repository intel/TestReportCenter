<?php

require_once dirname(__FILE__).'/../lib/configurationGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/configurationGeneratorHelper.class.php';

/**
 * configuration actions.
 *
 * @package    trc
 * @subpackage configuration
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class configurationActions extends autoConfigurationActions
{
	public function executeNew(sfWebRequest $request)
	{
		$this->form = new ConfigurationForm();

		$this->form->setDefault('created_at', date("Y-m-d H:i:s"));
	}

	public function executeCreate(sfWebRequest $request)
	{
		$this->form = new ConfigurationForm();

		if($request->isMethod("post"))
		{
			$this->processCreate($request, $this->form);
		}

		$this->setTemplate('new');
	}

	protected function processCreate(sfWebRequest $request, ConfigurationForm $form)
	{
		$form->bind($request->getParameter($form->getName()));

		if ($form->isValid())
		{
			// Get POST values
			$values = $form->getValues();

			// Create new entry into Project table
			$configurationObject = new Configuration();
			$configurationObject->setImageId($values['image_id']);
			$configurationObject->setTestEnvironmentId($values['test_environment_id']);
			$configurationObject->setProjectToProductId($values['project_to_product_id']);
			$configurationObject->save();

			if ($request->hasParameter('_save_and_add'))
			{
				$this->getUser()->setFlash('notice', $notice.' You can add another one below.');

				$this->redirect('@configuration_new');
			}
			else
			{
				$this->getUser()->setFlash('notice', $notice);

				$this->redirect(array('sf_route' => 'configuration_edit', 'sf_subject' => $configurationObject));
			}
		}
		else
		{
			$this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
		}
	}

	public function executeEdit(sfWebRequest $request)
	{
		$this->configuration = $this->getRoute()->getObject();

		$this->form = new ConfigurationForm($this->configuration);
	}

	public function executeUpdate(sfWebRequest $request)
	{
		$this->configuration = $this->getRoute()->getObject();

		$this->form = new ConfigurationForm($this->configuration);

		$this->processUpdate($request, $this->form);

		$this->setTemplate('edit');
	}

	/**
	 * Delete configurations from database (batch action).
	 */
	public function executeBatchDelete(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$query = Doctrine_Manager::getInstance()->getCurrentConnection();

		$ids = $request->getParameter('ids');

		foreach($ids as $id)
		{
			$sql = "DELETE FROM ".$qa_generic.".test_session WHERE configuration_id = ".$id;
			$result = $query->execute($sql);

			$sql = "DELETE FROM ".$qa_generic.".configuration WHERE id = ".$id;
			$result = $query->execute($sql);
		}

		$this->getUser()->setFlash('notice', 'The selected configurations have been deleted successfully.');

		$this->redirect('configuration');
	}

	/**
	 * Delete configuration (list action).
	 */
	public function executeListDelete(sfWebRequest $request)
	{
		$configuration = $this->getRoute()->getObject();
		$configuration->delete_configuration();

		$this->getUser()->setFlash('notice', 'The selected configuration have been deleted successfully.');

		$this->redirect('configuration');
	}
}
