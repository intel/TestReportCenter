<?php

/**
 * TestEnvironment filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseTestEnvironmentFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'description'    => new sfWidgetFormFilterInput(),
      'cpu'            => new sfWidgetFormFilterInput(),
      'board'          => new sfWidgetFormFilterInput(),
      'gpu'            => new sfWidgetFormFilterInput(),
      'other_hardware' => new sfWidgetFormFilterInput(),
      'name_slug'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'name'           => new sfValidatorPass(array('required' => false)),
      'description'    => new sfValidatorPass(array('required' => false)),
      'cpu'            => new sfValidatorPass(array('required' => false)),
      'board'          => new sfValidatorPass(array('required' => false)),
      'gpu'            => new sfValidatorPass(array('required' => false)),
      'other_hardware' => new sfValidatorPass(array('required' => false)),
      'name_slug'      => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('test_environment_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TestEnvironment';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'name'           => 'Text',
      'description'    => 'Text',
      'cpu'            => 'Text',
      'board'          => 'Text',
      'gpu'            => 'Text',
      'other_hardware' => 'Text',
      'name_slug'      => 'Text',
    );
  }
}
