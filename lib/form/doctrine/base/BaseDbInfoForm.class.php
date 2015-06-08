<?php

/**
 * DbInfo form base class.
 *
 * @method DbInfo getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseDbInfoForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'version'      => new sfWidgetFormInputHidden(),
      'comment'      => new sfWidgetFormInputText(),
      'core_db_name' => new sfWidgetFormInputText(),
      'user_id'      => new sfWidgetFormInputText(),
      'changed_at'   => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'version'      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('version')), 'empty_value' => $this->getObject()->get('version'), 'required' => false)),
      'comment'      => new sfValidatorPass(),
      'core_db_name' => new sfValidatorString(array('max_length' => 45)),
      'user_id'      => new sfValidatorInteger(),
      'changed_at'   => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('db_info[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DbInfo';
  }

}
