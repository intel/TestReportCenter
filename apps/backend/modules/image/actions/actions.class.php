<?php

require_once dirname(__FILE__).'/../lib/imageGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/imageGeneratorHelper.class.php';

/**
 * image actions.
 *
 * @package    trc
 * @subpackage image
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class imageActions extends autoImageActions
{
	/**
	 * Delete obsolete images from database.
	 */
	public function executeCleanObsolete(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$query = Doctrine_Manager::getInstance()->getCurrentConnection();

		$sql = "DELETE FROM ".$qa_generic.".image WHERE id NOT IN (SELECT image_id FROM ".$qa_generic.".configuration ) ";
		$result = $query->execute($sql);

		$this->getUser()->setFlash('notice', 'Obsolete items have been deleted successfully.');

		$this->redirect('image');
	}
}
