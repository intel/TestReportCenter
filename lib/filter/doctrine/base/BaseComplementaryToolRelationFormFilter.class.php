<?php

/**
 * ComplementaryToolRelation filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseComplementaryToolRelationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'label'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'table_name_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TableName'), 'add_empty' => true)),
      'table_entry_id'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'complementary_tool_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'tool_key'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'category'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'label'                 => new sfValidatorPass(array('required' => false)),
      'table_name_id'         => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('TableName'), 'column' => 'id')),
      'table_entry_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'complementary_tool_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'tool_key'              => new sfValidatorPass(array('required' => false)),
      'category'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('complementary_tool_relation_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ComplementaryToolRelation';
  }

  public function getFields()
  {
    return array(
      'id'                    => 'Number',
      'label'                 => 'Text',
      'table_name_id'         => 'ForeignKey',
      'table_entry_id'        => 'Number',
      'complementary_tool_id' => 'Number',
      'tool_key'              => 'Text',
      'category'              => 'Number',
    );
  }
}
