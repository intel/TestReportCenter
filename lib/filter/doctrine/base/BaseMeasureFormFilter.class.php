<?php

/**
 * Measure filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseMeasureFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'test_result_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TestResult'), 'add_empty' => true)),
      'value'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'unit'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'description'    => new sfWidgetFormFilterInput(),
      'category'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'operator'       => new sfWidgetFormFilterInput(),
      'measure_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Measure'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'test_result_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('TestResult'), 'column' => 'id')),
      'value'          => new sfValidatorPass(array('required' => false)),
      'unit'           => new sfValidatorPass(array('required' => false)),
      'description'    => new sfValidatorPass(array('required' => false)),
      'category'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'operator'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'measure_id'     => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Measure'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('measure_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Measure';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'test_result_id' => 'ForeignKey',
      'value'          => 'Text',
      'unit'           => 'Text',
      'description'    => 'Text',
      'category'       => 'Number',
      'operator'       => 'Number',
      'measure_id'     => 'ForeignKey',
    );
  }
}
