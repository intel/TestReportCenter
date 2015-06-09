<?php

/**
 * TestEnvironment form base class.
 *
 * @method TestEnvironment getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseTestEnvironmentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'name'           => new sfWidgetFormInputText(),
      'description'    => new sfWidgetFormInputText(),
      'cpu'            => new sfWidgetFormInputText(),
      'board'          => new sfWidgetFormInputText(),
      'gpu'            => new sfWidgetFormInputText(),
      'other_hardware' => new sfWidgetFormInputText(),
      'name_slug'      => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'           => new sfValidatorString(array('max_length' => 255)),
      'description'    => new sfValidatorPass(array('required' => false)),
      'cpu'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'board'          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'gpu'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'other_hardware' => new sfValidatorPass(array('required' => false)),
      'name_slug'      => new sfValidatorString(array('max_length' => 255)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'TestEnvironment', 'column' => array('name_slug')))
    );

    $this->widgetSchema->setNameFormat('test_environment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TestEnvironment';
  }

}
