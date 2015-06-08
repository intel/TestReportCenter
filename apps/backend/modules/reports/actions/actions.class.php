<?php

require_once dirname(__FILE__).'/../lib/reportsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/reportsGeneratorHelper.class.php';

/**
 * reports actions.
 *
 * @package    trc
 * @subpackage reports
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class reportsActions extends autoReportsActions
{
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
			$sql = "UPDATE ".$qa_generic.".test_session SET published = 0 WHERE id = ".$id;
			$result = $query->execute($sql);
		}

		$this->getUser()->setFlash('notice', 'The selected reports have been deleted successfully.');

		$this->redirect('test_session');
	}

	/**
	 * Delete permanently reports from database (batch action).
	 */
	public function executeBatchDelete_permanently(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$query = Doctrine_Manager::getInstance()->getCurrentConnection();

		$ids = $request->getParameter('ids');

		foreach($ids as $id)
		{
			$sql = "DELETE FROM ".$qa_generic.".test_session WHERE id = ".$id;
			$result = $query->execute($sql);
		}

		$this->getUser()->setFlash('notice', 'The selected reports have been deleted permanently.');

		$this->redirect('test_session');
	}

	/**
	 * Delete reports (list action).
	 */
	public function executeListDelete(sfWebRequest $request)
	{
		$report = $this->getRoute()->getObject();
		$report->delete_session(false);

		$this->getUser()->setFlash('notice', 'The selected reports have been deleted successfully.');

		$this->redirect('test_session');
	}

	/**
	 * Delete permanently reports from database (list action).
	 */
	public function executeListDeletePermanently(sfWebRequest $request)
	{
		$report = $this->getRoute()->getObject();
		$report->delete_session(true);

		$this->getUser()->setFlash('notice', 'The selected reports have been deleted permanently.');

		$this->redirect('test_session');
	}
}
