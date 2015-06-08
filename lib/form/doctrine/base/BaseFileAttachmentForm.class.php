<?php

/**
 * FileAttachment form base class.
 *
 * @method FileAttachment getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseFileAttachmentForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'name'           => new sfWidgetFormInputText(),
      'comment'        => new sfWidgetFormInputText(),
      'user_id'        => new sfWidgetFormInputText(),
      'uploaded_at'    => new sfWidgetFormDateTime(),
      'filename'       => new sfWidgetFormInputText(),
      'file_size'      => new sfWidgetFormInputText(),
      'file_mime_type' => new sfWidgetFormInputText(),
      'link'           => new sfWidgetFormInputText(),
      'checksum'       => new sfWidgetFormInputText(),
      'table_name_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TableName'), 'add_empty' => false)),
      'table_entry_id' => new sfWidgetFormInputText(),
      'category'       => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'           => new sfValidatorString(array('max_length' => 128)),
      'comment'        => new sfValidatorPass(array('required' => false)),
      'user_id'        => new sfValidatorInteger(),
      'uploaded_at'    => new sfValidatorDateTime(),
      'filename'       => new sfValidatorString(array('max_length' => 128)),
      'file_size'      => new sfValidatorInteger(),
      'file_mime_type' => new sfValidatorString(array('max_length' => 128)),
      'link'           => new sfValidatorString(array('max_length' => 255)),
      'checksum'       => new sfValidatorString(array('max_length' => 64)),
      'table_name_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('TableName'))),
      'table_entry_id' => new sfValidatorInteger(),
      'category'       => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('file_attachment[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FileAttachment';
  }

}
