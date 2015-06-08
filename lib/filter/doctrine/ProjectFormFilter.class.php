<?php

/**
 * Project filter form.
 *
 * @package    trc
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectFormFilter extends BaseProjectFormFilter
{
	public function configure()
	{
		unset($this['user_id']);

		$this->setWidget('id', new sfWidgetFormFilterInput(array('with_empty' => false)));
    	$this->setValidator('id', new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))));

		// Get list of users
		$users = Doctrine_Core::getTable("sfGuardUser")->getIdList();
		// Convert users to an associative array with user id as key
		$userOptions = array("" => "");
		foreach($users as $user)
		{
		    $userOptions[$user['id']] = $user["username"];
		}

		$this->setWidget("username", new sfWidgetFormChoice(array(
			"choices"	=> $userOptions,
		)));

		$this->setValidator('username', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($userOptions))));
	}

	public function addUsernameColumnQuery($query, $field, $value)
	{
		Doctrine::getTable('Project')->applyUsernameFilter($query, $value);
	}
}
