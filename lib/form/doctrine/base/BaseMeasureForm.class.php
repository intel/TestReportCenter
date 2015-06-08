<?php

/**
 * Measure form base class.
 *
 * @method Measure getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseMeasureForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'test_result_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TestResult'), 'add_empty' => false)),
      'value'          => new sfWidgetFormInputText(),
      'unit'           => new sfWidgetFormInputText(),
      'description'    => new sfWidgetFormInputText(),
      'category'       => new sfWidgetFormInputText(),
      'operator'       => new sfWidgetFormInputText(),
      'measure_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Measure'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'test_result_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('TestResult'))),
      'value'          => new sfValidatorString(array('max_length' => 45)),
      'unit'           => new sfValidatorString(array('max_length' => 45)),
      'description'    => new sfValidatorPass(array('required' => false)),
      'category'       => new sfValidatorInteger(),
      'operator'       => new sfValidatorInteger(array('required' => false)),
      'measure_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Measure'), 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('measure[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Measure';
  }

}
