<?php

/**
 * ComplementaryToolRelation form base class.
 *
 * @method ComplementaryToolRelation getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseComplementaryToolRelationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                    => new sfWidgetFormInputHidden(),
      'label'                 => new sfWidgetFormInputText(),
      'table_name_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TableName'), 'add_empty' => false)),
      'table_entry_id'        => new sfWidgetFormInputText(),
      'complementary_tool_id' => new sfWidgetFormInputText(),
      'tool_key'              => new sfWidgetFormInputText(),
      'category'              => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                    => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'label'                 => new sfValidatorString(array('max_length' => 128)),
      'table_name_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('TableName'))),
      'table_entry_id'        => new sfValidatorInteger(),
      'complementary_tool_id' => new sfValidatorInteger(),
      'tool_key'              => new sfValidatorString(array('max_length' => 255)),
      'category'              => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('complementary_tool_relation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ComplementaryToolRelation';
  }

}
