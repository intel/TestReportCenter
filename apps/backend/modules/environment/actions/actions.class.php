<?php

require_once dirname(__FILE__).'/../lib/environmentGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/environmentGeneratorHelper.class.php';

/**
 * environment actions.
 *
 * @package    trc
 * @subpackage environment
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class environmentActions extends autoEnvironmentActions
{
	/**
	 * Delete obsolete environments from database.
	 */
	public function executeCleanObsolete(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$query = Doctrine_Manager::getInstance()->getCurrentConnection();

		$sql = "DELETE FROM ".$qa_generic.".test_environment WHERE id NOT IN (SELECT test_environment_id FROM ".$qa_generic.".configuration ) ";
		$result = $query->execute($sql);

		$this->getUser()->setFlash('notice', 'Obsolete items have been deleted successfully.');

		$this->redirect('test_environment');
	}
}
