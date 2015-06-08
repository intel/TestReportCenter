<?php

/**
 * TestSession filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseTestSessionFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'build_id'          => new sfWidgetFormFilterInput(),
      'testset'           => new sfWidgetFormFilterInput(),
      'name'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'test_objective'    => new sfWidgetFormFilterInput(),
      'qa_summary'        => new sfWidgetFormFilterInput(),
      'user_id'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'created_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'editor_id'         => new sfWidgetFormFilterInput(),
      'updated_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'project_release'   => new sfWidgetFormFilterInput(),
      'project_milestone' => new sfWidgetFormFilterInput(),
      'issue_summary'     => new sfWidgetFormFilterInput(),
      'status'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'published'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'configuration_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Configuration'), 'add_empty' => true)),
      'campaign_checksum' => new sfWidgetFormFilterInput(),
      'build_slug'        => new sfWidgetFormFilterInput(),
      'testset_slug'      => new sfWidgetFormFilterInput(),
      'notes'             => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'build_id'          => new sfValidatorPass(array('required' => false)),
      'testset'           => new sfValidatorPass(array('required' => false)),
      'name'              => new sfValidatorPass(array('required' => false)),
      'test_objective'    => new sfValidatorPass(array('required' => false)),
      'qa_summary'        => new sfValidatorPass(array('required' => false)),
      'user_id'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'editor_id'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'updated_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'project_release'   => new sfValidatorPass(array('required' => false)),
      'project_milestone' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'issue_summary'     => new sfValidatorPass(array('required' => false)),
      'status'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'published'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'configuration_id'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Configuration'), 'column' => 'id')),
      'campaign_checksum' => new sfValidatorPass(array('required' => false)),
      'build_slug'        => new sfValidatorPass(array('required' => false)),
      'testset_slug'      => new sfValidatorPass(array('required' => false)),
      'notes'             => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('test_session_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TestSession';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'build_id'          => 'Text',
      'testset'           => 'Text',
      'name'              => 'Text',
      'test_objective'    => 'Text',
      'qa_summary'        => 'Text',
      'user_id'           => 'Number',
      'created_at'        => 'Date',
      'editor_id'         => 'Number',
      'updated_at'        => 'Date',
      'project_release'   => 'Text',
      'project_milestone' => 'Number',
      'issue_summary'     => 'Text',
      'status'            => 'Number',
      'published'         => 'Number',
      'configuration_id'  => 'ForeignKey',
      'campaign_checksum' => 'Text',
      'build_slug'        => 'Text',
      'testset_slug'      => 'Text',
      'notes'             => 'Text',
    );
  }
}
