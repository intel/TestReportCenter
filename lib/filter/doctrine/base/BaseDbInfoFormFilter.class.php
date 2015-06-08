<?php

/**
 * DbInfo filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseDbInfoFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'comment'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'core_db_name' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'user_id'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'changed_at'   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'comment'      => new sfValidatorPass(array('required' => false)),
      'core_db_name' => new sfValidatorPass(array('required' => false)),
      'user_id'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'changed_at'   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('db_info_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DbInfo';
  }

  public function getFields()
  {
    return array(
      'version'      => 'Number',
      'comment'      => 'Text',
      'core_db_name' => 'Text',
      'user_id'      => 'Number',
      'changed_at'   => 'Date',
    );
  }
}
