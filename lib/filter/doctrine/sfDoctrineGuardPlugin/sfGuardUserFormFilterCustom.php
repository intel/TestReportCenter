<?php
class sfGuardUserFormFilterCustom extends sfGuardUserFormFilter
{
	public function configure()
	{
		$this->setWidget('id', new sfWidgetFormFilterInput(array('with_empty' => false)));
    	$this->setValidator('id', new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))));

		$this->getWidget('first_name')->setOption('with_empty', false);
		$this->getWidget('last_name')->setOption('with_empty', false);
		$this->getWidget('last_login')->setOption('with_empty', false);
	}
}