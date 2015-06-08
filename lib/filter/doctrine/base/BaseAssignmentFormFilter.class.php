<?php

/**
 * Assignment filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseAssignmentFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'role_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Role'), 'add_empty' => true)),
      'user_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('User'), 'add_empty' => true)),
      'project_group_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectGroup'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'role_id'          => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Role'), 'column' => 'id')),
      'user_id'          => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('User'), 'column' => 'id')),
      'project_group_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ProjectGroup'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('assignment_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Assignment';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'role_id'          => 'ForeignKey',
      'user_id'          => 'ForeignKey',
      'project_group_id' => 'ForeignKey',
    );
  }
}
