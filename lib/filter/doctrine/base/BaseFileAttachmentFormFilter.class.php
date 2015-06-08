<?php

/**
 * FileAttachment filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseFileAttachmentFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'comment'        => new sfWidgetFormFilterInput(),
      'user_id'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'uploaded_at'    => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'filename'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'file_size'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'file_mime_type' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'link'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'checksum'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'table_name_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TableName'), 'add_empty' => true)),
      'table_entry_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'category'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'name'           => new sfValidatorPass(array('required' => false)),
      'comment'        => new sfValidatorPass(array('required' => false)),
      'user_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'uploaded_at'    => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'filename'       => new sfValidatorPass(array('required' => false)),
      'file_size'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'file_mime_type' => new sfValidatorPass(array('required' => false)),
      'link'           => new sfValidatorPass(array('required' => false)),
      'checksum'       => new sfValidatorPass(array('required' => false)),
      'table_name_id'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('TableName'), 'column' => 'id')),
      'table_entry_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'category'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('file_attachment_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'FileAttachment';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'name'           => 'Text',
      'comment'        => 'Text',
      'user_id'        => 'Number',
      'uploaded_at'    => 'Date',
      'filename'       => 'Text',
      'file_size'      => 'Number',
      'file_mime_type' => 'Text',
      'link'           => 'Text',
      'checksum'       => 'Text',
      'table_name_id'  => 'ForeignKey',
      'table_entry_id' => 'Number',
      'category'       => 'Number',
    );
  }
}
