<?php

require_once dirname(__FILE__).'/../lib/projectGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/projectGeneratorHelper.class.php';

/**
 * project actions.
 *
 * @package    trc
 * @subpackage project
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectActions extends autoProjectActions
{
	public function executeNew(sfWebRequest $request)
	{
		// Initialize a new project object to disable a warning in the UI
		$this->project = new Project();

		$this->form = new ProjectFormCustom();
		$this->form->setDefault('created_at', date("Y-m-d H:i:s"));
	}

	public function executeCreate(sfWebRequest $request)
	{
		$this->form = new ProjectFormCustom();

		if($request->isMethod("post"))
		{
			$this->processCreate($request, $this->form);
		}

		$this->setTemplate('new');
	}

	protected function processCreate(sfWebRequest $request, ProjectFormCustom $form)
	{
		$form->bind($request->getParameter($form->getName()));

		if ($form->isValid())
		{
			$values = $form->getValues();

			// Slugify name, and check if slug generated does not already exist and generate a new one if needed
			if (empty($values['name_slug']))
				$slug = MiscUtils::slugify($values['name']);
			else
				$slug = $values['name_slug'];
			$size = 1;
			while(Doctrine_Core::getTable("Project")->checkSlugForProject(NULL, $slug, true))
			{
				$slug = MiscUtils::slugify($values['name']).substr(microtime(), -$size);
				$size++;
			}

			// Create the project into database
			$projectObject = new Project();
			$projectObject->setName($values['name']);
			$projectObject->setDescription($values['description']);
			$projectObject->setUserId($values['user_id']);
			$projectObject->setCreatedAt($values['created_at']);
			$projectObject->setStatus($values['status']);
			$projectObject->setSecurityLevel($values['security_level']);
			$projectObject->setNameSlug($slug);
			$projectObject->save();

			// Get the project's id
			$projectId = $projectObject->getId();

			// Create a new relationship between projects, products and project groups for each checked form's product
			foreach($values['product'] as $product)
			{
				$ptpObject = new ProjectToProduct();
				$ptpObject->setProjectGroupId($values['group']);
				$ptpObject->setProjectId($projectId);
				$ptpObject->setProductId($product);
				$ptpObject->save();
			}

			if($request->hasParameter('_save_and_add'))
			{
				$this->getUser()->setFlash('notice', $notice.' You can add another one below.');
				$this->redirect('@project_new');
			}
			else
			{
				$this->getUser()->setFlash('notice', $notice);
				$this->redirect(array('sf_route' => 'project_edit', 'sf_subject' => $projectObject));
			}
		}
		else
		{
			$this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
		}

	}

	public function executeEdit(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");

		$this->project = $this->getRoute()->getObject();

		// Get list of products available for this project from database
		$query = "SELECT ptp.project_group_id, ptp.product_id FROM ".$qa_generic.".project_to_product ptp WHERE project_id=".$this->project['id'];
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

		if(empty($result))
		{
			$this->getUser()->setFlash('error', 'No entry corresponding to the project into project_to_product table', false);
		}
		else
		{
			$productsIds = array();
			foreach($result as $element)
			{
				$productsIds[] = $element['product_id'];
			}

			$this->form = new ProjectFormCustom($this->project, array("projectGroupId" => $result[0]['project_group_id'], "productsIds" => $productsIds));
		}
	}

	public function executeUpdate(sfWebRequest $request)
	{
		$this->project = $this->getRoute()->getObject();

		$this->form = new ProjectFormCustom($this->project);

		$this->processUpdate($request, $this->form);

		$this->setTemplate('edit');
	}

	protected function processUpdate(sfWebRequest $request, ProjectFormCustom $form)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");

		$form->bind($request->getParameter($form->getName()));
		if ($form->isValid())
		{
			$values = $form->getValues();

			// Get project and project group id
			$projectId = $values['id'];
			$projectGroupId = $values['group'];

			// Slugify name, check if generated slug does not already exist and generate a new one if needed
			if(empty($values['name_slug']))
				$slug = MiscUtils::slugify($values['name']);
			else
				$slug = $values['name_slug'];
			$size = 1;
			while(Doctrine_Core::getTable("Project")->checkSlugForProject($projectId, $slug, false))
			{
				$slug = MiscUtils::slugify($values['name']).substr(microtime(), -$size);
				$size++;
			}

			// Update project data
			$projectObject = Doctrine_Core::getTable('Project')->find($projectId);
			$projectObject->setName($values['name']);
			$projectObject->setDescription($values['description']);
			$projectObject->setUserId($values['user_id']);
			$projectObject->setCreatedAt($values['created_at']);
			$projectObject->setStatus($values['status']);
			$projectObject->setSecurityLevel($values['security_level']);
			$projectObject->setNameSlug($slug);
			$projectObject->save();

			// Get all products linked to the current project from the database (complementary_tool_relation table)
			$query = "SELECT ptp.id, ptp.product_id FROM ".$qa_generic.".project_to_product ptp WHERE ptp.project_id = ".$projectId." AND ptp.project_group_id = ".$projectGroupId;
			$results = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);
			$productsFromDatabase = array();
			foreach($results as $projectToProduct)
			{
				// If one of the retrieved relationship is missing from form's products, delete the entry from database
				if(!in_array($projectToProduct['product_id'], $values['product']))
					Doctrine_Core::getTable('ProjectToProduct')->delete($projectToProduct['id']);
				else
					array_push($productsFromDatabase, $projectToProduct['product_id']);
			}

			// Now, cycle through form's products to add the new entries
			foreach($values['product'] as $product)
			{
				if(!in_array($product, $productsFromDatabase))
				{
					// Create new entry into ProjectToProduct table
					$ptpObject = new ProjectToProduct();
					$ptpObject->setProjectGroupId($values['group']);
					$ptpObject->setProjectId($projectId);
					$ptpObject->setProductId($product);
					$ptpObject->save();
				}
			}

			$this->getUser()->setFlash('notice', 'Project has been updated');
			$this->redirect(array('sf_route' => 'project_edit', 'sf_subject' => $projectObject));
		}
		else
		{
			$this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
		}

	}

	public function executeDelete(sfWebRequest $request)
	{
		$this->project = $this->getRoute()->getObject();

		$request->checkCSRFProtection();

		$qa_generic = sfConfig::get("app_table_qa_generic");

		// Set project status to 0 (= inactive)
		$query = "UPDATE ".$qa_generic.".project SET status=0 WHERE id=".$this->project['id'];
		Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);

		/*
		 * $query = "DELETE FROM ".$qa_generic.".project_to_product WHERE project_id=".$this->project['id'];
		* Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);
		* $query = "DELETE FROM ".$qa_generic.".project WHERE id=".$this->project['id'];
		* Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);
		*/

		$this->getUser()->setFlash('notice', 'The item was deleted successfully.');

		$this->redirect('@project');
	}

	/**
	 * Delete reports (batch action).
	 */
	public function executeBatchDelete(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$query = Doctrine_Manager::getInstance()->getCurrentConnection();

		$ids = $request->getParameter('ids');

		foreach($ids as $id)
		{
			$sql = "UPDATE ".$qa_generic.".project SET status = 0 WHERE id = ".$id;
			$result = $query->execute($sql);
		}

		$this->getUser()->setFlash('notice', 'The selected projects have been deleted successfully.');

		$this->redirect('project');
	}

	/**
	 * Delete reports (list action).
	 */
	public function executeListDelete(sfWebRequest $request)
	{
		$project = $this->getRoute()->getObject();
		$project->delete_project(false);

		$this->getUser()->setFlash('notice', 'The selected projects have been deleted successfully.');

		$this->redirect('project');
	}

	// Custom formatter so as to include inline_label css class as label attribute
	public function formatRadioLine($widget, $inputs)
	{
		$rows = array();
		foreach ($inputs as $input)
		{
			$mygt = strpos($input["label"], ">");
			$myrow = substr($input["label"], 0, $mygt)." class=\"inline_label\">";
			$myrow .= $input["input"].$widget->getOption("label_separator").substr($input["label"], $mygt+1);
			$rows[] = $myrow;
		}
		return implode($widget->getOption("separator"), $rows);
	}
}