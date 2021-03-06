<?php

/**
 * ProductType filter form.
 *
 * @package    trc
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProductTypeFormFilter extends BaseProductTypeFormFilter
{
	public function configure()
	{
		$this->getWidget('description')->setOption('with_empty', false);

		$this->setWidget('id', new sfWidgetFormFilterInput(array('with_empty' => false)));
    	$this->setValidator('id', new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))));
	}
}
