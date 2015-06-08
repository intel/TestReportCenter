<?php

/**
 * Assignment form base class.
 *
 * @method Assignment getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseAssignmentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'role_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Role'), 'add_empty' => false)),
      'user_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('User'), 'add_empty' => false)),
      'project_group_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectGroup'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'role_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Role'))),
      'user_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('User'))),
      'project_group_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectGroup'))),
    ));

    $this->widgetSchema->setNameFormat('assignment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Assignment';
  }

}
