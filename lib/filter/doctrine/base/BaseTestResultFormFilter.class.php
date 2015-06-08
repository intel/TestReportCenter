<?php

/**
 * TestResult filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseTestResultFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'complement'           => new sfWidgetFormFilterInput(),
      'test_session_id'      => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TestSession'), 'add_empty' => true)),
      'decision_criteria_id' => new sfWidgetFormFilterInput(),
      'comment'              => new sfWidgetFormFilterInput(),
      'started_at'           => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'execution_time'       => new sfWidgetFormFilterInput(),
      'status'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'bugs'                 => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'name'                 => new sfValidatorPass(array('required' => false)),
      'complement'           => new sfValidatorPass(array('required' => false)),
      'test_session_id'      => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('TestSession'), 'column' => 'id')),
      'decision_criteria_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'comment'              => new sfValidatorPass(array('required' => false)),
      'started_at'           => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'execution_time'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'status'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'bugs'                 => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('test_result_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TestResult';
  }

  public function getFields()
  {
    return array(
      'id'                   => 'Number',
      'name'                 => 'Text',
      'complement'           => 'Text',
      'test_session_id'      => 'ForeignKey',
      'decision_criteria_id' => 'Number',
      'comment'              => 'Text',
      'started_at'           => 'Date',
      'execution_time'       => 'Number',
      'status'               => 'Number',
      'bugs'                 => 'Text',
    );
  }
}
