<?php

/**
 * SearchTagRelation filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseSearchTagRelationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'table_name_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TableName'), 'add_empty' => true)),
      'table_entry_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'search_tag_id'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'table_name_id'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('TableName'), 'column' => 'id')),
      'table_entry_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'search_tag_id'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('search_tag_relation_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SearchTagRelation';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'table_name_id'  => 'ForeignKey',
      'table_entry_id' => 'Number',
      'search_tag_id'  => 'Number',
    );
  }
}
