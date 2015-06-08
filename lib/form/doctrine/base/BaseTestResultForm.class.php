<?php

/**
 * TestResult form base class.
 *
 * @method TestResult getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseTestResultForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'name'                 => new sfWidgetFormInputText(),
      'complement'           => new sfWidgetFormInputText(),
      'test_session_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TestSession'), 'add_empty' => false)),
      'decision_criteria_id' => new sfWidgetFormInputText(),
      'comment'              => new sfWidgetFormInputText(),
      'started_at'           => new sfWidgetFormDateTime(),
      'execution_time'       => new sfWidgetFormInputText(),
      'status'               => new sfWidgetFormInputText(),
      'bugs'                 => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'                 => new sfValidatorString(array('max_length' => 255)),
      'complement'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'test_session_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('TestSession'))),
      'decision_criteria_id' => new sfValidatorInteger(array('required' => false)),
      'comment'              => new sfValidatorPass(array('required' => false)),
      'started_at'           => new sfValidatorDateTime(),
      'execution_time'       => new sfValidatorInteger(array('required' => false)),
      'status'               => new sfValidatorInteger(),
      'bugs'                 => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('test_result[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TestResult';
  }

}
