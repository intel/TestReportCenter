<?php

/**
 * SearchTagRelation form base class.
 *
 * @method SearchTagRelation getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseSearchTagRelationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'table_name_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TableName'), 'add_empty' => false)),
      'table_entry_id' => new sfWidgetFormInputText(),
      'search_tag_id'  => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'table_name_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('TableName'))),
      'table_entry_id' => new sfValidatorInteger(),
      'search_tag_id'  => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('search_tag_relation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SearchTagRelation';
  }

}
