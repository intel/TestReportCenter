<?php

/**
 * Configuration filter form.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ConfigurationFormFilter extends BaseConfigurationFormFilter
{
	public function configure()
	{
		$this->setWidget('id', new sfWidgetFormFilterInput(array('with_empty' => false)));
    	$this->setValidator('id', new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))));
	}
}
