<?php

/**
 * TestSession filter form.
 *
 * @package    trc
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TestSessionFormFilter extends BaseTestSessionFormFilter
{
	public function configure()
	{
		unset($this['user_id']);
		unset($this['editor_id']);

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
		$this->setWidget("editor", new sfWidgetFormChoice(array(
			"choices"	=> $userOptions,
		)));

		$this->setValidator('username', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($userOptions))));
		$this->setValidator('editor', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($userOptions))));
	}

	public function addUsernameColumnQuery($query, $field, $value)
	{
		Doctrine::getTable('TestSession')->applyUsernameFilter($query, $value);
	}

	public function addEditorColumnQuery($query, $field, $value)
	{
		Doctrine::getTable('TestSession')->applyEditorFilter($query, $value);
	}

	public function getFields()
	{
		return array_merge(parent::getFields(), array('username' => 'username', 'editor' => 'editor'));
	}
}
